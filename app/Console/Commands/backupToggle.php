<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class backupToggle extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'backup:toggle';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		if (config('database.default') === 'mysql') {
			$this->call('backup:use');
		} else {
			$this->call('backup:oryginal');
		}

		return Command::SUCCESS;
	}
}
