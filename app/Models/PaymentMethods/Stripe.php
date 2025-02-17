<?php

namespace App\Models\PaymentMethods;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Jobs\ExpireCheckoutSession;
use App\Models\Club;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Parental\HasParent;
use RuntimeException;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Event;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Refund;
use Stripe\Webhook;

class Stripe extends PaymentMethod
{
	use HasParent;

	public const NAME = 'Stripe';
	public const ADMIN_TAB = 'Stripe';
	public const GLOBAL_SETTINGS = false;

	public static function enable(Club $club)
	{
		PaymentMethod::disabledAllOnlineMethod($club);

		self::updateOrCreate(
			[
				'club_id' => $club->id,
			],
			[
				'online' => true,
				'enabled' => true,
			]
		);
	}

	public static function connect(Request $request, Club $club)
	{
		$method = self::firstOrCreate(
			[
				'club_id' => $club->id,
			],
			[
				'online' => true,
                'enabled' => true,
			]
		);
		$method->external_id = null;

		if ($method->activated) {
			return redirect()
				->route('club.online-payments.index')
				->with('message', [
					'type' => 'error',
					'content' => __('online-payments.already-connected'),
				]);
		}

		if (!empty($method->external_id) && ($account = Account::retrieve($method->external_id))) {
			if ($account->details_submitted) {
				return redirect()
					->route('club.online-payments.index')
					->with('message', [
						'type' => 'error',
						'content' => __('online-payments.already-connected'),
					]);
			}
		} else {
			$account = Account::create([
				'type' => 'standard',
				'country' => $club->country->code,
				'default_currency' => $club->country->currency,
				'business_profile' => [
					'name' => $club->name,
					'mcc' => '7941',
				],
			]);

			$method->update(['external_id' => $account->id]);
		}

		$link = AccountLink::create([
			'account' => $method->external_id,
			'type' => 'account_onboarding',
			'refresh_url' => route('club.online-payments.return', ['type' => 'stripe']),
			'return_url' => route('club.online-payments.return', ['type' => 'stripe']),
		]);

        $method->club->flushCache();
        $method->flushCache();

		return Inertia::location($link->url);
	}

	public static function disconnect(Request $request, Club $club)
	{
		$method = self::where('club_id', $club->id)
			->where('online', true)
			->first();

		if (!$method || !$method->activated || !$method->external_id) {
			return redirect()
				->route('club.online-payments.index')
				->with('message', [
					'type' => 'error',
					'content' => __('online-payments.not-connected'),
				]);
		}
		$method->update(['external_id' => null, 'activated' => false, 'enabled' => false]);

        $method->club->flushCache();
        $method->flushCache();
		return redirect()
			->route('club.online-payments.index')
			->with('message', [
				'type' => 'success',
				'content' => __('online-payments.stripe.disconnected'),
			]);
	}

	public static function return(Request $request, Club $club)
	{
		$method = self::firstWhere([
			'club_id' => $club->id,
		]);

		if (!$method || empty($method->external_id)) {
			return redirect()
				->route('club.online-payments.index')
				->with('message', [
					'type' => 'error',
					'content' => __('online-payments.general-error'),
				]);
		}

		$account = Account::retrieve($method->external_id);

		if (!$account->details_submitted || !$account->charges_enabled) {
			return redirect()
				->route('club.online-payments.index')
				->with('message', [
					'type' => 'error',
					'content' => __('online-payments.stripe.no-connection-yet'),
					'timeout' => 4000,
				]);
		}

		$method->update(['activated' => true, 'enabled' => true]);

		return redirect()
			->route('club.online-payments.index')
			->with('message', [
				'type' => 'success',
				'content' => __('online-payments.stripe.connected'),
			]);
	}

