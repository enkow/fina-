<?php

return [
	/*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

	'accepted' => ':Attribute musi zostać zaakceptowane.', //yes, 1, true
	'active_url' => ':Attribute nie jest prawidłowym adresem URL.',
	'after' => ':Attribute musi być datą późniejszą niż :date.',
	'after_or_equal' => ':Attribute musi być datą późniejszą lub taką samą jak :date.',
	'alpha' => ':Attribute może zawierać tylko litery.',
	'alpha_dash' => ':Attribute może zawierać tylko litery, cyfry i podkreślenia.',
	'alpha_num' => ':Attribute może zawierać tylko litery i cyfry.',
	'array' => ':Attribute musi być tablicą.',
	'before' => ':Attribute musi być datą wcześniejszą niż :date.',
	'between' => [
		'numeric' => ':Attribute musi być wartością pomiędzy :min i :max.',
		'file' => ':Attribute musi mieć pomiędzy :min a :max kilobajtów.',
		'string' => ':Attribute musi mieć pomiędzy :min a :max znaków.',
		'array' => ':Attribute musi mieć pomiędzy :min a :max pozycji.',
	],
	'boolean' => 'Pole :attribute musi być true lub false',
	'confirmed' => "Potwierdzenie pola \":attribute\" nie zgadza się.",
	'date' => ':Attribute nie jest prawidłową datą.',
	'date_format' => ':Attribute nie zgadza się z formatem :format.',
	'different' => ':Attribute i :other muszą być różne.',
	'digits' => ':Attribute musi mieć :digits cyfr.',
	'digits_between' => ':Attribute musi mieć pomiędzy :min a :max cyfr.',
	'distinct' => 'Nazwa musi być unikalna',
	'email' => ':Attribute musi być poprawnym adresem email.',
	'exists' => 'Wybrana wartość ":attribute" jest nieprawidłowa.',
	'gt' => [
		'numeric' => 'Pole ":attribute" musi być większe niż :value.',
		'zero' => 'Pole ":attribute" musi być większe od 0',
	],
	'gte' => [
		'numeric' => 'Pole ":attribute" musi być większe niż :value.',
	],
	'image' => ':Attribute musi być obrazkiem.',
	'in' => 'wybrany :attribute jest nieprawidłowy.',
	'invalid' => ':attribute jest nieprawidłowy.',
	'integer' => ':Attribute musi być liczbą całkowitą.',
	'ip' => ':Attribute musi być poprawnym adresem IP.',
	'max' => [
		'numeric' => 'Pole ":attribute" musi być mniejsze lub równe :max.',
		'file' => 'Plik nie może być większy niż :max kilobajtów.',
		'string' => "Pole \":attribute\" nie może być dłuższe niż :max znaków.",
		'array' => ':Attribute nie może mieć więcej niż :max pozycji.',
	],
	'mimes' => ':Attribute musi być plikiem typu: :values.',
	'min' => [
		'numeric' => 'Pole ":attribute" musi być większe lub równe :min.',
		'file' => ':Attribute musi mieć co najmniej :min kilobajtów.',
		'string' => "Pole \":attribute\" nie może być krótsze niż :min znaków.",
		'array' => ':Attribute musi mieć co najmniej :min element.',
	],
	'not_in' => "Wybrane pole \":attribute\" jest nieprawidłowe.",
	'numeric' => ':Attribute musi być liczbą.',
	'regex' => "Format pola \":attribute\" jest nieprawidłowy",
	'required' => 'Pole ":attribute" jest wymagane.',
	'required_if' => "Pole \":attribute\" jest wymagane.",
	'required_with' => 'pole :attribute jest wymagane, gdy :values są zdefiniowane.',
	'required_with_all' => 'pole :attribute jest wymagane, gdy :values są zdefiniowane.',
	'required_without' => 'pole :attribute jest wymagane, gdy :values nie są zdefiniowane.',
	'required_without_all' => 'pole :attribute jest wymagane, gdy żadne z :values nie są zdefiniowane.',
	'same' => ':Attribute i :other muszą być takie same.',
	'size' => [
		'numeric' => ':Attribute must be :size.',
		'file' => ':Attribute musi mieć :size kilobajtów.',
		'string' => ':Attribute musi mieć :size znaków.',
		'array' => ':Attribute musi zawierać :size pozycji.',
	],
	'string' => "Pole \":attribute\" musi być tekstem.",
	'unique' => "Wartość pola \":Attribute\" jest już zajęta.",
	'url' => 'format :attribute jest nieprawidłowy.',
	'timezone' => ':Attribute musi być prawidłową strefą czasową.',

	/*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

	'custom' => [
		'attribute-name' => [
			'rule-name' => 'custom-message',
		],
	],

	/*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

	'attributes' => [
		'game_id' => 'gra',
		'order' => 'waga wyświetlania',
		'additional_commission_fixed' => 'dodatkowa stała prowizja',
		'additional_commission_percent' => 'dodatkowa procentowa prowizja',
		'invoice_emails' => 'emaile do faktur',
		'name' => 'nazwa',
		'video_url' => 'film',
		'thumbnail' => 'miniaturka',
		'title' => 'tytuł',
		'description' => 'opis',
		'content' => 'treść',
		'weight' => 'waga wyświetlania',
		'active' => 'aktywny',
		'payment_method_id' => 'metoda płatności',
		'payment_method_type' => 'metoda płatności',
		'currency' => 'waluta',
		'locale' => 'język',
		'first_name' => 'imię',
		'last_name' => 'nazwisko',
		'phone' => 'numer telefonu',
		'email' => 'adres email',
		'code' => 'kod',
		'value' => 'wartość',
		'extension' => 'rozszerzenie',
		'type' => 'typ',
		'end_at' => 'data zakończenia',
		'data' => 'dane',
		'start_at' => 'data rozpoczęcia',
		'content_top' => 'treść (góra)',
		'content_bottom' => 'treść (dół)',
		'code' => 'kod',
		'code_quantity' => 'limit',
		'code_quantity_per_customer' => 'limit na klient',
		'club_start' => 'otwarcie klubu',
		'club_end' => 'zamknięcie klubu',
		'club_closed' => 'zamknięcie klubu',
		'reservation_start' => 'otwarcie rezerwacji',
		'reservation_end' => 'zamknięcie rezerwacji',
		'reservation_closed' => 'zamknięcie rezerwacji',
		'time_from' => 'od',
		'time_to' => 'do',
		'price' => 'cena',
		'*.price' => 'cena',
		'exceptions.*.price' => 'cena',
		'days.*.*.price' => 'cena',
		'entries.*.price' => 'cena',
		'color' => 'kolor',
		'active_week_days' => 'zakres dni tygodnia',
		'time' => 'czas',
		'slots' => 'ilość',
		'range_type' => 'typ zakresu',
		'range_from' => 'godzina rozpoczęcia',
		'range_to' => 'godzina zakończenia',
		'when_applies' => 'kiedy obowiązuje',
		'when_not_applies' => 'kiedy nie obowiązuje',
		'club_slug' => 'slug klubu',
		'password' => 'hasło',
		'current_password' => 'obecne hasło',
		'value.*' => 'wartość',
		'icon' => 'Ikona',
		'photo' => 'zdjęcie',
		'mobile_photo' => 'zdjęcie',
		'customer_note' => 'uwagi klienta',
		'club_note' => 'uwagi klubu',
		'customer.first_name' => 'imię',
		'customer.last_name' => 'nazwisko',
		'customer.email' => 'email',
		'customer.phone' => 'telefon',
		'reasonType' => 'powód anulowania',
		'postal_code' => 'kod pocztowy',
		'vat_number' => 'numer VAT',
		'first_login_message' => 'informacja po rejestracji',
		'city' => 'miasto',
		'country' => 'kraj',
		'address' => 'adres',
		'slug' => 'symbol linku',
		'phone_number' => 'numer telefonu',
		'invoice_emails.1' => 'Każda linia',
		'days.*.*.from' => 'od',
		'days.*.*.to' => 'do',
		'time_range.start' => 'Zakres godzin',
		'time_range.end' => 'Zakres godzin',
		'*.fee_percent' => 'Prowizja procentowa',
		'*.fee_fixed' => 'Prowizja stała',
		'reasonContent' => 'Powód anulowania',
		'billing_name' => 'nazwa firmy',
		'billing_address' => 'adres',
		'billing_postal_code' => 'kod pocztowy',
		'billing_city' => 'miasto',
		'billing_nip' => 'numer VAT',
		'billing_lang' => 'Jezyk faktury',
	],
];
