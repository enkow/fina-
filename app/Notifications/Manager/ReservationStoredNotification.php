<?php

namespace App\Notifications\Manager;

use App\Models\Reservation;
use App\Models\ReservationNumber;
use App\Models\TablePreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationStoredNotification extends Notification implements ShouldQueue
{
	use Queueable;

	public function __construct(public ReservationNumber $reservationNumber)
	{
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed  $notifiable
	 *
	 * @return array†
	 */
	public function via(mixed $notifiable): array
	{
		return ['mail'];
	}

	/**
	 * Inform manager about new reservation
	 *
	 * @param  mixed  $notifiable
	 *
	 * @return MailMessage
	 */
	public function toMail(mixed $recipient): MailMessage
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

		return (new MailMessage())
			->subject(config('app.name') . ' - ' . __('reservation.new-reservation'))
			->markdown('notifications::email', [
				'greeting' => __('reservation.new-reservation'),
				'markdown' => false,
				'level' => 'primary',
				'reservationData' => $reservationData,
			]);
	}
}
