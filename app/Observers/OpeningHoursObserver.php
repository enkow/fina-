<?php

namespace App\Observers;

use App\Models\DataChange;
use App\Models\OpeningHours;

class OpeningHoursObserver
{
	public function updated(OpeningHours $openingHours): void
	{
		DataChange::storeChange($openingHours);
		if (club()) {
			club()->flushCache();
		}
	}

	public function created(OpeningHours $openingHours): void
	{
		if (club()) {
			club()->flushCache();
		}
	}
}
