<?php

namespace App\Jobs;

use App\Enums\ReservationSlotCancelationType;
use App\Enums\ReservationSlotStatus;
use App\Events\ReservationStatusChanged;
use App\Models\Payment;
use App\Notifications\Customer\ReservationExpiredNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Stripe\Checkout\Session;

class ExpireCheckoutSession implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(public Payment $payment)
	{
	}

	public function handle(): ?Session
	{
		try {
			$notifiable = $this->payment->payable->reservation->customer;
			if ($notifiable === null && isset($reservation->unregistered_customer_data['email'])) {
				$notifiable = (new AnonymousNotifiable())->route(
					'mail',
					$reservation->unregistered_customer_data['email']
				);
			}
			$sendExpirationMail = false;
			foreach ($this->payment->payable->reservationSlots as $reservationSlot) {
				if ($reservationSlot->status !== ReservationSlotStatus::Confirmed) {
					$sendExpirationMail = true;
					$numberModel = $reservationSlot->numberModel();
					if ($numberModel) {
						$numberModel->cancel(
							true,
							ReservationSlotCancelationType::System,
							null,
							true,
							ReservationSlotStatus::Expired
						);
					}
					event(new ReservationStatusChanged($this->payment->payable));
				}
			}
			if ($sendExpirationMail) {
				$notifiable->notify(
					new ReservationExpiredNotification(
						$this->payment->payable->firstReservationSlot->numberModel()
					)
				);
			}

			$paymentMethod = $this->payment->paymentMethod;
			if ($paymentMethod->type === 'stripe') {
				$external_account = $paymentMethod->external_id;
				$isExternal = $paymentMethod->club_id !== null;
				if ($isExternal && !$external_account) {
					throw new RuntimeException(
						"No external account found for payment method ({$this->payment->payment_method_id})"
					);
				}
				$expire = Session::retrieve(
					$this->payment->external_id,
					$isExternal
						? [
							'stripe_account' => $external_account,
						]
						: []
				);

				return $expire->expire();
			}
		} catch (\Throwable $e) {
			report($e);
		}

		return null;
	}
}
