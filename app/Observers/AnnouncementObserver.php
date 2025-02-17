<?php

namespace App\Observers;

use App\Models\Announcement;

class AnnouncementObserver
{
	public function created(Announcement $announcement): void
	{
		$announcement->club->flushCache();
	}

	public function updated(Announcement $announcement): void
	{
		$announcement->club->flushCache();
	}

	public function deleted(Announcement $announcement): void
	{
		$announcement->club->flushCache();
	}
}
