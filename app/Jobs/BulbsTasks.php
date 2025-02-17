<?php

namespace App\Jobs;

use App\Models\BulbAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulbsTasks implements ShouldQueue
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
		$actions = BulbAction::where('run_at', '<=', now('UTC'))->whereNull('deleted_at')->get();

		foreach ($actions as $action) {
			BulbRunAction::dispatch($action);
		}
		RemoveOldBulbActions::dispatch();
	}
}
