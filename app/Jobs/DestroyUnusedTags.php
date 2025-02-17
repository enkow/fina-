<?php

namespace App\Jobs;

use App\Models\Tag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class DestroyUnusedTags implements ShouldQueue
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
		$tagIdsToDelete = Tag::withCount('customers')
			->having('customers_count', '=', 0)
			->pluck('id');
		DB::table('customer_tag')
			->whereIn('tag_id', $tagIdsToDelete)
			->delete();
		Tag::whereIn('id', $tagIdsToDelete)->delete();
	}
}
