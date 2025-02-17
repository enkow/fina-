<?php

namespace App\Observers;

use App\Models\Agreement;
use Illuminate\Support\Facades\Cache;

class AgreementObserver
{
	public function created(Agreement $agreement): void
	{
		$this->clearCache($agreement);
	}

	public function updated(Agreement $agreement): void
	{
		$this->clearCache($agreement);
	}

	public function clearCache(Agreement $agreement): void
	{
		$club = $agreement->club;
		Cache::forget('club:' . $club->id . ':agreements');
		Cache::forget('club:' . $club->id . ':calendar_resource');
		Cache::forget('club:' . $club->id . ':widget_resource');
		Cache::forget('club:' . $club->id . ':rendered_settings');
		foreach ($club->users as $user) {
			Cache::forget('user:' . $user->id . ':table_preferences');
		}
	}
}
