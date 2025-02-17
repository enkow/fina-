<?php

namespace App\Jobs;

use App\Enums\ReminderMethod;
use App\Enums\ReservationSource;
use App\Models\Game;
use App\Models\Product;
use App\Models\Reminder;
use App\Models\Slot;
use Carbon\Carbon;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Country;
use App\Models\Reservation;

use App\Custom\Timezone;
use App\Custom\Fakturownia;
use App\Enums\ReservationSlotStatus;

class CreateInvoices implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	public function addProductData(
		$club,
		$billingPeriod,
		$today,
		&$invoiceItemsToCreate,
		&$smsReminderIds,
		$includeLabels = null,
		$excludeLabels = null
	) {
		foreach (['month', 'year'] as $period) {
			if ($billingPeriod[$period][$billingPeriod['payment_time']] === $today) {
				// Handle the products on the invoice
				foreach (
					$club
						->products()
						->when($includeLabels, function ($query) use ($includeLabels) {
							$query->whereIn('system_label', $includeLabels);
						})
						->when($excludeLabels, function ($query) use ($excludeLabels) {
							$query->where(function ($query) use($excludeLabels) {
                                $query->whereNotIn('system_label', $excludeLabels);
                                $query->orWhereNull('system_label');
                            });
						})
						->wherePivot('period', $period)
						->get()
					as $product
				) {
					$invoiceProductData = [
						'settings' => [
							'period' => $period,
						],
						'model_id' => $product->id,
						'model_type' => Product::class,
						'total' => $product->pivot->cost,
					];

					if (in_array($product->system_label, ['sms_online', 'sms_offline'])) {
						$quantity =
							$club->sms_count['month'][
								$product->system_label === 'sms_online' ? 'online' : 'offline'
							];

						$invoiceProductData['settings'] = [
							'period' => $period,
							'quantity' => 1,
						];
						$invoiceProductData['total'] = $product->pivot->cost * $quantity;

						$smsReminderIds[$product->system_label] = $club
							->remindersReservation()
							->where('method', ReminderMethod::Sms)
							->where('real', true)
							->whereNull('invoice_item_id')
							->whereHasMorph('remindable', [Reservation::class], function ($query) use (
								$billingPeriod,
								$product
							) {
								return $query
									->where(
										'source',
										$product->system_label === 'sms_online'
											? ReservationSource::Widget
											: ReservationSource::Panel
									)
									->where(
										'created_at',
										'>=',
										$billingPeriod['month']['start_at'] . ' 00:00:00'
									);
							})
							->pluck('reminders.id');
					}
					if ($invoiceProductData['total']) {
						$invoiceItemsToCreate[] = $invoiceProductData;
					}
				}
			}
		}
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 * @throws \JsonException
	 */
	public function handle($clubs = null)
	{
		// Fetch all active countries
		$countries = Country::getCached()->where('active', true);

		// Select all clubs that are in the time zone with the current time of 00:10
		// The script is supposed to run every hour at 10 minutes, so we only verify the hour without minutes
		$clubs =
			$clubs ??
			$countries
				->filter(function ($country) {
					$currentTime = now($country->timezone);
					return (int) $currentTime->format('H') === 1;
				})
				->flatMap(function ($country) {
					return $country->clubs;
				});

		// Get the current date based on the first club's timezone
		$firstClubTimezone = $clubs->first()?->country?->timezone ?? 0;
		$today = now($firstClubTimezone)->format('Y-m-d');

		foreach ($clubs as $club) {
            $invoiceItemsToCreate = [];
			$billingPeriod = $club->getBillingPeriod();

            // Check if an invoice should be issued today
            if(!in_array($today, [$club->invoice_next_month, $club->invoice_next_year])) {
                continue;
            }

			echo $club->id .
				' ' .
				$club->name .
				' ' .
				$club->country->name .
				' ' .
				$club->country->timezone .
				PHP_EOL;

			$fromData =
				$today === $billingPeriod['year']['end_at']
					? $billingPeriod['year']['start_at']
					: $billingPeriod['month']['start_at'];
			if ($club->created_at > $fromData) {
				$fromData = $club->created_at;
			}

			$toData =
				$today === $billingPeriod['year']['end_at']
					? $billingPeriod['year']['end_at']
					: $billingPeriod['month']['end_at'];

			// Create an invoice model in the database
			$invoice = $club->invoices()->create([
				'total' => 0,
				'currency' => $club->country->currency,
				'vat' => $club->vat,
				'auto_send' => $club->invoice_autosend,
				'advance_payment' => $club->invoice_advance_payment,
				'days_for_payment' => $club->invoice_autopay ? 0 : $club->invoice_payment_time,
				'last' => $club->invoice_last,
				'to' => $toData,
				'from' => $fromData,
			]);

			$smsReminderIds = [];
			$this->addProductData(
				$club,
				$billingPeriod,
				$today,
				$invoiceItemsToCreate,
				$smsReminderIds,
				null,
				['sms_online', 'sms_offline']
			);

			//			if ($billingPeriod['month']['end_at'] === $today) {
			// Handling the commission in the monthly invoice.
			foreach ($club->games as $game) {
				$onlinePaymentsClass = $club->paymentMethod ? $club->paymentMethod->getChildType() : null;

				$invoiceItemData = [
					'settings' => [
						'include' => $game->pivot->include_on_invoice,
						'status_include' => $game->pivot->include_on_invoice_status,
						'fee_percent' => $game->pivot->fee_percent,
						'fee_fixed' => $game->pivot->fee_fixed,
						'online_payments_enabled' => $club->online_payments_enabled,
						'offline_payments_enabled' => $club->offline_payments_enabled,
						'online_payment_method' => $onlinePaymentsClass
							? [
								'name' => $onlinePaymentsClass::NAME ?? null,
								'fee_percent' => $club->paymentMethod->fee_percentage
									? $club->paymentMethod->fee_percentage / 100
									: 0,
								'fee_fixed' => $club->paymentMethod->fee_fixed ?? 0,
							]
							: null,
					],
					'model_id' => $game->id,
					'model_type' => Game::class,
					'total' => 0,
				];

                $clubPricelistIds = $club->pricelists()->pluck('id')->toArray();
                $clubSlotIds = Slot::whereIn('pricelist_id', $clubPricelistIds)->pluck('id')->toArray();
				$reservationsQuery = fn() => Reservation::where('game_id', $game->id)
					->whereHas('reservationSlots', function ($query) use ($club, $billingPeriod, $clubSlotIds) {
						$query
							->whereIn('slot_id', $clubSlotIds)
							->whereBetween('end_at', [
								now()
									->parse($billingPeriod['month']['start_at'] . ' 00:00:00')
									->timezone('UTC'),
								now()
									->parse($billingPeriod['month']['end_at'] . ' 23:59:59')
									->timezone('UTC'),
							]);
					})
					->whereNull('invoice_id');

				$include_on_invoice_status = json_decode(
					$game->pivot->include_on_invoice_status ?? '[]',
					true,
					512,
					JSON_THROW_ON_ERROR
				);
				$include_on_invoice = $game->pivot->include_on_invoice;

				$invoiceItemData['details'] = [
					'offline' => [
						'commission' => $reservationsQuery()
							->whereHas('reservationSlots', function ($query) use (
								$include_on_invoice_status
							) {
								$query->whereIn('status', $include_on_invoice_status);
							})
							->where('source', ReservationSource::Panel)
							->sum('app_commission'),
						'price' => $reservationsQuery()
							->where('source', ReservationSource::Panel)
							->sum('price'),
					],
					'online' => [
						'online' => [
							'commission' =>
								$include_on_invoice &&
								in_array(ReservationSlotStatus::Confirmed->value, $include_on_invoice_status)
									? $reservationsQuery()
										->where('source', ReservationSource::Widget)
										->whereHas('reservationSlots', function ($query) {
											$query->where('status', ReservationSlotStatus::Confirmed->value);
										})
										->whereNotNull('paid_at')
										->sum('app_commission')
									: 0,
							'price' => $reservationsQuery()
								->where('source', ReservationSource::Widget)
								->whereHas('reservationSlots', function ($query) {
									$query->where('status', ReservationSlotStatus::Confirmed->value);
								})
								->whereNotNull('paid_at')
								->sum('price'),
						],
						'club' => [
							'commission' =>
								$include_on_invoice &&
								in_array(ReservationSlotStatus::Confirmed->value, $include_on_invoice_status)
									? $reservationsQuery()
										->where('source', ReservationSource::Widget)
										->whereHas('reservationSlots', function ($query) {
											$query->where('status', ReservationSlotStatus::Confirmed->value);
										})
										->whereNull('paid_at')
										->sum('app_commission')
									: 0,
							'price' => $reservationsQuery()
								->where('source', ReservationSource::Widget)
								->whereHas('reservationSlots', function ($query) {
									$query->where('status', ReservationSlotStatus::Confirmed->value);
								})
								->whereNull('paid_at')
								->sum('price'),
						],
						'expired' => [
							'commission' =>
								$include_on_invoice &&
								in_array(ReservationSlotStatus::Expired->value, $include_on_invoice_status)
									? $reservationsQuery()
										->where('source', ReservationSource::Widget)
										->whereHas('reservationSlots', function ($query) {
											$query->whereIn('status', [
												ReservationSlotStatus::Expired->value,
												ReservationSlotStatus::Pending->value,
											]);
										})
										->sum('app_commission')
									: 0,
							'price' => $reservationsQuery()
								->where('source', ReservationSource::Widget)
								->whereHas('reservationSlots', function ($query) {
									$query->whereIn('status', [
										ReservationSlotStatus::Expired->value,
										ReservationSlotStatus::Pending->value,
									]);
								})
								->sum('price'),
						],
						'providerCommission' => $reservationsQuery()->sum('provider_commission'),
					],
				];

				$invoiceItemData['total'] = $include_on_invoice
					? $reservationsQuery()
						->whereHas('reservationSlots', function ($query) use ($include_on_invoice_status) {
							$query->whereIn('status', $include_on_invoice_status);
						})
						->sum('app_commission')
					: 0;
				$reservationsQuery()->update([
					'invoice_id' => $invoice->id,
					'invoice_conditions_matched' => true,
				]);

				if ($invoiceItemData['total'] > 0) {
					$invoiceItemsToCreate[] = $invoiceItemData;
				}

				//				}
			}

			$this->addProductData(
				$club,
				$billingPeriod,
				$today,
				$invoiceItemsToCreate,
				$smsReminderIds,
				['sms_online', 'sms_offline'],
				null
			);
			// merge the same products
			$result = [];

			foreach ($invoiceItemsToCreate as $item) {
				$key = json_encode($item['settings']) . '|' . $item['model_id'] . '|' . $item['model_type'];

				if (isset($result[$key])) {
					$result[$key]['total'] += $item['total'];
				} else {
					$result[$key] = $item;
				}
			}

			if (count($invoiceItemsToCreate) === 0) {
				$invoice->delete();
				continue;
			}

			$invoiceItemsToCreate = array_values($result);
			foreach ($invoiceItemsToCreate as $invoiceItemToCreate) {
				if ($invoiceItemToCreate['total'] === 0) {
					continue;
				}
				$invoice->items()->create($invoiceItemToCreate);
			}

			$invoice->total = $invoice->items()->sum('total');
			$invoice->save();

            foreach(['year','month'] as $period) {
                if ($club->invoice_last) {
                    // If it's the last invoice, set the next invoice date to null
                    $club->{"invoice_next_$period"} = null;
                } elseif ($period === 'year' && $club->invoice_next_year) {
                    // If billing period is yearly, add one year to the next invoice date
                    $club->invoice_next_year = now()
                        ->parse($billingPeriod[$period][$billingPeriod['payment_time']])
                        ->addYears(1);
                } elseif ($period === 'month' && $club->invoice_next_month) {
                    // If billing period is monthly, add one month to the next invoice date
                    $club->invoice_next_month = now()
                        ->parse($billingPeriod['month'][$billingPeriod['payment_time']])
                        ->addMonths(1);
                }
            }

            $club->save();

			foreach ($smsReminderIds as $label => $ids) {
				Reminder::whereIn('id', $ids)->update([
					'invoice_item_id' => $invoice
						->items()
						->whereHasMorph('model', [Product::class], function ($query) use ($label) {
							$query->where('system_label', $label);
						})
						->first()?->id,
				]);
			}

			if ($club->invoice_autopay) {
				// If automatic payment is set, mark the invoice as successful
				try {
					$invoice->charge();
				} catch (\Exception $e) {
					$invoice->fail();
				}
				info('c');
			} else {
				// Otherwise, create the invoice in "fakturownia"
				$invoice->fakturowniaCreate();
			}
		}

		return;
	}
}
