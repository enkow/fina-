<?php

return [
	'help-section' => 'In diesem Abschnitt können Sie die Verbindung zum Online-Zahlungsbroker konfigurieren',
	'invalid-method-type' => 'Ungültiger Zahlungsmitteltyp',
	'already-connected' => 'Diese Zahlungsmethode ist bereits aktiv',
	'general-error' => 'Beim Anlegen einer Zahlungsmethode ist ein Fehler aufgetreten',

	'invoice-payment' => 'Zahlung für Rechnung #:id auf Bookgame.io',
	'reservation-payment' => 'Reservierung :reservation_number in :club_name',

	'payment-time-limit-notice' =>
		'Denken Sie daran, dass Sie 5 Minuten Zeit haben, um Ihre Buchung zu bezahlen.',

	'stripe' => [
		'continue-connection' => 'Verbindung fortsetzen mit',

		'active' => [
			'title' => 'Stripe-Zahlungen sind aktiv',
			'desc' => 'Ihre Kunden können jetzt Online-Zahlungen über Stripe vornehmen',
		],
		'connecting' => [
			'title' => 'Der Verbindungsaufbau zu Stripe hat begonnen',
			'desc' =>
				'Wenn Sie die Verbindung zu Stripe nicht herstellen konnten, klicken Sie auf die Schaltfläche unten, um es erneut zu versuchen.',
		],
		'not-connected' => [
			'title' => 'Online-Zahlungen mit Stripe akzeptieren',
			'desc' =>
				'Stripe ist der weltweit führende Anbieter von Online-Zahlungen. Verknüpfen Sie Ihr Konto, um Online-Zahlungen zu akzeptieren.',
		],

		'login' => 'Anmeldung zum',
		'disconnect' => 'Konto abmelden',
		'connect' => 'Verbinden Sie Ihr Konto',
		'no-connection-yet' =>
			'Wir haben noch keine Bestätigung für eine erfolgreiche Verbindung von Stripe erhalten. Wenn Sie den Verbindungsprozess erfolgreich abgeschlossen haben, wird die Methode in ein paar Minuten automatisch aktiviert.',
		'connected' => 'Erfolgreich mit Stripe verbunden',
	],
];
