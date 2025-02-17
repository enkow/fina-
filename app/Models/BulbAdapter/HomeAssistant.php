<?php

namespace App\Models\BulbAdapter;

use App\Models\BulbAdapter;
use Illuminate\Support\Facades\Http;
use Parental\HasParent;

class HomeAssistant extends BulbAdapter
{
	use HasParent;

	public const ADMIN_SETTINGS = ['host', 'token'];

	public function isBulbOn(string|null $bulb): ?bool
	{
        try {
            $url = "http://{$this->credentials->host}/api/states/switch.{$bulb}";

            $response = Http::withHeaders($this->getHeaders())->get($url);
            return ($response->json()['state'] ?? false) === 'on';
        }
        catch (\Exception $e) {
            return false;
        }
	}

	/**
	 * @throws \JsonException
	 */
	public function changeState(string $bulb, bool|null $targetProperty): ?bool
	{
		if ($targetProperty === null) {
			$service = $this->isBulbOn($bulb) ? 'turn_on' : 'turn_off';
		} else {
			$service = $targetProperty ? 'turn_on' : 'turn_off';
		}
		$url = "http://{$this->credentials->host}/api/services/switch/{$service}";
		try {
            $response = Http::withHeaders($this->getHeaders())->post($url, [
                'entity_id' => 'switch.' . $bulb,
            ]);
        } catch(\Exception $e) {}
		return $this->isBulbOn($bulb);
	}

	private function getHeaders(): array
	{
		return [
			'Authorization' => 'Bearer ' . $this->credentials->token,
			'Content-Type' => 'application/json',
		];
	}
}
