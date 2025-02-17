<?php

namespace App\Console;

use App\Jobs\BulbsTasks;
use App\Jobs\DestroyUnusedTags;
use App\Jobs\ExpireUnpaidOnlineReservation;
use App\Jobs\SendRateRequests;
use App\Jobs\CreateInvoices;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	/**
	 * Define the application's command schedule.
	 *
	 * @param  Schedule  $schedule
	 *
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		// $schedule->command('inspire')->hourly();
		$schedule->job(BulbsTasks::class)->everyMinute();
		if (
			in_array(config('app.url'), [
				'https://bookgame.io',
				'https://paneldev.bookgame.io',
				'https://bookgame.io.test',
			])
		) {
			$schedule->job(SendRateRequests::class)->everyMinute();
			$schedule->job(ExpireUnpaidOnlineReservation::class)->everyMinute();
			$schedule->job(DestroyUnusedTags::class)->everySixHours();
		}
        $schedule->job(CreateInvoices::class)->hourly();

		if (config('app.url') === 'https://backup-instance.bookgame.io') {
			$schedule->command('backup:run')->twiceDaily(8, 20);
		}
	}

	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		$this->load(__DIR__ . '/Commands');

		require base_path('routes/console.php');
	}
}
