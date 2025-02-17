<?php

return [
	'singular' => 'Rezerwacja',
	'plural' => 'Rezerwacje',
	'number' => 'Numer rezerwacji',
	'group-reservation' => 'Rezerwacja grupowa',
	'online-reservations' => 'Rezerwacje online',
	'club-calendar' => 'Kalendarz klubu',
	'reservation-search' => 'Wyszukiwarka rezerwacji',
	'calendar-name' => 'Nazwa na kalendarzu',
	'start-date' => 'Data',
	'reservation-hours' => 'Godziny rezerwacji',
	'duration-time' => 'Czas trwania',
	'reservation-type' => 'Typ rezerwacji',
	'special-offer' => 'Promocja',
	'app_commission' => 'Prowizja aplikacji',
	'provider_commission' => 'Prowizja pośrednika',
	'set' => 'Zestaw',
	'discount-code' => 'Kod rabatowy',
	'payment-status' => 'Status płatności',
	'club-reservation' => 'Rezerwacja klubu',
	'anonymous-reservation' => 'Rezerwacja anonimowa',
	'club-note' => 'Uwagi klubu',
	'no-club-note' => 'Brak komentarza klubu',
	'customer-note' => 'Uwagi klienta',
	'no-customer-note' => 'Brak komentarza klienta',
	'send-sms-notification' => 'Wyślij powiadomienie SMSem',
	'send-email-notification' => 'Wyślij powiadomienie Email',
	'add-reservation' => 'Dodaj rezerwację',
	'show-on-reservation' => 'Pokaż na rezerwacji',
	'cancel-reservation' => 'Anuluj rezerwację',
	'related-reservations' => 'Rezerwacje powiązane',
	'cancel-related-reservations' => 'Anuluj rezerwacje powiązane',
	'apply-to-all-reservations' => 'Zastosuj do wszystkich rezerwacji',
	'reduce-the-online-number-of-available-slots' => 'Zmniejsz liczbę dostępnych miejsc online',
	'number-of-slots' => 'Liczba miejc',
	'new-reservation' => 'Nowa rezerwacja',
	'edit-reservation' => 'Edytuj rezerwację',
	'view-reservation' => 'Pokaż rezerwację',
	'new-price' => 'Nowa cena',
	'update-price' => 'Aktualizuj cenę',
	'update-reservation' => 'Zapisz',
	'reservations-found' => 'Lista rezerwacji',
	'update-reservation-confirm' => 'Czy na pewno chcesz zaktualizować wybraną rezerwację?',
	'reservation-history' => 'Historia rezerwacji',
	'reservation-number-value' => 'Rezerwacja nr :value',
	'reservation-number' => 'Numer rezerwacji',
	'reservation-numbers' => 'Numery rezerwacji',
	'reservations-list' => 'Lista rezerwacji',
	'cancelation-confirmation' => 'Czy na pewno chcesz anulować swoją rezerwację?',
	'cancelation-reason' => 'Powód anulowania',
	'canceled-reservations' => 'Anulowane rezerwacje',
	'cancelation-reason-type-select-placeholder' => 'Wybierz powód',
	'no-results' => 'Brak wyników',
	'none' => 'brak',
	'canceler' => 'Anulujący',
	'no-history-logged' => 'Brak zapisanych zmian',
	'place' => 'Miejsce',
	'game-price' => 'Cena gry',
	'reservation-datetime' => 'Data / Godzina rezerwacji',
	'all-reservations' => 'Wszystkie rezerwacje',
	'timer-status' => 'Timer',
	'timer-init' => 'Uruchom timer',
	'add-reservation-to-google-calendar' => 'Dodaj rezerwację do kalendarza google',
	'app-reservation-mail-title' => 'Rezerwacja w serwisie :app_name',
	'calendar-reservation-title' => ':club_name,:game_name nr #:reservation_number',
	'calendar-reservation-details' => 'Rezerwacja :game_name nr #:reservation_number w :club_name',
	'reservation-notification-sms-content' =>
		'Zapisaliśmy Twoją rezerwację w :club_name na godzinę :start_at. Zapraszamy!',
	'reservation-stored-notification-content' =>
		'Nowa rezerwacja! :game_name - :start_at :customer_name właśnie dokonał rezerwacji o numerze: :number',
	'reservation-canceled' => 'Anulowano rezerwację!',
	'contact-with-club-mail' => 'W razie pytań skontaktuj się z klubem',
	'canceled-by' => 'Anulowana przez',
	'reservation-hour' => 'Godzina rezerwacji',
	'changed-by-value' => 'Zmiana: :value',
	'created-by-value' => 'Utworzone: :value (:datetime)',

	'statuses' => [
		0 => 'Nieopłacona',
		1 => 'Opłacona',
		2 => 'Anulowana',
	],

	'status' => [
		'unpaid' => 'niezapłacone',
		'offline' => 'offline',
		'paid-card' => 'zapłacone kartą',
		'paid-cash' => 'zapłacone gotówką',
		'paid-cashless' => 'zapłacone bezgotówkowo',
		'during-payment' => 'w trakcie płatności',
		'paid-online' => 'zapłacone online',
		'all' => 'wszystkie',
		'canceled-by-customer' => 'anulowane przez klienta',
		'canceled-by-club' => 'anulowane przez klub',
		'canceled-by-system' => 'anulowane przez system',
		'club-reservation' => 'rezerwacja klubu',
		'club' => 'klubu',
		'online' => 'online',
		'paid' => 'zapłacone',
		'pending' => 'oczekujące',
	],

	'timer-statuses' => [
		'0' => 'nieaktywny',
		'1' => 'wyłączony',
		'2' => 'włączony',
		'3' => 'wstrzymany',
		'4' => 'zatrzymany',
	],

	'cancelation-types' => [
		'1' => 'Klient nie przyszedł',
		'2' => 'Klient odwołał rezerwację',
		'0' => 'Inny',
	],

	'start-date-filters' => [
		'yesterday' => 'Wczoraj',
		'today' => 'Dziś',
		'tomorrow' => 'Jutro',
		'last-seven-days' => 'Ostatnie 7 dni',
		'last-thirty-days' => 'Ostatnie 30 dni',
		'this-month' => 'Ten miesiąc',
		'this-year' => 'Ten rok',
	],

	'placeholders' => [
		'first-name' => 'Jan',
		'last-name' => 'Kowalski',
		'phone' => '000-000-000-000',
		'email' => 'jan.kowalski@qmail.com',
	],

	'rate-request-notification' => [
		'title' => 'Oceń swoją rezerwację w :club_name',
		'action-text' => 'Oceń',
		'intro-lines' => [
			'0' => 'Oceń swoją wizytę w :club_name',
		],
	],
	'canceled-notification' => [
		'title' => 'Anulowanie rezerwacji :reservation_number w :club_name',
		'greeting' =>
			'Nie udało się przeprocesować Twojej transakcji ponieważ nie została opłacona w ciągu regulaminowych 5 minut.',
		'outro-lines' => [
			'0' => 'Rezerwacja nie została utworzona.',
			'1' =>
				'Jeśli chcesz utworzyć nową rezerwację - rozpocznij proces rezerwacji od nowa i opłać ją maksymalnie w ciągu 5 minut.',
		],
		'refunded-info' => 'Środki za rezerwację zostaną zwrócone',
		'not-refunded-info' => 'Środki za rezerwację nie zostaną zwrócone',
	],
	'rated-notification' => [
		'title' => 'Oceniono rezerwację :reservation_number',
	],

	'successfully-canceled-content' => 'Rezerwacja została poprawnie anulowana',
	'successfully-canceled-content-plural' => 'Rezerwacje zostały poprawnie anulowane',

	'successfully-stored-content' => 'Rezerwacja została poprawnie utworzona',
	'successfully-stored-content-plural' => 'Rezerwacje zostały poprawnie utworzone',

	'successfully-updated-content' => 'Rezerwacja została poprawnie zaktualizowana',
	'successfully-updated-content-plural' => 'Rezerwacje zostały poprawnie zaktualizowana',

	'successfully-stored-singular-short' => 'Rezerwacja została dodana!',
	'successfully-stored-plural-short' => 'Rezerwacja grupowa dodana!',

	'the-club-is-closed-during-these-hours' => 'Klub w tych godzinach jest zamknięty',
	'reservation-moved-to-different-pricelist-error' =>
		'Możesz przesuwać opłacone rezerwacje tylko w obrębie tej samej ceny!',
	'reservation-moved-to-different-pricelist-timer-error' =>
		'Możesz przesuwać rezerwacje z aktywnym timerem tylko w obrębie tej samej ceny!',
	'paid-reservation-duration-change-error' => 'Można wydłużać i skracać tylko nieopłacone rezerwacje',
	'pending-reservation-modified-error' => 'Nie można edytować rezerwacji oczekujących na płatność',
	'reservation-time-changed-when-timer-enabled-error' =>
		'Nie można zmieniać czasu rezerwacji z włączonym timerem',
	'reservation-timer-stopped' => 'Timer został zatrzymany, rezerwacja została zaktualizowana',

	'sms' => [
		'stored' =>
			'Potwierdzenie rezerwacji nr. :number na :game, :day o godzinie :time. Status platnosci :payment_status :price.',
		'updated' =>
			'Aktualizacja rezerwacji nr. :number na :game, :day o godzinie :time. Status platnosci :payment_status :price.',
		'canceled' =>
			'Anulowanie rezerwacji nr. :number na :game, :day o godzinie :time. Status: :payment_status :price.',

		'verification' =>
			'Twoj kod: :code, UWAGA!!! - wprowadzenie tego kodu to TYLKO ukonczenie rejestracji. Jesli rozpoczales proces rezerwacji nie zapomnij go dokonczyc.',
	],

	'bulbs' => [
		'label' => 'Zarządzaj oświetleniem',
		'switch-off-time' => 'Godzina wyłączenia: :time',

		'reservation-time' => 'Zapal światło do "pełnej godziny"',
		'duration-time' => 'Zapal światło na czas trwania gry',
		'nothing' => 'Nie rób nic z oświetleniem',
	],
];
