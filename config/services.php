<?php

return [
	/*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

	'mailgun' => [
		'domain' => env('MAILGUN_DOMAIN'),
		'secret' => env('MAILGUN_SECRET'),
		'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
		'scheme' => 'https',
	],

	'postmark' => [
		'token' => env('POSTMARK_TOKEN'),
	],

	'ses' => [
		'key' => env('AWS_ACCESS_KEY_ID'),
		'secret' => env('AWS_SECRET_ACCESS_KEY'),
		'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
	],

	'facebook' => [
		'client_id' => env('FB_CLIENT_ID', null),
		'client_secret' => env('FB_CLIENT_SECRET', null),
		'redirect' => env('FB_REDIRECT_URL', null),
	],

	'stripe' => [
		'publishable' => env('STRIPE_PUBLISHABLE_KEY'),
		'secret' => env('STRIPE_SECRET_KEY'),
		'webhook_secret' => [
			'account' => env('STRIPE_ACCOUNT_WEBHOOK_SECRET'),
			'connect' => env('STRIPE_CONNECT_WEBHOOK_SECRET'),
		],
	],

	'tpay' => [
		'secret' => env('TPAY_SECRET'),
		'id' => env('TPAY_ID'),
		'production' => env('TPAY_PROD'),
		'confirmation_code' => env('TPAY_CONFIRMATION_CODE'),
		'merchant_code' => env('MERCHANT_CONFIRMATION_CODE'),
	],
];
