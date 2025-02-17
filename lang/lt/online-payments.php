<?php

return [
	'help-section' => 'Šiame skyriuje galite konfigūruoti ryšį su internetinio mokėjimo tarpininku',
	'invalid-method-type' => 'Neteisingas mokėjimo metodo tipas',
	'already-connected' => 'Šis mokėjimo metodas jau aktyvus',
	'general-error' => 'Kuriant mokėjimo metodą įvyko klaida',

	'invoice-payment' => 'Mokėjimas už sąskaitą faktūrą #:id Bookgame.io svetainėje',
	'reservation-payment' => ':reservation_number užsakymas :club_name',

	'payment-time-limit-notice' => 'Nepamirškite, kad turite 5 minutes sumokėti už užsakymą.',

	'stripe' => [
		'continue-connection' => 'Tęsti ryšį su',

		'active' => [
			'title' => 'Stripe mokėjimai yra aktyvūs',
			'desc' => 'Jūsų klientai dabar gali atlikti mokėjimus internetu naudodami Stripe',
		],
		'connecting' => [
			'title' => 'Prasidėjo susijungimo su Stripe procesas',
			'desc' =>
				'Jei nepavyko prisijungti prie Stripe, paspauskite toliau esantį mygtuką ir bandykite dar kartą',
		],
		'not-connected' => [
			'title' => 'Priimti mokėjimus internetu su Stripe',
			'desc' =>
				'Stripe yra pasaulinis internetinių mokėjimų lyderis. Susiekite savo paskyrą ir pradėkite priimti mokėjimus internetu.',
		],

		'login' => 'Prisijunkite prie',
		'disconnect' => 'Prisijunkite prie',
		'connect' => 'Sujunkite savo paskyrą',
		'no-connection-yet' =>
			'Dar negavome patvirtinimo apie sėkmingą prisijungimą iš "Stripe". Jei sėkmingai užbaigėte prisijungimo procesą, metodas bus automatiškai aktyvuotas po kelių minučių.',
		'connected' => 'Sėkmingai prisijungta prie Stripe',
	],
];
