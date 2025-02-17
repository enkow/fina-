<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasTimers extends Feature
{
	use HasParent;

	public static string $name = 'has_timers';

	public array $conflictedFeatures = [
		PersonAsSlot::class,
		FullDayReservations::class,
		FixedReservationDuration::class,
	];
}
