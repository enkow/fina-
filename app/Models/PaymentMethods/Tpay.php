<?php

namespace App\Models\PaymentMethods;

use App\Enums\PaymentStatus;
use App\Enums\ReservationSlotStatus;
use App\Jobs\ExpireCheckoutSession;
use App\Models\Club;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Parental\HasParent;
use Tpay\OpenApi\Api\TpayApi;
use Tpay\OpenApi\Utilities\TpayException;
use Tpay\OpenApi\Webhook\JWSVerifiedPaymentNotification;

class Tpay extends PaymentMethod
{
	use HasParent;

	public const ADMIN_TAB = 'Tpay';
	public const NAME = 'Tpay';
	public const GLOBAL_SETTINGS = true;

	public static function client(PaymentMethod|null $model = null): TpayApi|null
	{
		if ($model?->club_id === null) {
			return new TpayApi(
				config('services.tpay.id'),
				config('services.tpay.secret'),
				config('services.tpay.production'),
				'read'
			);
		} elseif (empty($model->credentials)) {
			return null;
		} else {
			$credentials = (object) $model->credentials;

			return new TpayApi(
				$credentials?->merchant_id ?? '',
				$credentials?->merchant_secret ?? '',
				config('services.tpay.production'),
				'read'
			);
		}
	}

	public function getClient()
	{
		return self::client($this);
	}

	public function getClientByClub(Club $club)
	{
		return self::client(
			$club
				->paymentMethods()
				->where('enabled', true)
				->where('type', strtolower(self::NAME))
				->first()
		);
	}

