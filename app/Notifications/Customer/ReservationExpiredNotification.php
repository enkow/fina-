<?php

namespace App\Notifications\Customer;

use App\Models\Country;
use App\Models\ReservationNumber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationExpiredNotification extends Notification implements ShouldQueue
{
	use Queueable;
	public function __construct(protected ReservationNumber $reservationNumber)
	{
		//
	}

	public function via($notifiable)
	{
		return ['mail'];
	}

	public function toMail(mixed $notifiable): MailMessage
	{
		$clubName = $this->reservationNumber->numerable->firstReservationSlot->slot->pricelist->club->name;
		app()->setLocale($this->reservationNumber->numerable->reservation->locale);

		return (new MailMessage())
			->subject(
				config('app.name') .
					' - ' .
					__('reservation.canceled-notification.title', [
						'reservation_number' => $this->reservationNumber->formatted_number,
						'club_name' => $clubName,
					])
			)
			->markdown('notifications::email', [
				'greeting' => __('reservation.canceled-notification.greeting') . '<br><br>',
				'markdown' => false,
				'level' => 'primary',
				'outroLines' => [
					__('reservation.canceled-notification.outro-lines.0') . '<br><br>',
					__('reservation.canceled-notification.outro-lines.1'),
				],
			]);
	}
}
