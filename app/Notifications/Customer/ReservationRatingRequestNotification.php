<?php

namespace App\Notifications\Customer;

use App\Models\Country;
use App\Models\Customer;
use App\Models\ReservationNumber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;

class ReservationRatingRequestNotification extends Notification implements ShouldQueue
{
	use Queueable;

	public function __construct(public ReservationNumber $reservationNumber)
	{
	}

	public function via(mixed $recipient): array
	{
		return ['mail'];
	}

	public function toMail(mixed $recipient): MailMessage
	{
		$club = $this->reservationNumber->numerable->firstReservationSlot->slot->pricelist->club;
		$clubName = $this->reservationNumber->numerable->firstReservationSlot->slot->pricelist->club->name;
		app()->setLocale($this->reservationNumber->numerable->reservation->locale);
		return (new MailMessage())
			->subject(
				config('app.name') .
					' - ' .
					__('reservation.rate-request-notification.title', [
						'club_name' => $clubName,
					])
			)
			->markdown('vendor.notifications.email', [
				'level' => 'primary',
				'actionText' => __('reservation.rate-request-notification.action-text'),
				'actionUrl' => route('widget.customers.reservations.rate', [
					'club' => $club,
					'encryptedCustomerId' => Crypt::encrypt($recipient->id ?? null),
					'reservationNumber' => $this->reservationNumber->id,
				]),
				'introLines' => [
					__('reservation.rate-request-notification.intro-lines.0', ['club_name' => $clubName]),
				],
			]);
	}
}
