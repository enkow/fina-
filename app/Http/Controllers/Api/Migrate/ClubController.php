<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Custom\Fakturownia;
use App\Models\PaymentMethod;
use App\Models\Country;
use App\Models\ImportedModel;
use App\Models\Club;
use App\Models\OpeningHours;
use App\Models\Game;
use App\Models\PaymentMethods\Tpay;
use App\Models\PaymentMethods\Stripe;
use Illuminate\Support\Facades\Cache;

class ClubController extends Controller
{
	public function store(Request $request)
	{
		$clubMorph = (new Club())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $clubMorph)
			->first();

		if ($importModel) {
			return $this->update($request, $request->input('id'));
		}

		$data = $request->only([
			'name',
			'email',
			'description',
			'address',
			'city',
			'phone_number',
			'postal_code',
			'billing_name',
			'billing_address',
			'billing_postal_code',
			'billing_city',
			'vat_number',
			'offline_payments_enabled',
			'sms_notifications_online',
			'sms_notifications_offline',
			'created_at',
		]);

		$data['invoice_emails'] = explode(',', str_replace(' ', '', $request->input('invoice_emails')));

		$data['online_payments_enabled'] = 'disabled';

		$fakturownia = new Fakturownia();
		$data['fakturownia_id'] = $fakturownia->createClient(['name' => $request->input('name')])['id'];

		$data['country_id'] = Country::where('code', 'pl')->first()->id;
		$data['slug'] = $request->input('name');
		$data['invoice_next_month'] = now()
			->startOfMonth()
			->addMonth()
			->format('Y-m-d');
		$data['invoice_lang'] = 'pl';
		$data['invoice_advance_payment'] = false;
		$data['invoice_autopay'] = false;

		$importedClub = Club::create($data);

		// Set Settings

		$importedClub->settings()->create([
			'key' => 'calendar_sync_status',
			'feature_id' => null,
			'value' => $request->input('calendar_sync_status'),
		]);

		$importedClub->settings()->create([
			'key' => 'timers_status',
			'feature_id' => null,
			'value' => $request->input('timers_status'),
		]);

		$importedClub->settings()->create([
			'key' => 'additional_commission_percent',
			'feature_id' => null,
			'value' => ((float)str_replace(",",".",$request->input('additional_commission_percent',0))) * 100,
		]);

		$importedClub->settings()->create([
			'key' => 'additional_commission_fixed',
			'feature_id' => null,
			'value' => $request->input('additional_commission_fixed',0),
		]);

		// init games

		$bowling = Game::where('name', 'bowling')->first();
		$billiard = Game::where('name', 'billiard ')->first();
		$table = Game::where('name', $request->input('games.table.variant'))->first();

		// Set games

		// This game Edited because ClubObserver was created when creating the club
		if ((bool) $request->input('games.billiard')) {
			if ($importedClub->games->contains($billiard->id)) {
				$importedClub->games()->updateExistingPivot($billiard, [
					'custom_names' => json_encode(
						[
							$data['country_id'] => $request->input('games.billiard.trans_pl'),
						],
						JSON_THROW_ON_ERROR
					),
					'enabled_on_widget' => $request->input('games.billiard.widget'),
					'fee_percent' => $request->input('games.billiard.provision') ?? 0,
				]);
			} else {
				$importedClub->games()->attach($billiard, [
					'weight' => 1,
					'enabled_on_widget' => $request->input('games.billiard.widget'),
					'fee_percent' => $request->input('games.billiard.provision') ?? 0,
					'custom_names' => json_encode(
						[
							$data['country_id'] => $request->input('games.billiard.trans_pl'),
						],
						JSON_THROW_ON_ERROR
					),
				]);
			}
		} else {
			$importedClub->games()->detach($billiard->id);
		}

		if ((bool) $request->input('games.bowling')) {
			$importedClub->games()->attach($bowling, [
				'weight' => 2,
				'enabled_on_widget' => $request->input('games.bowling.widget'),
				'fee_percent' => $request->input('games.bowling.provision') ?? 0,
				'custom_names' => json_encode(
					[
						$data['country_id'] => $request->input('games.bowling.trans_pl'),
					],
					JSON_THROW_ON_ERROR
				),
			]);
		}

