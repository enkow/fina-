<?php

namespace App\Observers;

use App\Models\DataChange;
use App\Models\Setting;

class SettingObserver
{
	public function updated(Setting $setting): void
	{
		DataChange::storeChange($setting);
		$club = club() ?? request()->route('club');
		if ($club) {
			$club?->flushCache();
		}
	}

	public function stored(Setting $setting): void
	{
		club()?->flushCache();
		$club = club() ?? request()->route('club');
		if ($club) {
			$club?->flushCache();
		}
	}
}
