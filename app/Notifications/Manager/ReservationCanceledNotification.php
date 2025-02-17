<?php

namespace App\Notifications\Manager;

use App\Enums\ReservationSlotCancelationType;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\ReservationNumber;
use App\Models\TablePreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCanceledNotification extends Notification implements ShouldQueue
{
	use Queueable;

	public function __construct(
		protected ReservationNumber $reservationNumber,
		protected bool $refunded = false
	) {
	}

	public function via(mixed $notifiable): array
	{
		return ['mail'];
	}

	public function toMail(mixed $notifiable): MailMessage
	{
		$country = Country::getCountry(
			$this->reservationNumber->numerable->firstReservationSlot->slot->pricelist->club->country_id
		);
		app()->setLocale($this->reservationNumber->numerable->reservation->locale);
		$reservationData = TablePreference::getDataArrayFromModel(
			$this->reservationNumber
				->numerable()
				->first()
				->prepareForOutput(true),
			array_merge(
				Reservation::tableData(gameId: $this->reservationNumber->numerable->game->id)['preference'],
				[['key' => 'extended', 'enabled' => true]]
			)
		);
		$reservationData['country_id'] = $country->id;

		$reservationData['cancelation_reason'] =
			$this->reservationNumber->numerable->firstReservationSlot->cancelation_reason;
		$emailData = [
			'greeting' => __('reservation.reservation-canceled'),
			'markdown' => false,
			'level' => 'primary',
			'reservationData' => $reservationData,
		];
		if ($reservationData['payment_method_online']) {
			$emailData['afterGreeting'] = match ($this->refunded) {
				true => __('reservation.canceled-notification.refunded-info'),
				false => __('reservation.canceled-notification.not-refunded-info'),
			};
		}

		return (new MailMessage())
			->subject(config('app.name') . ' - ' . __('reservation.reservation-canceled'))
			->markdown('notifications::email', $emailData);
	}
}