		if ((bool) $request->input('games.table')) {
			$importedClub->games()->attach($table, [
				'weight' => 3,
				'enabled_on_widget' => $request->input('games.table.widget'),
				'fee_percent' => $request->input('games.table.provision') ?? 0,
				'custom_names' => json_encode(
					[
						$data['country_id'] => $request->input('games.table.trans_pl'),
					],
					JSON_THROW_ON_ERROR
				),
			]);

			//create empty pricelist for table
			$importedClub->createEmptyPricelist($table);

            $tableFixedReservationDurationFeature = $table->features()->where('type','fixed_reservation_duration')->first();

            $importedClub->settings()->create([
                'key' => 'fixed_reservation_duration_value',
                'feature_id' => $tableFixedReservationDurationFeature->id,
                'value' => 24,
            ]);
            $importedClub->settings()->create([
                'key' => 'fixed_reservation_duration_status',
                'feature_id' => $tableFixedReservationDurationFeature->id,
                'value' => true,
            ]);
		}

		// features default

		$bowlingFeature = $bowling->getFeaturesByType('price_per_person')->first();
		$billiardFeature = $billiard->getFeaturesByType('price_per_person')->first();
		$billiardLounge = $billiard->getFeaturesByType('slot_has_lounge')->first();

		if ($request->input('games.billiard')) {
			$importedClub->settings()->create([
				'key' => 'price_per_person_type',
				'feature_id' => $billiardFeature->id,
				'value' => 0,
			]);

			$importedClub->settings()->create([
				'key' => 'lounges_status',
				'feature_id' => $billiardLounge->id,
				'value' => false,
			]);
		}

		if ($request->input('games.bowling')) {
			$importedClub->settings()->create([
				'key' => 'price_per_person_type',
				'feature_id' => $bowlingFeature->id,
				'value' => 1,
			]);
		}

		// Close club in all days in week

		$openingHoursData = [
			'club_closed' => 1,
			'reservation_closed' => 1,
		];

		OpeningHours::where('club_id', $importedClub->id)->update($openingHoursData);

		//Add tpay method
		$custom_method = $request->input('custome_payments');
		$onlinePaymentStatus = $request->input('online_status');

		if (!$onlinePaymentStatus) {
			$importedClub->online_payments_enabled = 'disabled';
		} elseif ($custom_method === null) {
			$importedClub->online_payments_enabled = 'internal';
			Tpay::create([
				'club_id' => $importedClub->id,
				'online' => true,
				'enabled' => true,
			]);
		} elseif ($custom_method !== null) {
			$importedClub->online_payments_enabled = 'external';
			Tpay::create([
				'club_id' => $importedClub->id,
				'online' => true,
				'activated' => true,
				'enabled' => true,
				'credentials' => $custom_method,
			]);
		}
		$importedClub->save();

		// Imported model create

		ImportedModel::create([
			'model_type' => $clubMorph,
			'model_id' => $importedClub->id,
			'old_id' => $request->input('id'),
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Club())->getMorphClass())
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$data = $request->only([
			'name',
			'email',
			'description',
			'address',
			'city',
			'phone_number',
			'postal_code',
			'billing_name',
			'billing_address',
			'billing_postal_code',
			'billing_city',
			'vat_number',
			'offline_payments_enabled',
			'additional_commission_fixed',
			'additional_commission_percent',
			'sms_notifications_online',
			'sms_notifications_offline',
		]);

		$data['invoice_emails'] = explode(',', $request->input('invoice_emails'));

		$data['online_payments_enabled'] = 'disabled';

		$importModel->model->update($data);

		// update Settings

		$importModel->model->settings()->updateOrCreate(
			[
				'key' => 'calendar_sync_status',
				'feature_id' => null,
			],
			['value' => $request->input('calendar_sync_status')]
		);

		$importModel->model->settings()->updateOrCreate(
			[
				'key' => 'timers_status',
				'feature_id' => null,
			],
			['value' => $request->input('timers_status')]
		);
		$importModel->model->settings()->updateOrCreate(
			[
				'key' => 'additional_commission_percent',
				'feature_id' => null,
			],
			['value' => ((float)str_replace(",",".",$request->input('additional_commission_percent'))) * 100]
		);
		$importModel->model->settings()->updateOrCreate(
			[
				'key' => 'additional_commission_fixed',
				'feature_id' => null,
			],
			['value' => $request->input('additional_commission_fixed')]
		);
		// update Games

