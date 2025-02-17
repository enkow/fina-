<?php

namespace App\Jobs;

use App\Enums\ReservationSlotCancelationType;
use App\Enums\ReservationSlotStatus;
use App\Events\CalendarDataChanged;
use App\Events\ReservationStatusChanged;
use App\Models\ReservationSlot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireUnpaidOnlineReservation implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$reservationSlots = ReservationSlot::whereHas('reservation', function ($query) {
			$query->whereHas('paymentMethod', function ($query) {
				$query->where('online', true);
			});
			$query->where('created_at', '<', now('UTC')->subMinutes(6));
		})
			->whereIn('status', [ReservationSlotStatus::Pending, ReservationSlotStatus::Expired])
			->whereNull('canceled_at')
			->get();
		foreach ($reservationSlots as $reservationSlot) {
			$reservationSlot
				->numberModel()
				->cancel(
					true,
					ReservationSlotCancelationType::System,
					null,
					true,
					ReservationSlotStatus::Expired
				);
			event(new ReservationStatusChanged($reservationSlot->reservation));
			event(new CalendarDataChanged($reservationSlot->slot->pricelist->club));
		}
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		//
	}
}
