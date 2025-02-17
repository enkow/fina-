<?php

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationStatusChanged implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public int $status;
	public string $reservationNumber;

	public function __construct(Reservation $reservation)
	{
		$this->reservationNumber = $reservation->number;
		$this->status = $reservation->firstReservationSlot->status->value;
        info(now('UTC')->diffInSeconds($reservation->getRawOriginal('created_at')));
		if (now('UTC')->diffInSeconds($reservation->getRawOriginal('created_at')) > 3) {
			event(new CalendarDataChanged($reservation->firstReservationSlot->slot->pricelist->club));
		}
	}

	public function broadcastOn(): array
	{
		return ['reservation-number-' . $this->reservationNumber];
	}

	public function broadcastAs(): string
	{
		return 'reservation-status-changed';
	}
}
