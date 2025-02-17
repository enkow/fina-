<?php

return [
	'singular' => 'Bokning',
	'plural' => 'Bokningar',
	'number' => 'Reservationsnummer',
	'group-reservation' => 'Grupp-reservation',
	'online-reservations' => 'Onlinebokningar',
	'club-calendar' => 'Klubbkalender',
	'reservation-search' => 'Sökmotor för bokningar',
	'calendar-name' => 'Namn på kalendern',
	'start-date' => 'Datum',
	'reservation-hours' => 'Reservation-timmar',
	'duration-time' => 'Varaktighetstid',
	'reservation-type' => 'Bokningstyp',
	'special-offer' => 'Kampanj',
	'set' => 'Set',
	'discount-code' => 'Rabattkod',
	'payment-status' => 'Betalningsstatus',
	'club-reservation' => 'Klubb-bokning',
	'anonymous-reservation' => 'Anonym bokning',
	'club-note' => 'Klubbens anmärkningar',
	'no-club-note' => 'Ingen klubbkommentar',
	'customer-note' => 'Kundkommentarer',
	'no-customer-note' => 'Ingen kundkommentar',
	'send-sms-notification' => 'Skicka SMS-notifiering',
	'send-email-notification' => 'Skicka e-postavisering',
	'add-reservation' => 'Lägg till bokning',
	'show-on-reservation' => 'Visa vid bokning',
	'cancel-reservation' => 'Avboka bokning',
	'related-reservations' => 'Relaterade reservationer',
	'cancel-related-reservations' => 'Avbryt relaterade reservationer',
	'apply-to-all-reservations' => 'Tillämpa på alla bokningar',
	'reduce-the-online-number-of-available-slots' => 'Minska antalet tillgängliga platser online',
	'number-of-slots' => 'Antal platser',
	'new-reservation' => 'Ny bokning',
	'edit-reservation' => 'Redigera bokning',
	'view-reservation' => 'Visa bokning',
	'new-price' => 'Nytt pris',
	'update-price' => 'Uppdatera pris',
	'update-reservation' => 'Spara',
	'reservations-found' => 'Bokningslista',
	'update-reservation-confirm' => 'Är du säker på att du vill uppdatera den valda reservationen?',
	'reservation-history' => 'Bokningshistorik',
	'reservation-number-value' => 'Bokningsnummer :value',
	'reservation-number' => 'Bokningsnummer',
	'reservation-numbers' => 'Bokningsnummer',
	'reservations-list' => 'Bokningslista',
	'cancelation-confirmation' => 'Är du säker på att du vill avboka din bokning?',
	'cancelation-reason' => 'Orsak till avbokning',
	'canceled-reservations' => 'Avbokade bokningar',
	'cancelation-reason-type-select-placeholder' => 'Välj orsak',
	'no-results' => 'Inga resultat',
	'none' => 'ingen',
	'canceler' => 'Avbokare',
	'no-history-logged' => 'Inga ändringar loggade',
	'place' => 'Plats',
	'game-price' => 'Spelpris',
	'reservation-datetime' => 'Datum/tid för bokning',
	'all-reservations' => 'Alla bokningar',
	'timer-status' => 'Timer',
	'add-reservation-to-google-calendar' => 'Lägg till bokning i google-kalender',
	'app-reservation-mail-title' => 'Bokning i :app_name',
	'calendar-reservation-title' => ':club_name,:game_name nr #:reservation_number',
	'calendar-reservation-details' => 'Bokningsnummer :club_name #:reservation_number i :club_name',
	'reservation-notification-sms-content' =>
		'Vi har sparat din bokning på :club_name för :start_at. Du är välkommen!',
	'reservation-stored-notification-content' =>
		'Ny bokning! :game_name - :start_at :customer_name har just gjort en bokning med numret: :nummer',
	'reservation-canceled' => 'Avbruten reservation!',
	'contact-with-club-mail' => 'Kontakta klubben om du har några frågor',
	'canceled-by' => 'Avbokad av',
	'reservation-hour' => 'Bokningstid',
	'changed-by-value' => 'Ändrad av: :value',
	'created-by-value' => 'Skapad: :value (:datetime)',

	'statuses' => [
		0 => 'Obetald',
		1 => 'Betald',
		2 => 'Utgått',
	],

	'status' => [
		'unpaid' => 'obetald',
		'offline' => 'offline',
		'paid-card' => 'betald med kort',
		'paid-cash' => 'betald med kontanter',
		'paid-cashless' => 'betald utan kontanter',
		'during-payment' => 'under betalning',
		'paid-online' => 'betald online',
		'all' => 'alla',
		'canceled-by-customer' => 'avbeställd av kund',
		'canceled-by-club' => 'avbokad av klubb',
		'canceled-by-system' => 'avbokad av systemet',
		'club-reservation' => 'klubb-reservation',
		'club' => 'club',
		'online' => 'online',
		'paid' => 'betald',
		'pending' => 'laukiama',
	],

	'timer-statuses' => [
		'0' => 'inaktiv',
		'1' => 'avstängd',
		'2' => 'på',
		'3' => 'pausad',
		'4' => 'stoppad',
	],

	'cancelation-types' => [
		'1' => 'Kunden kom inte',
		'2' => 'Kunden avbokade bokningen',
		'0' => 'Annat',
	],

	'start-date-filters' => [
		'yesterday' => 'Igår',
		'today' => 'Idag',
		'tomorrow' => 'I morgon',
		'last-seven-days' => 'Senaste sju dagarna',
		'last-thirty-days' => 'De senaste 30 dagarna',
		'this-month' => 'Den här månaden',
		'this-year' => 'Det här året',
	],

	'placeholders' => [
		'first-name' => 'Jan',
		'last-name' => 'Kowalski',
		'phone' => '000-000-000-000',
		'email' => 'jan.kowalski@qmail.com',
	],

	'rate-request-notification' => [
		'title' => 'Betygsätt din bokning på :club_name',
		'action-text' => 'betygsätt',
		'intro-lines' => [
			'0' => 'Betygsätt ditt besök på :club_name',
		],
	],
	'canceled-notification' => [
		'title' => 'Avbokning av bokning :reservation_number hos :club_name',
		'greeting' =>
			'Din transaktion kunde inte bearbetas på nytt eftersom den inte betalades inom de lagstadgade 5 minuterna.',
		'outro-lines' => [
			'0' => 'Bokningen har inte skapats.',
			'1' =>
				'Om du vill skapa en ny bokning - starta bokningsprocessen från början och betala för den inom högst 5 minuter.',
		],
		'refunded-info' => 'Bokningsavgiften kommer att återbetalas',
		'not-refunded-info' => 'Bokningsavgifter kommer inte att återbetalas',
	],
	'rated-notification' => [
		'title' => 'Bokning bedöms :reservation_number',
	],

	'successfully-canceled-content' => 'Bokningen avbokades',
	'successfully-canceled-content-plural' => 'Bokningar avbokades',

	'successfully-stored-content' => 'Bokningen skapades',
	'successfully-stored-content-plural' => 'Bokningar skapades',

	'successfully-updated-content' => 'Bokningen har uppdaterats',
	'successfully-updated-content-plural' => 'Bokningar har uppdaterats',

	'successfully-stored-singular-short' => 'Bokningen har lagts till!',
	'successfully-stored-plural-short' => 'Gruppbokning har lagts till!',

	'the-club-is-closed-during-these-hours' => 'Klubben är stängt under dessa tider',
	'reservation-moved-to-different-pricelist-error' =>
		'Du kan bara flytta betalda bokningar inom samma pris!',
	'reservation-moved-to-different-pricelist-timer-error' =>
		'Du kan endast flytta bokningar med en aktiv timer inom samma pris!',
	'paid-reservation-duration-change-error' => 'Endast obetalda bokningar kan förlängas och förkortas',
	'pending-reservation-modified-error' => 'Väntande bokningar kan inte redigeras',
	'reservation-time-changed-when-timer-enabled-error' => 'Bokningstiden kan inte ändras med timern på',
	'reservation-timer-stopped' => 'Timern har stoppats, bokningen har uppdaterats',

	'sms' => [
		'stored' =>
			'Bekräftelse av bokning :number för :game, :day vid :time. Betalningsstatus :payment_status :price.',
		'updated' =>
			'Uppdatering av reservation nr. :number till :game, :day till :time. Betalningsstatus :payment_status :price.',
		'canceled' =>
			'Avbokning av reservation nr :number för :game, :day vid :time. Betalningsstatus :payment_status :price.',
	],
];
