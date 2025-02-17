<?php

namespace App\Events;

use App\Custom\Timezone;
use App\Models\Club;
use App\Models\Customer;
use App\Models\Pricelist;
use App\Models\Reservation;
use App\Models\Slot;
use App\Models\Translation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationStored implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public array $reservationData;
	private string $channel;

	public function __construct(Reservation $reservation)
	{
		$club = $reservation->getClub();
		$this->channel = 'calendar' . $club->id;
		date_default_timezone_set($club->country->timezone);
		$this->reservationData = [
			'game_name' => Translation::retrieveGameNames($club)[$reservation->game_id],
			'number' => $reservation->number,
			'customer_name' => Customer::getCustomer($reservation?->customer_id)->full_name ?? 'Anon',
			'start_at' => $reservation->firstReservationSlot->start_at->format('H:i'),
			'source' => $reservation->source,
			'creator_id' => $reservation->firstReservationSlot->creator_id,
		];
	}

	public function broadcastOn(): string
	{
		return $this->channel;
	}

	public function broadcastAs(): string
	{
		return 'reservation-stored';
	}
}
