<?php

namespace App\Notifications\Manager;

use App\Models\Reservation;
use App\Models\ReservationNumber;
use App\Models\TablePreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationRatedNotification extends Notification implements ShouldQueue
{
	use Queueable;
	public function __construct(public ReservationNumber $reservationNumber)
	{
	}

	public function via($notifiable): array
	{
		return ['mail'];
	}

	public function toMail($notifiable): MailMessage
	{
		app()->setLocale(
			$this->reservationNumber->numerable->firstReservationSlot->slot->pricelist->club->country->locale
		);
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
		$reservationData['rate_service'] = $this->reservationNumber->numerable->reservation->formattedRate(
			'service'
		);
		$reservationData['rate_atmosphere'] = $this->reservationNumber->numerable->reservation->formattedRate(
			'atmosphere'
		);
		$reservationData['rate_staff'] = $this->reservationNumber->numerable->reservation->formattedRate(
			'staff'
		);
		$reservationData['rate_final'] = $this->reservationNumber->numerable->reservation->formattedRate(
			'final'
		);
		$reservationData['rate_content'] = $this->reservationNumber->numerable->reservation->rate_content;
		return (new MailMessage())
			->subject(
				config('app.name') .
					' - ' .
					__('reservation.rated-notification.title', [
						'reservation_number' => $reservationData['number'],
					])
			)
			->markdown('notifications::email', [
				'greeting' => __('reservation.rated-notification.title', [
					'reservation_number' => $reservationData['number'],
				]),
				'markdown' => false,
				'level' => 'primary',
				'reservationData' => $reservationData,
			]);
	}
}
