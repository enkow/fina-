<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UrlInstance extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UrlInstance:set {name?} ';

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
			'bookgame.io' => 'https://bookgame.io',
			'backup-instance.bookgame.io' => 'https://backup-instance.bookgame.io',
		];
		if (!isset($paramTranslate[$this->argument('name')])) {
			$fail = true;
			$this->line('Aktualnie używany adres aplikacji:' . config('app.url'));

			while ($fail) {
				$inputUrl = $this->anticipate(
					'Wybierz typ bazy danych do użycia (bookgame.io, backup-instance.bookgame.io)',
					['bookgame.io', 'backup-instance.bookgame.io'],
					'bookgame.io'
				);

				if (isset($paramTranslate[$inputUrl])) {
					$fail = false;
					$selectUrl = $paramTranslate[$inputUrl];
				} else {
					$this->error('Błędny adres aplikacji');
				}
			}
		} else {
			$mcdArg = $paramTranslate[$this->argument('name')];

			if (isset($paramTranslate[$mcdArg])) {
				$fail = false;
				$selectUrl = $paramTranslate[$mcdArg];
			} else {
				$this->error('Błędny adres aplikacji');
				return Command::FAILURE;
			}
		}

		setEnv('APP_URL', $selectUrl);
		setEnv('VITE_APP_URL', $selectUrl);

		$this->info("\nZmieniono adres na: $selectUrl");

		return Command::SUCCESS;
	}
}
