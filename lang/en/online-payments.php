<?php

return [
	'help-section' => 'In this section you can configure the connection to the online payment broker',
	'invalid-method-type' => 'Invalid payment method type',
	'already-connected' => 'This payment method is already active',
	'general-error' => 'An error occurred while creating a payment method',

	'invoice-payment' => 'Payment for invoice #:id on Bookgame.io',
	'reservation-payment' => 'Reservation :reservation_number in :club_name',

	'payment-time-limit-notice' => 'Remember that you have 5 minutes to pay for your reservation',

	'stripe' => [
		'continue-connection' => 'Continue connecting with',

		'active' => [
			'title' => 'Stripe payments are active',
			'desc' => 'Your customers can now make online payments using Stripe',
		],
		'connecting' => [
			'title' => 'The process of connecting to Stripe has started',
			'desc' => 'If you failed to connect to Stripe, press the button below to try again.',
		],
		'not-connected' => [
			'title' => 'Accept online payments with Stripe',
			'desc' =>
				'Stripe is the world leader in online payments. Link your account to start accepting online payments.',
		],

		'login' => 'Login to',
		'disconnect' => 'Disconnect account',
		'connect' => 'Connect your account',
		'no-connection-yet' =>
			'We have not yet received confirmation of a successful connection from Stripe. If you have successfully completed the connection process, the method will automatically activate in a few minutes.',
		'connected' => 'Successfully connected to Stripe',
	],
];
