<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Feature;
use App\Models\Game;
use App\Models\Translation;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TranslateDb extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'translate:db {country}';

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
		$fromCountry = Country::where('code', 'GB')->first();
		$toCountry = Country::where('code', $this->argument('country'))->first();
		$toCountryLocale = $toCountry->locale;
		$toCountry->translations()->delete();

		$globalTranslations = Translation::retrieve($fromCountry->id, null, null);
		foreach ($globalTranslations as $key => $val) {
			if (is_array($val)) {
				continue;
			}
			$toCountry->translations()->create([
				'key' => $key,
				'value' => $this->translateString($val, 'en', $toCountryLocale),
			]);
		}

		foreach (Game::all() as $game) {
			foreach (Translation::retrieve($fromCountry->id, $game->id, null) as $key => $val) {
				if (is_array($val)) {
					continue;
				}
				$game->translations()->create([
					'country_id' => $toCountry->id,
					'key' => $key,
					'value' => $this->translateString($val, 'en', $toCountryLocale),
				]);
			}
		}

		foreach (Feature::all() as $feature) {
			foreach (Translation::retrieve($fromCountry->id, null, $feature->id) as $key => $val) {
				if (is_array($val)) {
					continue;
				}
				$feature->translations()->create([
					'country_id' => $toCountry->id,
					'key' => $key,
					'value' => $this->translateString($val, 'en', $toCountryLocale),
				]);
			}
		}

		$this->info("Translations for country '$toCountry->code' have been created.");
	}

	function translateString($text, $from, $to)
	{
		if (!is_array($to)) {
			$to = [$to];
		}

		$baseUrl = config('deepltranslator.deepl_url');
		$translated = [];

		foreach ($to as $toLang) {
			$params = [
				'tag_handling' => 'xml',
				'ignore_tags' => 'ignore,ignore-filename,ignore-index',
				'source_lang' => strtoupper($from),
				'target_lang' => strtoupper($toLang),
				'auth_key' => config('deepltranslator.deepl_api_key'),
				'formality' => config('deepltranslator.formality'),
				'preserve_formatting' => config('deepltranslator.preserve_formatting'),
			];
			$body = http_build_query($params) . '&text=' . $text;

			$client = new Client();
			$response = $client->post($baseUrl, [
				'body' => $body,
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
			]);

			$result = json_decode($response->getBody()->getContents());
			if (isset($result->translations)) {
				if (isset($result->translations[0])) {
					$translated[$toLang] = $result->translations[0]->text;
				}
			}
		}

		return $translated[$to[0]];
	}
}
