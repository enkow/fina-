<?php

namespace App\Observers;

use App\Models\DataChange;
use App\Models\Set;

class SetObserver
{
	public function created(Set $set): void
	{
		$set->update([
			'creator_id' => auth()->user()?->id,
		]);
		$set->club->flushCache();
	}

	public function updated(Set $set): void
	{
		DataChange::storeChange($set);
		$set->club->flushCache();
	}

	public function deleted(Set $set): void
	{
		$set->club->flushCache();
	}
}
