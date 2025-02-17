<?php

namespace App\Models\BulbAdapter;

use App\Models\BulbAdapter;
use Illuminate\Support\Facades\Log;
use Parental\HasParent;

class WebThingsAdapter extends BulbAdapter
{
	use HasParent;

	public const ADMIN_SETTINGS = ['host', 'token'];

	public function isBulbOn(string $bulb): ?bool
	{
		try {
			$response = $this->curlCall($this->prepareUrl($bulb));

			return json_decode($response, true)['on'];
		} catch (\Throwable $e) {
			Log::error($e);

			return false;
		}
	}

	public function changeState(string $bulb, bool $targetProperty): ?bool
	{
		try {
			$response = $this->curlCall($this->prepareUrl($bulb), $targetProperty);
			Log::debug(sprintf('[%s] - %d - %s', $bulb, $targetProperty, $response));

			return json_decode($response, true)['on'];
		} catch (\Throwable $e) {
			Log::error($e);

			return false;
		}
	}

	private function getAccessToken(): string
	{
		$credentials = (object) ($this->credentials ?? []);

		return $credentials?->token ?? '';
	}

	private function prepareHost()
	{
		return substr($this->credentials->host, -1) === '/'
			? substr($this->credentials->host, 0, -1)
			: $this->credentials->host;
	}

	private function getHeaders(): array
	{
		return [
			'Accept: application/json',
			'content-type: application/json',
			sprintf('Authorization: Bearer %s', $this->getAccessToken()),
		];
	}

	private function prepareUrl(string $bulb): string
	{
		return sprintf('%s/things/%s/properties/on', $this->prepareHost(), $bulb);
	}

	private function curlCall(string $url, ?bool $targetProperty = null): string
	{
		try {
			$options = [
				CURLOPT_URL => $url,
				CURLOPT_HTTPHEADER => $this->getHeaders(),
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT_MS => BulbAdapter::TIMEOUT,
			];

			if ($targetProperty !== null) {
				$options = array_merge($options, [
					CURLOPT_CUSTOMREQUEST => 'PUT',
					CURLOPT_POSTFIELDS => json_encode(['on' => $targetProperty]),
				]);
			}

			$curl = curl_init();
			curl_setopt_array($curl, $options);
			$response = curl_exec($curl);
			curl_close($curl);

			return $response;
		} catch (\Throwable $e) {
			return json_encode(['on' => false]);
		}
	}
}
