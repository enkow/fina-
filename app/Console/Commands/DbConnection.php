<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mpdf\Tag\Q;

class DbConnection extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'dbConnection:set {name?} ';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Zmiana połączenia z bazą danych';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		$paramTranslate = [
			'slave' => 'bu-mysql',
			'master' => 'mysql',
		];
		if (!isset($paramTranslate[$this->argument('name')])) {
			$fail = true;
			$this->line('Aktualnie używana baza danych:' . config('database.default'));

			while ($fail) {
				$inputDB = $this->anticipate(
					'Wybierz typ bazy danych do użycia',
					['slave', 'master'],
					'slave'
				);

				if (isset($paramTranslate[$inputDB])) {
					$fail = false;
					$selectDb = $paramTranslate[$inputDB];
				} else {
					$this->error('Błędny typ bazy danych');
				}
			}
		} else {
			$selectDb = $paramTranslate[$this->argument('name')];
		}

		$this->call('backup:run');

		setEnv('DB_CONNECTION', $selectDb);

		$this->info("\nDatabase: $selectDb");

		return Command::SUCCESS;
	}
}
