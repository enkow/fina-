<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TranslateFiles extends Command
{
	protected $signature = 'translate:files {language}';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$newLanguage = $this->argument('language');

		$sourceFolder = 'lang/en';

		if (!is_dir($sourceFolder)) {
			$this->error("Folder 'en' does not exist.");
			return;
		}

		$destinationFolder = 'lang/' . $newLanguage;
		if (!is_dir($destinationFolder)) {
			mkdir($destinationFolder, 0777, true);
		}

		$files = scandir($sourceFolder);
		foreach ($files as $file) {
			if ($file === '.' || $file === '..') {
				continue;
			}

			$translations = require $sourceFolder . '/' . $file;
			$newTranslations = $this->translateArray($translations, 'en', $newLanguage);

			$newContent = "<?php\n\nreturn " . var_export($newTranslations, true) . ";\n";
			file_put_contents($destinationFolder . '/' . $file, $newContent);
		}

		$this->info("Translations for language '$newLanguage' have been created.");
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

	private function translateArray($array, $languageFrom, $languageTo)
	{
		$translatedArray = [];
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$translatedArray[$key] = $this->translateArray($value, $languageFrom, $languageTo);
			} else {
				$translatedArray[$key] = $this->translateString($value, $languageFrom, $languageTo);
			}
		}
		return $translatedArray;
	}
}
