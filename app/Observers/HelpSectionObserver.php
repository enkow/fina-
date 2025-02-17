<?php

namespace App\Observers;

use App\Models\HelpSection;

class HelpSectionObserver
{
	/**
	 * Handle the HelpSection "deleted" event.
	 *
	 * @param  HelpSection  $helpSection
	 *
	 * @return void
	 */
	public function deleting(HelpSection $helpSection): void
	{
		foreach ($helpSection->helpItems as $helpItem) {
			$helpItem->delete();
		}
	}
}
