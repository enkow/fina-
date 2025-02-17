<?php

namespace App\Jobs;

use App\Enums\BulbReason;
use App\Enums\BulbStatus;
use App\Events\BulbStatusChange;
use App\Models\BulbAction;
use App\Models\Features\SlotHasBulb;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulbRunAction implements ShouldQueue
{
	use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(public BulbAction $bulbAction)
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
		$this->bulbAction->loadMissing('bulbAdapter');
		$bulbAdapter = $this->bulbAction->bulbAdapter;

		if (!$bulbAdapter) {
			$this->bulbAction->delete();
			return;
		}

		if ($this->bulbAction->bulb === null) {
			$this->bulbAction->delete();
			return;
		}

		if ($this->bulbAction->bulb_status === BulbStatus::ON) {
			$result = $bulbAdapter->changeState($this->bulbAction->bulb, true);
		} elseif ($this->bulbAction->bulb_status === BulbStatus::OFF) {
			$result = $bulbAdapter->changeState($this->bulbAction->bulb, false);
		} else {
			$result = $bulbAdapter->isBulbOn($this->bulbAction->bulb);

			if ($result !== null) {
				$this->bulbAction->update(['bulb_status' => $result ? BulbStatus::ON : BulbStatus::OFF]);
			}
			$result = true;
		}

		if ($result === null) {
			return $this->bulbAction->create([
				'bulb_status' => BulbStatus::OFFLINE,
				'run_at' => now(),
				'slot_id' => $this->bulbAction->slot_id,
				'reason' => BulbReason::RESERVATION,
				'deleted_at' => now(),
			]);
		}

		$this->bulbAction->delete();
        SlotHasBulb::deleteOldBulbActions($this->bulbAction->slot);

		event(new BulbStatusChange($this->bulbAction->slot->pricelist->club));
	}
}
