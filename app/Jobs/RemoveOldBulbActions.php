<?php

namespace App\Jobs;

use App\Models\BulbAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveOldBulbActions implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$slotIds = BulbAction::where('run_at', '<=', now('UTC')->subHours(5))
			->onlyTrashed()
			->pluck('slot_id')
			->toArray();
		foreach ($slotIds as $slotId) {
			$lastDeletedBulbAction = BulbAction::onlyTrashed()
				->where('slot_id', $slotId)
				->latest()
				->first();
			BulbAction::where('run_at', '<=', now('UTC')->subHours(5))
				->where('slot_id', $slotId)
				->where('id', '<', $lastDeletedBulbAction->id)
				->onlyTrashed()
				->forcedelete();
		}
	}
}
