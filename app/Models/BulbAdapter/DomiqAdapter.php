<?php

namespace App\Models\BulbAdapter;

use App\Models\BulbAdapter;
use Illuminate\Support\Facades\Log;
use Parental\HasParent;

class DomiqAdapter extends BulbAdapter
{
	use HasParent;

	public const ADMIN_SETTINGS = ['host'];

	public function isBulbOn(string $bulb): ?bool
	{
		try {
			$response = $this->curlCall($this->prepareUrl($bulb, 'gettablestate'));
			$this->handleStatusResponse($response);

			return boolval($response);
		} catch (\Throwable $e) {
			Log::error($e);
            return false;
		}

		return null;
	}

	public function changeState(string $bulb, bool $targetProperty): ?bool
	{
		$action = $targetProperty ? 'on' : 'off';
		$url = sprintf('%s&value=%s', $this->prepareUrl($bulb, 'controltable'), $action);
		try {
			$response = $this->curlCall($url);
			Log::debug(sprintf('[%s] - %d - %s', $bulb, $targetProperty, $response));

			return $targetProperty;
		} catch (\Throwable $e) {
            return 'off';
		}

		return null;
	}

	private function prepareHost()
	{
		return substr($this->credentials->host, -1) === '/'
			? substr($this->credentials->host, 0, -1)
			: $this->credentials->host;
	}

	private function prepareUrl(string $bulb, string $method): string
	{
		return sprintf(
			'http://%s/call/outerpublic?vm=LOGIC&call=%s&table=%s',
			$this->prepareHost(),
			$method,
			$bulb
		);
	}

	private function getHeaders(): array
	{
		return ['Accept: */*', 'Content-type: text/html; charset=UTF-8'];
	}

	private function curlCall(string $url): string
	{
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => $this->getHeaders(),
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_TIMEOUT_MS => BulbAdapter::TIMEOUT,
		]);
		$response = curl_exec($curl);
		curl_close($curl);

		return $response;
	}

	private function handleStatusResponse(string $response): void
	{
		if (!ctype_digit($response)) {
			throw new \InvalidArgumentException('Bulb does not exists');
		}
	}
}
