<?php

namespace App\Observers;

use App\Models\DataChange;
use App\Models\OpeningHoursException;

class OpeningHoursExceptionObserver
{
	/**
	 * Handle the OpeningHoursException "created" event.
	 *
	 * @param  OpeningHoursException  $openingHoursException
	 *
	 * @return void
	 */
	public function created(OpeningHoursException $openingHoursException): void
	{
		$openingHoursException->update([
			'creator_id' => auth()->user()?->id,
		]);
		if (club()) {
			club()->flushCache();
		}
	}

	public function updated(OpeningHoursException $openingHoursException): void
	{
		DataChange::storeChange($openingHoursException);
		if (club()) {
			club()->flushCache();
		}
	}

	public function deleted(OpeningHoursException $openingHoursException): void
	{
		if (club()) {
			club()->flushCache();
		}
	}
}
