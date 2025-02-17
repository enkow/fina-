<?php

return [
	'provider' => env('SMS_PROVIDER', 'smsapi'),
	'from' => env('SMS_FROM', 'test'),
	'justsend_variant' => env('JUSTSEND_VARIANT', 'DYNAMIC'),
	'justsend_token' => env('JUSTSEND_TOKEN', 'a'),
	'smsapi_token' => env('SMSAPI_TOKEN', 'a'),
	'webhook_url' => env('WEBHOOK_URL', 'a'),
];
