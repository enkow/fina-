<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class backupUse extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'backup:use';

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
		$this->call('backup:run');

		setEnv('DB_CONNECTION', 'bg-mysql');

		$this->info("\n\n Database: backup");

		return Command::SUCCESS;
	}
}
