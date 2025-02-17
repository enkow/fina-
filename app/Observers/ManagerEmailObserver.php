<?php

namespace App\Observers;

use App\Models\ManagerEmail;
use Illuminate\Support\Facades\Cache;

class ManagerEmailObserver
{
	private function clearManagerEmailsCache($clubId): void
	{
		Cache::forget('club:' . $clubId . ':manager_emails');
	}

	public function created(ManagerEmail $managerEmail): void
	{
		$this->clearManagerEmailsCache($managerEmail->club_id);
	}

	public function updated(ManagerEmail $managerEmail): void
	{
		$this->clearManagerEmailsCache($managerEmail->club_id);
	}

	public function deleted(ManagerEmail $managerEmail): void
	{
		$this->clearManagerEmailsCache($managerEmail->club_id);
	}
}
