<?php

namespace App\Channels;

use App\Enums\SmsProvider;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class SmsChannel
{
	public function send($notifiable, $notification): void
	{
		$phone = $notifiable->routeNotificationFor('phone', $notification) ?? $notifiable->phone;
		$clubId = $notifiable->routeNotificationFor('clubId', $notification) ?? $notifiable->club->id;
		if (!$phone || !$clubId) {
			$notification->afterSms(['system' => 'SMS error']);
			return;
		}

		if (method_exists($notification, 'toSms')) {
			$message = $notification->toSms();
		} else {
			if (method_exists($notification, 'afterSms')) {
				$message = $notification->afterSms(['system' => 'Message undefined']);
			}
			return;
		}
		$msisdn = $notifiable->phone ?? $notifiable->routeNotificationFor('phone', $notification);

		if (config('sms.provider') === 'justsend') {
			$result = $this->sendNowByJustSend($msisdn, $message);
		} elseif (config('sms.provider') === 'smsapi') {
			$result = $this->sendNowBySmsApi($msisdn, $message);
		} else {
			$result = Http::post(config('sms.webhook_url'), ['number' => $msisdn, 'message' => $message]);
		}

		if (method_exists($notification, 'afterSms')) {
			$notification->afterSms($result);
		}

		return;
	}

	public function sendNowByJustSend($msisdn, $message)
	{
		$message = $this->replacePolishCharacters($message);
		$bulkVariant = [
			'BASIC' => 'ECO',
			'UNIQUE' => 'FULL',
			'DYNAMIC' => 'PRO',
		][strtoupper(config('sms.justsend_variant'))];

		$request = Http::withHeaders([
			'App-Key' => config('sms.justsend_token'),
		])->post('https://justsend.pl/api/rest/v2/message/send', [
			'bulkVariant' => $bulkVariant,
			'doubleEncode' => true,
			'from' => config('sms.from'),
			'message' => $message,
			'to' => str_replace(' ', '', $msisdn),
		]);

		$response = $request->json();

		return [
			'phone_number' => $msisdn,
			'provider' => SmsProvider::JUSTSEND,
			'message' => $message,
			'status' => $response['responseCode'] . ':' . $response['errorId'],
		];
	}

	public function sendNowBySmsApi($msisdn, $message)
	{
		$message = $this->replacePolishCharacters($message);
		$request = Http::withToken(config('sms.smsapi_token'))->post('https://api.smsapi.pl/sms.do', [
			'to' => $msisdn,
			'from' => config('sms.from'),
			'message' => $message,
			'encoding' => 'utf-8',
			'format' => 'text',
		]);

		$response = $request->body();

		return [
			'phone_number' => $msisdn,
			'provider' => SmsProvider::SMSAPI,
			'message' => $message,
			'status' => $response,
		];
	}

	private function replacePolishCharacters(string $str): string
	{
		$polish = ['ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ż', 'ź', 'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ż', 'Ź'];
		$latin = ['a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', 'A', 'C', 'E', 'L', 'N', 'O', 'S', 'Z', 'Z'];

		return str_replace($polish, $latin, $str);
	}
}
