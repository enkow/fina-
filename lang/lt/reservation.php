<?php

return [
	'singular' => 'Rezervacija',
	'plural' => 'Rezervacijos',
	'number' => 'Rezervacijos numeris',
	'group-reservation' => 'Grupės rezervacija',
	'online-reservations' => 'Užsakymo internetu laikas',
	'club-calendar' => 'Klubo kalendorius',
	'reservation-search' => 'Rezervacijų paieškos sistema',
	'calendar-name' => 'Kalendoriaus pavadinimas',
	'start-date' => 'Data',
	'reservation-hours' => 'Rezervacijos valandos',
	'duration-time' => 'Trukmė',
	'reservation-type' => 'Rezervacijos tipas',
	'special-offer' => 'Skatinimas',
	'set' => 'Rinkinys',
	'discount-code' => 'Nuolaidos kodas',
	'payment-status' => 'Mokėjimo būsena',
	'club-reservation' => 'Klubo rezervavimas',
	'anonymous-reservation' => 'Anoniminis rezervavimas',
	'club-note' => 'Klubo pastabos',
	'no-club-note' => 'Nėra klubo pastabų',
	'customer-note' => 'Kliento pastabos',
	'no-customer-note' => 'Nėra kliento pastabų',
	'send-sms-notification' => 'Siųsti SMS pranešimą',
	'send-email-notification' => 'Siųsti pranešimą el. paštu',
	'add-reservation' => 'Pridėti rezervaciją',
	'show-on-reservation' => 'Rodyti užsakant',
	'cancel-reservation' => 'Atšaukti rezervaciją',
	'related-reservations' => 'Susiję užsakymai',
	'cancel-related-reservations' => 'Atšaukti susijusius užsakymus',
	'apply-to-all-reservations' => 'Taikyti visoms rezervacijoms',
	'reduce-the-online-number-of-availilable-slots' => 'Sumažinti internetu prieinamų vietų skaičių',
	'number-of-slots' => 'Vietų skaičius',
	'new-reservation' => 'Naujas užsakymas',
	'edit-reservation' => 'Redaguoti užsakymą',
	'view-reservation' => 'Rodyti rezervaciją',
	'new-price' => 'Nauja kaina',
	'update-price' => 'Atnaujinti kainą',
	'update-reservation' => 'Išsaugoti',
	'reservations-found' => 'Užsakymų sąrašas',
	'update-reservation-confirm' => 'Ar tikrai norite atnaujinti pasirinktą rezervaciją?',
	'reservation-history' => 'Rezervacijų istorija',
	'reservation-number-value' => 'Rezervacijos numeris :value',
	'reservation-number' => 'Rezervacijos numeris',
	'reservation-numbers' => 'Rezervacijos numeriai',
	'reservations-list' => 'Rezervacijų sąrašas',
	'cancelation-confirmation' => 'Ar tikrai norite atšaukti užsakymą?',
	'cancelation-reason' => 'Atšaukimo priežastis',
	'canceled-reservations' => 'Atšauktos rezervacijos',
	'cancelation-reason-type-select-placeholder' => 'Pasirinkite priežastį',
	'no-results' => 'Nėra rezultatų',
	'none' => 'nėra',
	'canceler' => 'Atšaukimas',
	'no-history-logged' => 'Nėra išsaugotų pakeitimų',
	'place' => 'Vieta',
	'game-price' => 'Žaidimo kaina',
	'reservation-datetime' => 'Rezervacijos data ir laikas',
	'all-reservations' => 'Visos rezervacijos',
	'timer-status' => 'Laikmatis',
	'add-reservation-to-google-calendar' => 'Įtraukti rezervaciją į Google kalendorių',
	'app-reservation-mail-title' => 'Rezervacija :app_name',
	'calendar-reservation-title' => ':club_name,:game_name numeris #:reservation_number',
	'calendar-reservation-details' => 'Rezervacijos :club_name numeris #:reservation_number in :club_name',
	'reservation-notification-sms-content' =>
		'Išsaugojome jūsų rezervaciją :club_name :start_at. Maloniai kviečiame!',
	'reservation-stored-notification-content' =>
		'Nauja rezervacija! - :start_at - :customer_name ką tik atliko',
	'reservation-canceled' => 'Užsakymas atšauktas!',
	'contact-with-club-mail' => 'Jei turite klausimų, kreipkitės į klubą',
	'canceled-by' => 'Atšaukė',
	'reservation-hour' => 'Rezervacijos laikas',
	'changed-by-value' => 'Redagavo: :value',
	'created-by-value' => 'Sukurta: :value (:datetime)',

	'statuses' => [
		0 => 'Neapmokėta',
		1 => 'Apmokėtas',
		2 => 'Pasibaigęs',
	],

	'status' => [
		'unpaid' => 'neapmokėtas',
		'offline' => 'neprisijungęs',
		'paid-card' => 'apmokėta kortele',
		'paid-cash' => 'apmokėta grynaisiais',
		'paid-cashless' => 'apmokėta be grynųjų pinigų',
		'during-payment' => 'mokėjimo metu',
		'paid-online' => 'apmokėta internetu',
		'all' => 'visi',
		'canceled-by-customer' => 'atšaukė klientas',
		'canceled-by-club' => 'atšaukė klubas',
		'canceled-by-system' => 'atšaukė sistema',
		'club-reservation' => 'klubo rezervavimas',
		'club' => 'klubas',
		'online' => 'internete',
		'paid' => 'mokama',
		'pending' => 'laukiama',
	],

	'timer-statuses' => [
		'0' => 'neaktyvus',
		'1' => 'išjungtas',
		'2' => 'įjungtas',
		'3' => 'sustabdytas',
		'4' => 'užšaldytas',
	],

	'cancelation-types' => [
		'1' => 'Klientas neatvyko',
		'2' => 'Klientas atšaukė užsakymą',
		'0' => 'Kita priežastis',
	],

	'start-date-filters' => [
		'yesterday' => 'Vakar',
		'today' => 'Šiandien',
		'tomorrow' => 'Rytoj',
		'last-seven-days' => 'Last-seven-days',
		'last-thirty-days' => 'Paskutinės 30 dienų',
		'this-month' => 'Šis mėnuo',
		'this-year' => 'Šiais metais',
	],

	'placeholders' => [
		'first-name' => 'Janas',
		'last-name' => 'Kowalskis',
		'phone' => '000-000-000-000',
		'email' => 'janas.kowalskis@qmail.com',
	],

	'rate-request-notification' => [
		'title' => 'Įvertinkite užsakymą :club_name',
		'action-text' => 'Įvertinkite',
		'intro-lines' => [
			'0' => 'Įvertinkite savo apsilankymą :club_name',
		],
	],
	'canceled-notification' => [
		'title' => 'Užsakymo atšaukimas :reservation_number :club_name',
		'greeting' =>
			'Jūsų operacija negalėjo būti pakartotinai apdorota, nes ji nebuvo apmokėta per teisės aktuose nustatytas 5 minutes.',
		'outro-lines' => [
			'0' => 'Užsakymas nebuvo sukurtas.',
			'1' =>
				'Jei norite sukurti naują užsakymą - pradėkite užsakymo procesą nuo pradžių ir sumokėkite už jį ne vėliau kaip per 5 minutes.',
		],
		'refunded-info' => 'Už rezervaciją sumokėti pinigai bus grąžinti',
		'not-refunded-info' => 'Už rezervaciją sumokėti pinigai negrąžinami',
	],
	'rated-notification' => [
		'title' => 'Įvertinta rezervacija :reservation_number',
	],

	'successfully-canceled-content' => 'Rezervacijos buvo sėkmingai atšauktos',
	'successfully-canceled-content-plural' => 'Rezervacijos sėkmingai atšauktos',

	'successfully-stored-content' => 'Rezervacija buvo sėkmingai sukurta',
	'successfully-stored-content-plural' => 'Rezervacijos buvo sėkmingai sukurtos',

	'successfully-updated-content' => 'Rezervacija sėkmingai atnaujinta',
	'successfully-updated-content-plural' => 'Rezervacijos buvo sėkmingai atnaujintos',

	'successfully-stored-singular-short' => 'Rezervacija buvo pridėta!',
	'successfully-stored-plural-short' => 'Pridėtas grupės užsakymas!',

	'the-club-is-closse-dis closed-during-these-hours' => 'Klubas šiomis valandomis nedirba',
	'reservation-moved-to-different-pricelist-error' =>
		'Apmokėtus užsakymus galite perkelti tik tos pačios kainos ribose!',
	'reservation-moved-to-different-pricelist-timer-error' =>
		'Galite perkelti tik tos pačios kainos užsakymus su aktyviu laikmačiu!',
	'paid-reservation-duration-change-error' => 'Pratęsti ir sutrumpinti galima tik neapmokėtus užsakymus.',
	'pending-reservation-modified-error' => 'Neįvykdytų užsakymų redaguoti negalima',
	'reservation-time-changed-when-timer-enabled-error' => 'Įjungus laikmatį negalima keisti užsakymo laiko.',
	'reservation-timer-stopped' => 'Laikmatis sustabdytas, užsakymas atnaujintas',

	'sms' => [
		'stored' =>
			'Conferma della prenotazione :numer per :game, :day a :time. Stato del pagamento :payment_status :price.',
		'updated' =>
			'Aggiornamento della prenotazione n. :numer a :game, :day a :time. Stato del pagamento :payment_status :price.',
		'canceled' =>
			'Cancellazione della prenotazione n. :numer per :game, :day a :time. Stato del pagamento :payment_status :price.',
	],
];
