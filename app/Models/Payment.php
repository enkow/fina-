<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\ReservationSlotStatus;
use App\Events\ReservationStatusChanged;
use App\Events\ReservationStored;
use App\Interfaces\Payable;
use App\Notifications\Customer\ReservationStoredNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Lang;
use InvalidArgumentException;

class Payment extends Model
{
	protected $guarded = ['id'];
	protected $casts = ['status' => PaymentStatus::class];

	public static function for(Payable $payable, PaymentMethod $method, array $preloadedData = []): self
	{
		if ($payable->paid_at !== null) {
			throw new InvalidArgumentException('Payable is already paid');
		}

		if (!$method->online) {
			throw new InvalidArgumentException('To create a payment, the payment method must be online');
		}

		return tap(
			self::make([
				'payable_id' => $payable->id,
				'payable_type' => $payable->getMorphClass(),
				'total' => $payable->getPaymentTotal(),
				'commission' => $payable->getPaymentCommission(
					null,
					$preloadedData['firstReservationSlot'] ?? null
				),
				'currency' => $payable->getPaymentCurrency(),
				'status' => PaymentStatus::Pending,
			]),
			static fn(self $payment) => $payment
				->paymentMethod()
				->associate($method)
				->save()
		);
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class);
	}

	public function payable(): MorphTo
	{
		return $this->morphTo();
	}

	public function getTitle($translate = null, array $preloadedData = []): string
	{
		$translationPrefix = 'online-payments';
		$translationSuffix = 'reservation-payment';

		$translationForMethod =
			$translationPrefix . '.' . $this->paymentMethod->type . '.' . $translationSuffix;
		$translationUniversal = $translationPrefix . '.' . $translationSuffix;

		return match ($this->payable_type) {
			Reservation::class => __(
				Lang::has($translationForMethod) ? $translationForMethod : $translationUniversal,
				[
					'reservation_number' => (
						$preloadedData['reservation'] ?? $this->payable
					)->getNumberWithPreloadedReservation($preloadedData['firstReservationSlot'] ?? null),
					'club_name' => ($preloadedData['reservation'] ?? $this->payable)->getClub(
						$preloadedData['firstReservationSlot'] ??
							(isset($preloadedData['reservation'])
								? $preloadedData['reservation']?->reservationSlots->first()
								: null)
					)->name,
				],
				$translate
			),
			default => throw new InvalidArgumentException('Unknown payable type'),
		};
	}

	public function getUrl(string $return_url, array $preloadedData = []): string
	{
		$this->loadMissing('paymentMethod');

		return $this->paymentMethod->createPaymentUrl($this, $return_url, $preloadedData);
	}

	public function refund(int $amount): array
	{
		if ($this->payable_type !== Reservation::class) {
			throw new InvalidArgumentException('Only reservations can be refunded');
		}

		if (!in_array($this->status, [PaymentStatus::Paid, PaymentStatus::PartiallyRefunded], true)) {
			throw new InvalidArgumentException('Payment must be paid to be refunded');
		}

		$this->loadMissing('paymentMethod');

		return $this->paymentMethod->refundPayment($this, $amount);
	}

	public function success(): void
	{
		$this->update([
			'status' => PaymentStatus::Paid,
			'provider_commission' => $this->paymentMethod->calcProviderCommission($this),
		]);

		$this->payable()->update([
			'paid_at' => now(),
			'provider_commission' => $this->payable->provider_commission + $this->provider_commission,
		]);

		if ($this->payable instanceof Reservation) {
			$this->payable->reservationSlots()->update([
				'status' => ReservationSlotStatus::Confirmed,
			]);

			$this->payable->sendStoreNotification();
            $payable = Reservation::find($this->payable->id);
			event(new ReservationStatusChanged($payable));
			event(new ReservationStored($payable));
		}
	}

	public function fail(): void
	{
		$this->update([
			'status' => PaymentStatus::Failed,
		]);
	}
}
