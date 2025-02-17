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

	'accepted' => ':Attribute turi būti priimtas.', //yes, 1, true
	'active_url' => ':Attribute is not a valid URL.',
	'after' => ':Attribute turi būti vėlesnė data nei :date.',
	'after_or_equal' => ':Attribute turi būti data, vėlesnė už :data arba tokia pati kaip :data.',
	'alpha' => ':Attribute gali būti tik raidės.',
	'alpha_dash' => ':Atributą gali sudaryti tik raidės, skaičiai ir pabraukimai.',
	'alpha_num' => ':Atributą gali sudaryti tik raidės ir skaičiai.',
	'array' => ':Attribute turi būti masyvas.',
	'before' => ':Attribute turi būti ankstesnė data nei :date.',
	'between' => [
		'numeric' => ':Attribute turi būti reikšmė tarp :min ir :max.',
		'file' => ':Attribute turi būti tarp :min ir :max kilobaitų.',
		'string' => ':Attribute turi būti nuo :min iki :max simbolių.',
		'array' => ':Attribute turi turėti nuo :min iki :max elementų.',
	],
	'boolean' => 'Laukas :attribute turi būti true arba false',
	'confirmed' => ':Atributo patvirtinimas nesutampa.',
	'date' => ':Attribute nėra galiojanti data.',
	'date_format' => ':Attribute neatitinka :format.',
	'different' => ':Attribute ir :other turi būti skirtingi.',
	'digits' => ':Attribute must have :digits digits.',
	'digits_between' => ':Attribute must have between :min and :max digits.',
	'distinct' => 'Pavadinimas turi būti unikalus',
	'email' => ':Attribute turi būti galiojantis el. pašto adresas.',
	'exists' => 'Pasirinkta ":Attribute" reikšmė negalioja.',
	'gt' => [
		'numeric' => 'Laukas :attribute turi būti didesnis už :reikšmė',
		'zero' => 'Laukas ":attribute" turi būti didesnis nei 0',
	],
	'gte' => [
		'numeric' => 'Laukas :attribute turi būti didesnis už :value',
	],
	'image' => ':Attribute turi būti atvaizdas',
	'in' => 'Pasirinktas :attribute negalioja',
	'integer' => ':Attribute turi būti sveikasis skaičius.',
	'ip' => ':Attribute turi būti galiojantis IP adresas.',
	'max' => [
		'numeric' => 'Lauko :attribute turi būti mažesnis arba lygus :max.',
		'file' => ':Attribute neturi būti didesnis nei :max kilobaitai.',
		'string' => ':Attribute neturi būti ilgesnis nei :max simbolių.',
		'array' => ':Attribute negali turėti daugiau nei :max elementų.',
	],
	'mimes' => ':Attribute turi būti failo tipas: :values.',
	'min' => [
		'numeric' => 'Laukas :Attribute turi būti didesnis arba lygus :min.',
		'file' => ':Attribute turi būti ne mažesnis kaip :min kilobaitai.',
		'string' => ':Attribute turi būti ne mažesnis kaip :min simbolių.',
		'array' => ':Attribute turi turėti bent 1 elementą.',
	],
	'not_in' => "Pasirinktas laukas `:attribute' negalioja.",
	'numeric' => ':Attribute turi būti skaičius.',
	'regex' => 'formatas :attribute negalioja',
	'required' => 'Būtina užpildyti teksto lauką ":attribute"',
	'required_if' => 'laukas :attribute yra privalomas, kai :other turi reikšmę :value',
	'required_with' => 'laukas :attribute yra privalomas, kai apibrėžta :reikšmė',
	'required_with_all' => ':Attribute laukas yra privalomas, kai yra apibrėžtos :reikšmės.',
	'required_without' => ':Attribute laukas yra privalomas, kai :reikšmės nėra apibrėžtos.',
	'required_without_all' => ':Attribute laukas yra privalomas, kai nė viena iš :reikšmių nėra apibrėžta.',
	'same' => ':Attribute ir :other turi būti vienodi.',
	'size' => [
		'numeric' => ':Attribute turi būti :size.',
		'file' => ':Attribute turi būti :size kilobaitai.',
		'string' => ':Attribute must be :character size.',
		'array' => ':Attribute must contain :size items.',
	],
	'string' => 'Laukas :attribute turi būti tekstinis.',
	'unique' => ':Attribute jau užimtas.',
	'url' => 'formatas :attribute negalioja.',
	'timezone' => ':Attribute turi būti galiojanti laiko juosta.',

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
		'game_id' => 'žaidimas',
		'order' => 'seka',
		'additional_commission_fixed' => 'papildoma fiksuota komisinių suma',
		'additional_commission_percent' => 'papildomi procentiniai komisiniai',
		'invoice_emails' => 'sąskaitų faktūrų el. laiškai',
		'name' => 'vardas',
		'video_url' => 'filmas',
		'thumbnail' => 'miniatiūra',
		'title' => 'pavadinimas',
		'description' => 'aprašymas',
		'content' => 'Žinutė',
		'weight' => 'rošties svoris',
		'active' => 'aktyvus',
		'payment_method_id' => 'mokėjimo metodas',
		'payment_method_type' => 'mokėjimo metodas',
		'currency' => 'valiuta',
		'locale' => 'kalba',
		'first_name' => 'vardas',
		'last_name' => 'pavardė',
		'phone' => 'telefono numeris',
		'email' => 'el. paštas',
		'value' => 'vertė',
		'extension' => 'pratęsimas',
		'type' => 'tipas',
		'end_at' => 'pabaigos data',
		'data' => 'duomenys',
		'start_at' => 'Data',
		'content_top' => 'turinys (viršus)',
		'content_bottom' => 'turinys (apačia)',
		'code' => 'kodas',
		'code_quantity' => 'limitas',
		'code_quantity_per_customer' => 'limitas klientui',
		'club_start' => 'Klubo darbo laikas',
		'club_end' => '⠀',
		'club_closed' => 'klubo uždarymas',
		'reservation_start' => 'rezervacijos pradžia',
		'reservation_end' => 'rezervacijos pabaiga',
		'reservation_closed' => 'rezervacijos uždarymas',
		'time_from' => 'nuo',
		'time_to' => 'iki',
		'price' => 'kaina',
		'*.price' => 'kaina',
		'exceptions.*.price' => 'kaina',
		'days.*.*.price' => 'kaina',
		'entries.*.price' => 'kaina',
		'color' => 'spalva',
		'active_week_days' => 'savaitės dienų intervalas',
		'time' => 'laikas',
		'slots' => 'kiekis',
		'range_type' => 'diapazono tipas',
		'range_from' => 'pradžios laikas',
		'range_to' => 'pabaigos laikas',
		'when_applies' => 'taikoma',
		'when_not_applies' => 'netaikoma',
		'club_slug' => 'klubo nuoroda',
		'password' => 'slaptažodis',
		'current_password' => 'esamasis slaptažodis',
		'value.*' => 'vertė',
		'icon' => 'piktograma',
		'photo' => 'nuotrauka',
		'mobile_photo' => 'nuotrauka',
		'customer_note' => 'kliento pastaba',
		'club_note' => 'klubo pastaba',
		'customer.first_name' => 'vardas',
		'customer.last_name' => 'pavardė',
		'customer.email' => 'el. paštas',
		'customer.phone' => 'telefonas',
		'reasonType' => 'atšaukimo priežastis',
		'postal_code' => 'pašto kodas',
		'vat_number' => 'PVM kodas',
		'first_login_message' => 'informacija po registracijos',
		'city' => 'miestas',
		'address' => 'adresas',
		'slug' => 'nuorodos dalis',
		'phone_number' => 'telefono numeris',
		'days.*.*.from' => 'nuo',
		'days.*.*.to' => 'iki',
		'invoice_emails.1' => 'kiekvieną eilutę',
		'time_range.start' => 'Valandų diapazonas',
		'time_range.end' => 'Valandų diapazonas',
		'*.fee_percent' => 'Procentinis komisinis atlyginimas',
		'*.fee_fixed' => 'Fiksuoti komisiniai',
		'reasonContent' => 'Atšaukimo priežastis',
		'billing_name' => 'įmonės pavadinimas',
		'billing_address' => 'adresas',
		'billing_postal_code' => 'pašto kodas',
		'billing_city' => 'miestas',
	],
];