	public static function connect(Request $request, Club $club)
	{
		$method = self::firstOrCreate(
			[
				'club_id' => $club->id,
			],
			[
                'enabled' => true,
                'online' => true,
			]
		);

		$method->credentials = $request->only(['merchant_id', 'merchant_secret', 'confirmation_code']);
		$method->save();
		$method->refresh();

		$client = self::client($method);

		if ($client === null) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => 'Wprowadzono błędne dane',
				]);
		}

		try {
			$client->authorization();
			$method->update([
				'activated' => true,
			]);
			$method->club->flushCache();
            $method->flushCache();
			return redirect()
				->back()
				->with('message', [
					'type' => 'info',
					'content' => 'Poprawnie połączono',
				]);
		} catch (\Exception $exception) {
			$method->update([
				'activated' => false,
			]);
			$method->club->flushCache();
            $method->flushCache();
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => 'Wprowadzono błędne dane',
				]);
		}
	}

	public static function disconnect(Request $request, Club $club)
	{
		$method = self::where('club_id', $club->id)
			->where('online', true)
			->first();

		if (!$method || !$method->activated) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => __('online-payments.not-connected'),
				]);
		}
		$method->update(['activated' => false, 'enabled' => false]);

        $method->flushCache();
        $method->club->flushCache();

        return redirect()
			->back()
			->with('message', [
				'type' => 'success',
				'content' => __('Zezaktywowano płatności'),
			]);
	}

	public function refundPayment(Payment $payment, int $amount): array
	{
		if (!$payment->external_id) {
			return [
				'refunded' => false,
				'message' => null,
			];
		}

		$club = $payment->paymentMethod->club;

		if ($club) {
			$TpayClient = self::getClientByClub($club);
		} else {
			$TpayClient = self::client();
		}

		if (
			$payment->payable instanceof Reservation &&
			($amount === $payment->total ||
				!$payment->payable
					->reservationSlots()
					->whereNull('refund_id')
					->exists())
		) {
			$fields = [];
			$refund = $TpayClient
				->transactions()
				->createRefundByTransactionId($fields, $payment->external_id);
			if ($refund['result'] === 'success') {
				$payment->update(['status' => PaymentStatus::Refunded]);
			}
		} else {
			$fields = [
				'amount' => $amount / 100,
			];
			$refund = $TpayClient
				->transactions()
				->createRefundByTransactionId($fields, $payment->external_id);
			if ($refund['result'] === 'success') {
				$payment->update(['status' => PaymentStatus::PartiallyRefunded]);
			}
		}

		return [
			'refunded' => $refund['result'] === 'success',
			'message' => __('refund.refund-has-been-approved-successfully'),
		];
	}

	public function createPaymentUrl(Payment $payment, string $return_url, array $preloadedData = []): string
	{
		$TpayClient = $this->getClient();

		$payable = $preloadedData['reservation'] ?? $payment->payable;

		if ($payable instanceof Reservation) {
			$customer = Customer::getCustomer($payable->customer_id);
			$unregisteredCustomerData = $payable->unregistered_customer_data;
			$payer = [
				'email' => $customer->email ?? ($unregisteredCustomerData['email'] ?? ''),
				'name' =>
					$customer->full_name ??
					($unregisteredCustomerData['first_name'] ?? '') .
						' ' .
						($unregisteredCustomerData['last_name'] ?? ''),
				'phone' => $customer->phone ?? ($unregisteredCustomerData['phone'] ?? ''),
			];
			$lang = $payable->locale ?? 'pl';
		} else {
			$payer = [
				'email' => 'no-reply@bookgame.io',
				'name' => 'Sample payer',
			];
			$lang = 'pl';
		}

		$availableLangs = ['en', 'pl', 'de', 'fr', 'ru', 'it', 'es', 'uk'];
		$tpayLang = in_array(strtolower($lang), $availableLangs) ? strtolower($lang) : 'pl';

		$fields = [
			'description' => $payment->getTitle($tpayLang, $preloadedData),
			'hiddenDescription' => "$payment->id",
			'payer' => $payer,
			'amount' => $payment->total / 100,
			'lang' => $tpayLang,
			'callbacks' => [
				'payerUrls' => [
					'success' => $return_url,
					'error' => $return_url,
				],
				'notification' => [
					'url' => route('api.online-payments.webhook', ['type' => 'tpay']),
				],
			],
		];

		$result = $TpayClient->transactions()->createTransaction($fields);

		$payment->update(['external_id' => $result['transactionId']]);

		dispatch(new ExpireCheckoutSession($payment))->delay(now()->addMinutes(6));

		return $result['transactionPaymentUrl'] ?? '';
	}

	public static function webhook(Request $request)
	{
		try {
			$data = new JWSVerifiedPaymentNotification(
				config('services.tpay.confirmation_code'),
				config('services.tpay.production')
			);
			$data = $data->getNotification();

			$payment = Payment::find($data->tr_crc->getValue());
			info('---');
			info($data->tr_crc->getValue());

			if (!$payment) {
				info(0);
				return response()->json(false);
			}

			$TpayClient = self::client($payment->paymentMethod);

			$transaction = $TpayClient->transactions()->getTransactionById($payment->external_id);
			$transactionDate = $transaction['date'];
			$status = strtolower($transaction['status']);
			info($status);

			if ($data->tr_id->getValue() !== $transaction['title']) {
				$payment->fail();
				info(1);
				return response()->json(false);
			}

			if (!in_array($status, ['correct', 'paid'])) {
				$payment->fail();
				info(2);
				return response()->json(false);
			}

			if ($status === 'paid') {
				if (
					now()
						->parse($transactionDate['creation'])
						->diffInMinutes(now()->parse($transactionDate['realization'])) > 5
				) {
					$payment->fail();
					info(3);
					return response()->json(false);
				}
				if ($payment->status !== PaymentStatus::Pending) {
					$payment->fail();
					info(4);
					return response()->json(false);
				}
				return response()->json(true);
			}

			info(5);
			$payment->success();
			info(6);
			return response()->json(true);
		} catch (TpayException $exception) {
			info($exception->getMessage());
			return response()->json(false);
		}
	}

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
}
