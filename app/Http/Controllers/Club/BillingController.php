<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\UpdateBillingDetailsRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Club;
use App\Models\Country;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\PaymentMethod;

class BillingController extends Controller
{
	public function index()
	{
		$club = auth()->user()->club;

		$period = $club->getBillingPeriod();
		$nextInvoice = [
			'date' => '',
			'type' => '',
			'price' => 0,
			'from' => $period['month']['start_at'] ?? $period['year']['start_at'],
		];
		$activeInvoice = true;

		$periodMonthPayment =
			$period['month'][$period['payment_time']] !== null
				? Carbon::parse($period['month'][$period['payment_time']])
				: null;
		$periodYearPayment =
			$period['year'][$period['payment_time']] !== null
				? Carbon::parse($period['year'][$period['payment_time']])
				: null;

		if ($periodMonthPayment === null && $periodYearPayment === null) {
			$activeInvoice = false;
            $currentInvoice = [
                'date' => '',
                'type' => 'month',
                'price' => $club
                    ->products()
                    ->wherePivot('period', 'month')
                    ->sum('cost'),
                'from' => '',
            ];
			$nextInvoice = [
				'date' => '',
				'from' => '',
				'type' => 'month',
				'price' => $club
					->products()
					->wherePivot('period', 'month')
					->sum('cost'),
			];
		} elseif ($periodMonthPayment !== null && $periodMonthPayment->eq($periodYearPayment)) {
			$currentInvoice = [
				'date' => now()->parse($period['month']['end_at'])->subMonth()->format("Y-m-d"),
				'type' => 'month',
				'price' => $club->products()->sum('cost'),
				'from' => now()->parse($period['month']['start_at'])->subMonth()->format("Y-m-d"),
			];
			$nextInvoice = [
				'date' => $period['month']['end_at'],
				'type' => 'month',
				'price' => $club->products()->sum('cost'),
				'from' => $period['month']['start_at'],
			];
		} elseif (
			($periodMonthPayment === null || $periodMonthPayment->gt($periodYearPayment)) &&
			$period['year'][$period['payment_time']] !== null
		) {
			$nextInvoice = [
				'date' => $period['year']['end_at'],
				'type' => 'year',
				'price' => $club
					->products()
					->wherePivot('period', 'year')
					->sum('cost'),
				'from' => $period['year']['start_at'],
			];
			$currentInvoice = [
				'date' => now()->parse($period['year']['end_at'])->subYear()->format("Y-m-d"),
				'type' => 'year',
				'price' => $club
					->products()
					->wherePivot('period', 'year')
					->sum('cost'),
				'from' => now()->parse($period['year']['start_at'])->subYear()->format("Y-m-d"),
			];
		} elseif (
			($periodYearPayment === null || $periodMonthPayment->lt($periodYearPayment)) &&
			$period['month'][$period['payment_time']] !== null
		) {
            $nextInvoice = [
                'date' => $period['month']['end_at'],
                'type' => 'month',
                'price' => $club
                    ->products()
                    ->wherePivot('period', 'month')
                    ->sum('cost'),
                'from' => $period['month']['start_at'],
            ];
            $currentInvoice = [
                'date' => now()->parse($period['month']['end_at'])->subMonth()->format("Y-m-d"),
                'type' => 'month',
                'price' => $club
                    ->products()
                    ->wherePivot('period', 'month')
                    ->sum('cost'),
                'from' => now()->parse($period['month']['start_at'])->subMonth()->format("Y-m-d"),
            ];
		}

		club()->reloadFakturowniaInvoicesData();
		$invoices = club()
			->invoices()
			->whereNotNull('fakturownia_id')
			->where(function ($query) {
				$query->whereNotNull('sent_at');
				$query->orWhereNotNull('paid_at');
			})
			->orderBy('id', 'desc')
			->paginate(10);
		$invoices = InvoiceResource::collection($invoices);

		$lastInvoice = $club
			->invoices()
			->get()
			->last();

		return inertia('Club/Billing/Index', [
			'activePaymentMethod' => $club->stripe_customer_id
				? Customer::retrieve($club->stripe_customer_id)->invoice_settings->default_payment_method
				: null,
			'activeCountries' => Country::where('active', 1)
				->pluck('code', 'id')
				->toArray(),
			'paymentMethods' => $club->stripe_customer_id
				? collect(
					PaymentMethod::all([
						'customer' => $club->stripe_customer_id,
					])->data
				)->map(
					fn($method) => [
						'id' => $method->id,
						'type' => $method->type,
						'paypal' =>
							$method->type === 'paypal' ? ['email' => $method->paypal->payer_email] : null,
						'card' =>
							$method->type === 'card'
								? [
									'brand' => $method->card->brand,
									'last4' => $method->card->last4,
									'exp_month' => $method->card->exp_month,
									'exp_year' => $method->card->exp_year,
								]
								: null,
					]
				)
				: [],
			'hasBillingDetails' => with($club, static function ($club) {
				foreach (
					[
						'stripe_customer_id',
						'billing_name',
						'billing_address',
						'billing_postal_code',
						'billing_city',
					]
					as $attribute
				) {
					if (blank($club->{$attribute})) {
						return false;
					}
				}

				return true;
			}),
			'subscriptionDetails' => [
				'price' => [
					'year' => $club
						->products()
						->wherePivot('period', 'year')
						->sum('cost'),
					'month' => $club
						->products()
						->wherePivot('period', 'month')
						->sum('cost'),
				],
				'active' => $activeInvoice,
				'currency' => 'PLN',
				'nextInvoice' => $nextInvoice,
				'currentInvoice' => $currentInvoice,
			],
			'invoices' => $invoices,
			'lastPaymentStatus' => count($club->invoices) === 0 || $lastInvoice?->paid_at !== null,
		]);
	}