	public static function webhook(Request $request)
	{
		$secret = match ($request->get('type')) {
			'connect' => config('services.stripe.webhook_secret.connect'),
			'account' => config('services.stripe.webhook_secret.account'),
			default => throw new RuntimeException('Invalid stripe webhook type'),
		};

		try {
			$event = Webhook::constructEvent(
				$request->getContent(),
				$request->header('stripe-signature'),
				$secret
			);
		} catch (SignatureVerificationException $e) {
			return response()->json(['success' => false], 400);
		}

		switch ($event->type) {
			case Event::ACCOUNT_UPDATED:
				$account = $event->data->object;

				if ($account->details_submitted && $account->charges_enabled) {
					self::firstWhere([
						'external_id' => $account->id,
						'activated' => false,
					])?->update(['activated' => true]);
				}
				break;
			case Event::CHECKOUT_SESSION_ASYNC_PAYMENT_FAILED:
			case Event::CHECKOUT_SESSION_EXPIRED:
				$session = $event->data->object;

				if ($session->mode === 'payment') {
					Payment::firstWhere([
						'id' => $session->client_reference_id,
						'external_id' => $session->id,
					])?->fail();
				}

				break;
			case Event::CHECKOUT_SESSION_COMPLETED:
			case Event::CHECKOUT_SESSION_ASYNC_PAYMENT_SUCCEEDED:
				$session = $event->data->object;

				if ($session->mode === 'setup') {
					$paymentMethods = Customer::allPaymentMethods($session->customer);
					$paymentMethod = $paymentMethods->data[0];

					Customer::update($session->customer, [
						'invoice_settings' => [
							'default_payment_method' => $paymentMethod->id,
						],
					]);
				} elseif ($session->mode === 'payment') {
					if ($session->payment_status === 'paid') {
						Payment::firstWhere([
							'id' => $session->client_reference_id,
							'external_id' => $session->id,
							'status' => PaymentStatus::Pending,
						])?->success();
					}
				}

				break;
			case Event::PAYMENT_METHOD_ATTACHED:
				$paymentMethod = $event->data->object;

				$customer = Customer::retrieve($paymentMethod->customer);

				if (blank($customer->invoice_settings->default_payment_method)) {
					Customer::update($customer->id, [
						'invoice_settings' => [
							'default_payment_method' => $paymentMethod->id,
						],
					]);
				}
				break;
			case Event::PAYMENT_INTENT_CANCELED:
			case Event::PAYMENT_INTENT_REQUIRES_ACTION:
			case Event::PAYMENT_INTENT_SUCCEEDED:
				$intent = $event->data->object;

				Invoice::firstWhere([
					'stripe_payment_intent_id' => $intent->id,
				])?->{$intent->status === 'succeeded' ? 'success' : 'fail'}();

				break;
		}

		return response()->json(['success' => true]);
	}

	public function createPaymentUrl(Payment $payment, string $return_url, array $preloadedData = []): string
	{
		$isExternal = $this->external_id && $this->club_id;

		$session = Session::create(
			[
				'mode' => 'payment',
				'client_reference_id' => $payment->id,
				'success_url' => $return_url,
				'expires_at' => now()->addMinutes(31)->timestamp,
				'payment_intent_data' => [
					'description' => $payment->getTitle(null, $preloadedData ?? []),
					...$isExternal ? ['application_fee_amount' => $payment->commission] : [],
				],
				'locale' => ($preloadedData['reservation'] ?? $payment->payable)->locale,
				'line_items' => [
					[
						'quantity' => 1,
						'price_data' => [
							'currency' => $payment->currency,
							'unit_amount' => $payment->total,
							'product_data' => [
								'name' => $payment->getTitle(null, $preloadedData),
							],
						],
					],
				],
				'custom_text' => [
					'submit' => [
						'message' => __('online-payments.payment-time-limit-notice'),
					],
				],
			],
			$isExternal
				? [
					'stripe_account' => $this->external_id,
				]
				: []
		);

		$payment->update(['external_id' => $session->id]);
		dispatch(new ExpireCheckoutSession($payment))->delay(now()->addMinutes(6));

		return $session->url;
	}

	public function refundPayment(Payment $payment, int $amount): array
	{
		if (!$payment->external_id) {
			return [
				'refunded' => false,
				'message' => null,
			];
		}

		$isExternal = $this->external_id && $this->club_id;

		$session = Session::retrieve(
			$payment->external_id,
			$isExternal
				? [
					'stripe_account' => $this->external_id,
				]
				: []
		);

		if ($session->payment_status !== 'paid') {
			return [
				'refunded' => false,
				'message' => __('refund.reservation-unpaid'),
			];
		}

		try {
			$refund = Refund::create(
				[
					'payment_intent' => $session->payment_intent,
					'amount' => $amount,
				],
				$isExternal
					? [
						'stripe_account' => $this->external_id,
					]
					: []
			);
		} catch (InvalidRequestException $e) {
			if ($e->getStripeCode() === 'charge_already_refunded') {
				return [
					'refunded' => false,
					'message' => __('refund.refund-already-made'),
					'timeout' => 4000,
				];
			}

			throw $e;
		}

		if ($refund->status === 'succeeded') {
			$payment->update(['status' => PaymentStatus::Refunded]);
		}

		return [
			'refunded' => $refund->status === 'succeeded',
			'message' => __('refund.refund-has-been-approved-successfully'),
		];
	}
}
