<?php

namespace App\Observers;

use App\Models\HelpItemImage;
use Illuminate\Support\Facades\Storage;

class HelpItemImageObserver
{
	/**
	 * Handle the HelpItem "deleted" event.
	 *
	 * @param HelpItemImage $helpItemImage
	 *
	 * @return void
	 */
	public function deleted(HelpItemImage $helpItemImage): void
	{
		Storage::disk('helpItemImages')->delete($helpItemImage->path);
	}
}
