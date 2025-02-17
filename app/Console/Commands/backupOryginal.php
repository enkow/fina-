<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class backupOryginal extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'backup:oryginal';

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

		setEnv('DB_CONNECTION', 'mysql');

		$this->info("\n\n Database: master");

		return Command::SUCCESS;
	}
}
