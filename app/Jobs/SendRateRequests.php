<?php

namespace App\Jobs;

use App\Enums\ReminderMethod;
use App\Enums\ReminderType;
use App\Enums\ReservationSlotStatus;
use App\Mail\RateRequest;
use App\Models\Reservation;
use App\Notifications\Customer\ReservationRatingRequestNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRateRequests implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		Reservation::whereDoesntHave('reminders', function ($query) {
			$query->where('type', ReminderType::RatingRequest);
		})
			->whereHas('reservationSlots', function ($query) {
				$query->where('status', ReservationSlotStatus::Confirmed);
				$query->where('end_at', '<', now('UTC') /*->subMinutes(5)*/);
			})
			->take(10)
			->get()
			->each(function (Reservation $reservation) {
				$reservationNumber =
					$reservation->reservationNumber ?? $reservation->firstReservationSlot->reservationNumber;

				$notifiable = $reservation->customer;
				if ($notifiable === null && isset($reservation->unregistered_customer_data['email'])) {
					$notifiable = (new AnonymousNotifiable())->route(
						'mail',
						$reservation->unregistered_customer_data['email']
					);
				}
				$notifiable?->notify(new ReservationRatingRequestNotification($reservationNumber));

				$reminder = $reservation->reminders()->create([
					'type' => ReminderType::RatingRequest,
					'method' => ReminderMethod::Mail,
				]);

				$reminder->remindable()->associate($reservation);
			});
	}
}