	public function updateDetails(UpdateBillingDetailsRequest $request)
	{
		club()->update($request->validated());
		$club = $request->user()->club->refresh();

		$customerData = [
			'name' => $club->billing_name,
			'email' => $club->email,
			'phone' => $club->phone_number,
			'address' => [
				'line1' => $club->billing_address,
				'postal_code' => $club->billing_postal_code,
				'city' => $club->billing_city,
				'country' => $club->country->code,
			],
			'tax' => [
				'ip_address' => $request->ip(),
			],
			'metadata' => [
				'club_id' => $club->id,
			],
		];

		if (!empty($club->stripe_customer_id)) {
			Customer::update($club->stripe_customer_id, $customerData);
		} else {
			$customer = Customer::create($customerData);

			$club->update([
				'stripe_customer_id' => $customer->id,
			]);
		}
		$club->flushCache();

		return redirect()
			->route('club.billing.index')
			->with('message', [
				'type' => 'info',
				'content' => __('billing.details-updated'),
			]);
	}

	public function addMethod()
	{
		$club = auth()->user()->club;

		if (blank($club->stripe_customer_id)) {
			return redirect()
				->route('club.billing.index')
				->with('message', [
					'type' => 'error',
					'content' => __('billing.no-details'),
				]);
		}

		$session = Session::create([
			'mode' => 'setup',
			'success_url' => route('club.billing.index'),
			'cancel_url' => route('club.billing.index'),
			'customer' => $club->stripe_customer_id,
			'payment_method_types' => ['card', 'paypal'],
			'metadata' => [
				'club_id' => $club->id,
			],
		]);

		return redirect()->to($session->url);
	}

	public function removeMethod()
	{
		$club = auth()->user()->club;

		if (blank($club->stripe_customer_id)) {
			return redirect()
				->route('club.billing.index')
				->with('message', [
					'type' => 'error',
					'content' => __('billing.no-details'),
				]);
		}

		$id = request()->validate([
			'id' => 'required|string',
		])['id'];

		PaymentMethod::retrieve($id)->detach();

		$customer = Customer::retrieve($club->stripe_customer_id);

		if (blank($customer->invoice_settings->default_payment_method)) {
			$methods = PaymentMethod::all([
				'customer' => $club->stripe_customer_id,
			])->data;

			if (count($methods) > 0) {
				Customer::update($club->stripe_customer_id, [
					'invoice_settings' => [
						'default_payment_method' => $methods[0]->id,
					],
				]);
			}
		}

		return redirect()
			->route('club.billing.index')
			->with('message', [
				'type' => 'info',
				'content' => __('billing.method-removed'),
			]);
	}

	public function selectMethod()
	{
		$club = auth()->user()->club;

		if (blank($club->stripe_customer_id)) {
			return redirect()
				->route('club.billing.index')
				->with('message', [
					'type' => 'error',
					'content' => __('billing.no-details'),
				]);
		}

		$id = request()->validate([
			'id' => 'required|string',
		])['id'];

		Customer::update($club->stripe_customer_id, [
			'invoice_settings' => [
				'default_payment_method' => $id,
			],
		]);

		return redirect()
			->route('club.billing.index')
			->with('message', [
				'type' => 'info',
				'content' => __('billing.method-selected'),
			]);
	}

	public function activateSubscription()
	{
		$club = auth()->user()->club;

		if (blank($club->stripe_customer_id)) {
			return redirect()
				->route('club.billing.index')
				->with('message', [
					'type' => 'error',
					'content' => __('billing.no-details'),
				]);
		}

		$lastInvoice = $club
			->invoices()
			->get()
			->last();

		if (!empty($lastInvoice) && !$lastInvoice?->paid_at) {
			try {
				$lastInvoice->charge(true);
				$club->subscription_active = true;
				$club->save();
			} catch (\Exception $e) {
				$lastInvoice->fail();
				return redirect()
					->route('club.billing.index')
					->with('message', [
						'type' => 'error',
						'content' => __('billing.subscription-payment-declined'),
					]);
			}

			return redirect()
				->route('club.billing.index')
				->with('message', [
					'type' => 'info',
					'content' => __('billing.modal.subscription-activate'),
				]);
		} else {
			$club->subscription_active = true;
			$club->save();
		}
        foreach($club->users as $user) {
            Cache::forget('user:' . $user->id . ':data');
        }

		return redirect()
			->route('club.billing.index')
			->with('message', [
				'type' => 'info',
				'content' => __('billing.modal.subscription-activate'),
			]);
	}

	public function cancelSubscription()
	{
		$club = auth()->user()->club;

		if (blank($club->stripe_customer_id)) {
			return redirect()
				->route('club.billing.index')
				->with('message', [
					'type' => 'error',
					'content' => __('billing.no-details'),
				]);
		}

		$club->subscription_active = false;
		$club->save();
        foreach($club->users as $user) {
            Cache::forget('user:' . $user->id . ':data');
        }

		return redirect()
			->route('club.billing.index')
			->with('message', [
				'type' => 'info',
				'content' => __('billing.modal.subscription-cancel'),
			]);
	}
}
