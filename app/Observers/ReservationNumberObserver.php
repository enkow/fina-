<?php

namespace App\Observers;

use App\Models\ReservationNumber;

class ReservationNumberObserver
{
	public function created(ReservationNumber $reservationNumber): void
	{
	}
}