		$bowling = Game::where('name', 'bowling')->first();
		$billiard = Game::where('name', 'billiard')->first();
		$table = Game::whereIn(
			'name',
			$request->input('games.table.variant')
				? [$request->input('games.table.variant')]
				: ['unnumbered_tables', 'numbered_tables']
		)->get();

		if ((bool) $request->input('games.billiard')) {
			if ($importModel->model->games->contains($billiard->id)) {
				$importModel->model->games()->updateExistingPivot($billiard, [
					'custom_names' => json_encode(
						[
							$importModel->model->country_id => $request->input('games.billiard.trans_pl'),
						],
						JSON_THROW_ON_ERROR
					),
					'enabled_on_widget' => $request->input('games.billiard.widget'),
					'fee_percent' => $request->input('games.billiard.provision') ?? 0,
				]);
			} else {
				$importModel->model->games()->attach($billiard, [
					'weight' => 1,
					'enabled_on_widget' => $request->input('games.billiard.widget'),
					'fee_percent' => $request->input('games.billiard.provision') ?? 0,
					'custom_names' => json_encode(
						[
							$importModel->model->country_id => $request->input('games.billiard.trans_pl'),
						],
						JSON_THROW_ON_ERROR
					),
				]);
			}
		} else {
			$importModel->model->games()->detach($billiard->id);
		}

		if ((bool) $request->input('games.bowling')) {
			if ($importModel->model->games->contains($bowling->id)) {
				$importModel->model->games()->updateExistingPivot($bowling, [
					'custom_names' => json_encode(
						[
							$importModel->model->country_id => $request->input('games.bowling.trans_pl'),
						],
						JSON_THROW_ON_ERROR
					),
					'enabled_on_widget' => $request->input('games.bowling.widget'),
					'fee_percent' => $request->input('games.bowling.provision') ?? 0,
				]);
			} else {
				$importModel->model->games()->attach($bowling, [
					'weight' => 2,
					'enabled_on_widget' => $request->input('games.bowling.widget'),
					'fee_percent' => $request->input('games.bowling.provision') ?? 0,
					'custom_names' => json_encode(
						[
							$importModel->model->country_id => $request->input('games.bowling.trans_pl'),
						],
						JSON_THROW_ON_ERROR
					),
				]);
			}
		} else {
			$importModel->model->games()->detach($bowling->id);
		}

		if ((bool) $request->input('games.table') && $table->count() === 1) {
			if ($importModel->model->games->contains($table->first()->id)) {
				$importModel->model->games()->updateExistingPivot($table, [
					'custom_names' => json_encode(
						[
							$importModel->model->country_id => $request->input('games.table.trans_pl'),
						],
						JSON_THROW_ON_ERROR
					),
					'enabled_on_widget' => $request->input('games.table.widget'),
					'fee_percent' => $request->input('games.table.provision') ?? 0,
				]);
			} else {
				$importModel->model->games()->attach($table, [
					'weight' => 3,
					'enabled_on_widget' => $request->input('games.table.widget'),
					'fee_percent' => $request->input('games.table.provision') ?? 0,
					'custom_names' => json_encode(
						[
							$importModel->model->country_id => $request->input('games.table.trans_pl'),
						],
						JSON_THROW_ON_ERROR
					),
				]);
			}
		} else {
			$importModel->model->games()->detach($table);
		}

		//update Tpay Method
		$custom_method = $request->input('custome_payments');
		$onlinePaymentStatus = $request->input('online_status');

		if (!$onlinePaymentStatus) {
			$importModel->model->online_payments_enabled = 'disabled';
		} elseif ($custom_method === null) {
			PaymentMethod::disabledAllOnlineMethod($importModel->model);

			$importModel->model->online_payments_enabled = 'internal';

			Tpay::updateOrCreate(
				[
					'club_id' => $importModel->model->id,
				],
				[
					'online' => true,
					'enabled' => true,
				]
			);
		} elseif ($custom_method !== null) {
			PaymentMethod::disabledAllOnlineMethod($importModel->model);

			$importModel->model->online_payments_enabled = 'external';

			Tpay::updateOrCreate(
				[
					'club_id' => $importModel->model->id,
				],
				[
					'online' => true,
					'activated' => true,
					'enabled' => true,
					'credentials' => $custom_method,
				]
			);
		}

		$importModel->model->save();

		$importModel->model->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Club())->getMorphClass())
			->first();

		$importModel->model->delete();
		$importModel->delete();
		$importModel->model->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
