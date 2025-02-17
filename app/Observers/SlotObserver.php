<?php

namespace App\Observers;

use App\Models\Slot;
use Illuminate\Support\Facades\Cache;

class SlotObserver
{
	public function created(Slot $slot): void
	{
		$slot->pricelist->club->flushCache();
		Cache::forget('slot:' . $slot->id);
	}
	public function updated(Slot $slot): void
	{
		$slot->pricelist->club->flushCache();
		Cache::forget('slot:' . $slot->id);
	}
	public function deleted(Slot $slot): void
	{
		if ($slot->childrenSlots()->count()) {
			$slot->childrenSlots()->delete();
		}
		$slot->pricelist->club->flushCache();
		Cache::forget('slot:' . $slot->id);
	}
}
