<?php

namespace App\Observers;

use App\Models\ReservationType;

class ReservationTypeObserver
{
	public function created(ReservationType $reservationType): void
	{
		club()?->flushCache();
		$reservationType->flushCache();
	}

	public function updated(ReservationType $reservationType): void
	{
		club()?->flushCache();
		$reservationType->flushCache();
	}

	public function deleted(ReservationType $reservationType): void
	{
		club()?->flushCache();
		$reservationType->flushCache();
	}
}
