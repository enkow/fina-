<?php

return [
	'help-section' => 'I dette afsnit kan du konfigurere forbindelsen til online betalingsformidleren',
	'invalid-method-type' => 'Ugyldig type betalingsmetode',
	'already-connected' => 'Denne betalingsmetode er allerede aktiv',
	'general-error' => 'Der opstod en fejl under oprettelsen af en betalingsmetode',
	'invoice-payment' => 'Betaling for faktura #:id på Bookgame.io',
	'reservation-payment' => 'Betaling :reservation_number in :club',
	'payment-time-limit-notice' => 'Husk, at du har 5 minutter til at betale for din reservation.',
	'stripe' => [
		'continue-connection' => 'Fortsæt forbindelsen med',
		'active' => [
			'title' => 'Stripe-betalinger er aktive',
			'desc' => 'Dine kunder kan nu foretage onlinebetalinger med Stripe',
		],
		'connecting' => [
			'title' => 'Processen med at oprette forbindelse til Stripe er startet',
			'desc' =>
				'Hvis du ikke kunne oprette forbindelse til Stripe, skal du trykke på knappen nedenfor for at prøve igen.',
		],
		'not-connected' => [
			'title' => 'Tag imod onlinebetalinger med Stripe',
			'desc' =>
				'Stripe er verdens førende inden for onlinebetalinger. Link din konto for at begynde at modtage onlinebetalinger.',
		],
		'login' => 'Log ind på',
		'disconnect' => 'Afbryd forbindelsen til kontoen',
		'connect' => 'Opret forbindelse til din konto',
		'no-connection-yet' =>
			'Vi har endnu ikke modtaget bekræftelse på en vellykket forbindelse fra Stripe. Hvis du har gennemført forbindelsesprocessen, vil metoden automatisk blive aktiveret om et par minutter.',
		'connected' => 'Vellykket forbindelse til Stripe',
	],
];
