<?php

namespace App\Observers;

use App\Models\Feature;
use Illuminate\Support\Facades\Cache;

class FeatureObserver
{
	public function created(Feature $feature): void
	{
		if (empty($feature->data)) {
			$feature->insertDefaultData();
		}
		Cache::flush();
	}

	public function updated(Feature $feature): void
	{
		Cache::flush();
	}

	/**
	 * Handle the Feature "created" event.
	 *
	 * @param  Feature  $feature
	 *
	 * @return void
	 */
	public function deleting(Feature $feature): void
	{
		$feature->settings()->delete();
		Cache::flush();
	}
}
