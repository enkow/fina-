<?php

return [
	/*
    |--------------------------------------------------------------------------
    | Språkrader för validering
    |--------------------------------------------------------------------------
    |
    | Följande språkrader innehåller de standardfelmeddelanden som används av
    | valideringsklassen. Vissa av dessa regler har flera versioner, t.ex.
    | som storleksreglerna. Du är välkommen att justera vart och ett av dessa meddelanden här.
    |
    */

	'accepted' => ':Attribute måste vara accepterat.', //yes, 1, true
	'active_url' => ':Attribute är inte en giltig URL.',
	'after' => ':Attribute måste vara ett datum senare än :date.',
	'after_or_equal' => ':Attribute måste vara ett datum senare än eller samma som :date.',
	'alpha' => ':Attribute kan bara innehålla bokstäver.',
	'alpha_dash' => ':Attribute kan bara innehålla bokstäver, siffror och understreck.',
	'alpha_num' => ':Attribute kan bara innehålla bokstäver och siffror.',
	'array' => ':Attribute måste vara en array.',
	'before' => ':Attribute måste vara ett datum tidigare än :date.',
	'between' => [
		'numeric' => ':Attribute måste vara ett value mellan :min och :max.',
		'file' => ':Attribute måste vara mellan :min och :max kilobytes.',
		'string' => ':Attribute måste ha mellan :min och :max tecken.',
		'array' => ':Attribute måste ha mellan :min och :max items.',
	],
	'boolean' => 'Fältet :attribute måste vara true eller false',
	'confirmed' => ':Attributees bekräftelse matchar inte.',
	'date' => ':Attribute är inte ett giltigt datum.',
	'date_format' => ':Attribut matchar inte :format.',
	'different' => ':Attribut och :other måste vara olika.',
	'digits' => ':Attribute måste ha :digits siffror.',
	'digits_between' => ':Attribute måste ha mellan :min och :max siffror.',
	'distinct' => 'Namnet måste vara unikt',
	'email' => ':Attribute måste vara en giltig e-postadress.',
	'exists' => 'Det valda värdet för ":Attribute" är ogiltigt.',
	'gt' => [
		'numeric' => 'Fältet :attribute måste vara större än :value',
		'zero' => 'Fältet ":attribute" måste vara större än 0',
	],
	'gte' => [
		'numeric' => 'Fältet :attribute måste vara större än :value',
	],
	'image' => ':Attribute måste vara en bild',
	'in' => 'Det valda :Attribute är ogiltigt',
	'integer' => ':Attribute måste vara ett heltal.',
	'ip' => ':Attribute måste vara en giltig IP-adress.',
	'max' => [
		'numeric' => 'Fältet ":Attribute" måste vara mindre än eller lika med :max.',
		'file' => ':Attribute får inte vara större än :max kilobyte.',
		'string' => "Fältet \":Attribute\" får inte vara längre än :max tecken.",
		'array' => ':Attribut får inte ha fler än :max objekt.',
	],
	'mimes' => ':Attribute måste vara en fil av typen: :värden.',
	'min' => [
		'numeric' => 'Fältet ":attribute" måste vara större än eller lika med :min.',
		'file' => ':Attribute måste vara minst :min kilobyte.',
		'string' => "Fältet \":attribute\" får inte vara kortare än :min tecken.",
		'array' => ':Attribute måste ha minst :min element.',
	],
	'not_in' => "Det valda fältet \":Attribute\" är ogiltigt.",
	'numeric' => ':Attribute måste vara ett tal.',
	'regex' => 'format :attribut är ogiltigt',
	'required' => 'Fältet ":attribute" är obligatoriskt.',
	'required_if' => 'fältet :attribute är obligatoriskt när :other har ett value av :value.',
	'required_with' => 'Fältet :attribute krävs när :values är definierat.',
	'required_with_all' => ':attributfältet är obligatoriskt när :värden är definierade.',
	'required_without' => ':attributfältet är obligatoriskt när :värden inte är definierade.',
	'required_without_all' => ':attributfältet är obligatoriskt när inget av :värdena är definierade.',
	'same' => ':Attribute och :other måste vara samma.',
	'size' => [
		'numeric' => ':Attribut måste vara :size.',
		'file' => ':Attribute måste vara :size kilobytes.',
		'string' => ':Attribute måste vara :teckenstorlek.',
		'array' => ':Attribute måste innehålla :size-objekt.',
	],
	'string' => "Fältet \":Attribute\" måste vara text.",
	'unique' => ':Attribute är redan upptaget.',
	'url' => 'format :attribut är ogiltigt.',
	'timezone' => ':Attribute måste vara en giltig tidszon.',

	/*
    |--------------------------------------------------------------------------
    | Anpassade språkrader för validering
    |--------------------------------------------------------------------------
    |
    | Här kan du ange anpassade valideringsmeddelanden för attribut med hjälp av
    | konventionen "attribut.regel" för att namnge raderna. Detta gör det snabbt att
    | ange en specifik anpassad språkrad för en viss attributregel.
    |
    */

	'custom' => [
		'attribut-name' => [
			'rule-name' => 'anpassat meddelande',
		],
	],

	/*
    |--------------------------------------------------------------------------
    | Anpassade valideringsattribut
    |--------------------------------------------------------------------------
    |
    | Följande språkrader används för att byta ut vår attributplatshållare
    | med något mer läsarvänligt, till exempel "E-postadress" istället för
    | av "e-post". Detta hjälper oss helt enkelt att göra vårt meddelande mer uttrycksfullt.
    |
    */

	'attributes' => [
		'game_id' => 'spel',
		'order' => 'visningsvikt',
		'additional_commission_fixed' => 'extra fast provision',
		'additional_commission_percent' => 'extra procentuell provision',
		'invoice_emails' => 'e-postmeddelanden för fakturor',
		'name' => 'namn',
		'video_url' => 'video',
		'thumbnail' => 'miniatyrbild',
		'title' => 'titel',
		'description' => 'beskrivning',
		'content' => 'innehåll',
		'weight' => 'visningsvikt',
		'active' => 'aktiv',
		'payment_method_id' => 'betalningsmetod',
		'payment_method_type' => 'betalningsmetod',
		'currency' => 'valuta',
		'locale' => 'språk',
		'first_name' => 'namn',
		'last_name' => 'efternamn',
		'phone' => 'telefonnummer',
		'email' => 'e-postadress',
		'value' => 'värde',
		'extension' => 'anknytning',
		'type' => 'spel',
		'end_at' => 'slutdatum',
		'data' => 'data',
		'start_at' => 'startdatum',
		'content_top' => 'innehåll (överst)',
		'content_bottom' => 'innehåll (nederst)',
		'code' => 'kod',
		'code_quantity' => 'max',
		'code_quantity_per_customer' => 'max per kund',
		'club_start' => 'klubbens öppning',
		'club_end' => 'klubbens stängning',
		'club_closed' => 'klubbens stängning',
		'reservation_start' => 'reservation opening',
		'reservation_end' => 'reservation closed',
		'reservation_closed' => 'reservation stängt',
		'time_from' => 'från',
		'time_to' => 'till',
		'price' => 'pris',
		'*.price' => 'pris',
		'exceptions.*.price' => 'pris',
		'days.*.*.price' => 'pris',
		'entries.*.price' => 'pris',
		'color' => 'färg',
		'active_week_days' => 'tidsintervall av veckodagar',
		'time' => 'tid',
		'slots' => 'kvantitet',
		'range_type' => 'typ av intervall',
		'range_from' => 'starttidpunkt',
		'range_to' => 'sluttid',
		'when_applies' => 'när den är giltig',
		'when_not_applies' => 'när den inte är i kraft',
		'club_slug' => 'klubbens slug',
		'password' => 'lösenord',
		'current_password' => 'aktuellt lösenord',
		'value.*' => 'värde',
		'icon' => 'ikon',
		'photo' => 'foto',
		'mobile_photo' => 'foto',
		'customer_note' => 'kundanteckningar',
		'club_note' => 'klubbens anmärkningar',
		'customer.first_name' => 'namn',
		'customer.last_name' => 'efternamn',
		'customer.email' => 'email',
		'customer.phone' => 'telefonnummer',
		'reasonType' => 'Anledning till annullering',
		'postal_code' => 'postnummer',
		'vat_number' => 'Momsregistreringsnummer',
		'first_login_message' => 'Information efter registrering',
		'city' => 'stad',
		'address' => 'adress',
		'slug' => 'länksymbol',
		'phone_number' => 'telefonnummer',
		'invoice_emails.1' => 'varje rad',
		'days.*.*.from' => 'från',
		'days.*.*.to' => 'till',
		'time_range.start' => 'Tidsintervall',
		'time_range.end' => 'Tidsintervall',
		'*.fee_percent' => 'Procentuell provision',
		'*.fee_fixed' => 'Fast provision',
		'reasonContent' => 'Orsak till avbokning',
		'billing_name' => 'Företagets namn',
		'billing_address' => 'adress',
		'billing_postal_code' => 'postnummer',
		'billing_city' => 'stad',
	],
];
