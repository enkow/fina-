<?php

return [
	'help-section' => 'I det här avsnittet kan du konfigurera anslutningen till onlinebetalningsförmedlaren',
	'invalid-method-type' => 'Ogiltig typ av betalningsmetod',
	'already-connected' => 'Den här betalningsmetoden är redan aktiv',
	'general-error' => 'Ett fel uppstod när du skapade en betalningsmetod',

	'invoice-payment' => 'Betalning för faktura #:id på Bookgame.io',
	'reservation-payment' => 'Reservation :reservation_number i :club_name',

	'payment-time-limit-notice' => 'Kom ihåg att du har 5 minuter på dig att betala för din bokning',

	'stripe' => [
		'continue-connection' => 'Fortsätt ansluta till',

		'active' => [
			'title' => 'Stripe-betalningar är aktiva',
			'desc' => 'Dina kunder kan nu göra onlinebetalningar med Stripe',
		],
		'connecting' => [
			'title' => 'Anslutningsprocessen med Stripe har startat',
			'desc' =>
				'Om du har misslyckats med att ansluta till Stripe, tryck på knappen nedan för att försöka igen.',
		],
		'not-connected' => [
			'title' => 'Ta emot onlinebetalningar med Stripe',
			'desc' =>
				'Stripe är den globala ledaren inom onlinebetalningar. Länka ditt konto för att börja ta emot betalningar online.',
		],

		'login' => 'Logga in på',
		'disconnect' => 'Koppla bort konto',
		'connect' => 'Anslut ditt konto',
		'no-connection-yet' =>
			'Vi har ännu inte fått någon bekräftelse på en lyckad anslutning från Stripe. Om du har slutfört anslutningsprocessen kommer metoden att aktiveras automatiskt inom några minuter.',
		'connected' => 'Framgångsrikt ansluten till Stripe',
	],
];
