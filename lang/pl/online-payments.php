<?php

return [
	'help-section' => 'W tej sekcji możesz skonfigurować połączenie z pośrednikiem płatności online',
	'invalid-method-type' => 'Nieprawidłowy typ metody płatności',
	'already-connected' => 'Ta metoda płatności jest już aktywna',
	'not-connected' => 'Ta metoda płatności nie jest aktywna',
	'general-error' => 'Wystąpił błąd podczas tworzenia metody płatności',

	'invoice-payment' => 'Płatność za fakturę #:id w serwisie Bookgame.io',
	'reservation-payment' => 'Rezerwacja :reservation_number w :club_name',

	'payment-time-limit-notice' => 'Pamiętaj, że na opłacenie rezerwacji masz 5 minut',

	'stripe' => [
		'continue-connection' => 'Kontynuuj łączenie ze',

		'active' => [
			'title' => 'Płatności Stripe są aktywne',
			'desc' => 'Twoi klienci mogą teraz dokonywać płatności online za pomocą Stripe',
		],
		'connecting' => [
			'title' => 'Rozpoczęto proces połączenia ze Stripe',
			'desc' =>
				'Jeżeli nie udało się połączyć ze Stripe, naciśnij poniższy przycisk, aby spróbować ponownie.',
		],
		'not-connected' => [
			'title' => 'Przyjmuj płatności online dzięki Stripe',
			'desc' =>
				'Stripe to światowy lider w płatnościach online. Połącz swoje konto, aby zacząć przyjmować płatności online.',
		],

		'login' => 'Zaloguj się do',
		'disconnect' => 'Odłącz konto',
		'connect' => 'Połącz konto',
		'no-connection-yet' =>
			'Nie otrzymaliśmy jeszcze potwierdzenia o pomyślnym połączeniu od Stripe. Jeżeli pomyślnie ukończono proces połączenia, metoda aktywuje się automatycznie za kilka minut.',
		'connected' => 'Pomyślnie połączono ze Stripe',
		'disconnected' => 'Pomyślnie odłączono konto Stripe',
	],
];
