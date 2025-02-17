<?php

namespace App\Observers;

use App\Models\HelpItem;
use Illuminate\Support\Facades\Storage;

class HelpItemObserver
{
	/**
	 * Handle the HelpItem "updated" event.
	 *
	 * @param  HelpItem  $helpItem
	 *
	 * @return void
	 */
	public function updated(HelpItem $helpItem): void
	{
		if (
			isset($helpItem->getChanges()['thumbnail']) &&
			HelpItem::where('thumbnail', $helpItem->getOriginal()['thumbnail'])->count() === 1
		) {
			Storage::disk('helpItemThumbnails')->delete($helpItem->getOriginal()['thumbnail']);
		}
	}

	/**
	 * Handle the HelpItem "deleting" event.
	 *
	 * @param  HelpItem  $helpItem
	 *
	 * @return void
	 */
	public function deleting(HelpItem $helpItem): void
	{
		foreach ($helpItem->helpItemImages as $helpItemImage) {
			$helpItemImage->delete();
		}

		if ($helpItem->thumbnail && HelpItem::where('thumbnail', $helpItem->thumbnail)->count() === 1) {
			Storage::disk('helpItemThumbnails')->delete($helpItem->thumbnail);
		}
	}
}
