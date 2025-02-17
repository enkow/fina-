<?php

return [
	/*
    |--------------------------------------------------------------------------
    | Validation Language-Zeilen
    |--------------------------------------------------------------------------
    |
    | Die folgenden Sprachzeilen enthalten die Standard-Fehlermeldungen, die von
    | der Validator-Klasse verwendet werden. Einige dieser Regeln haben mehrere Versionen wie
    | wie die Größenregeln. Es steht Ihnen frei, jede dieser Meldungen hier zu ändern.
    |
    */

	'accepted' => ':Attribute muss akzeptiert werden.', //yes, 1, true
	'active_url' => ':Attribute ist keine gültige URL.',
	'after' => ':Attribute muss ein Datum nach :date sein.',
	'after_or_equal' => ':Attribute muss ein Datum sein, das später oder gleich ist als :date.',
	'alpha' => ':Attribute darf nur Buchstaben enthalten.',
	'alpha_dash' => ':Attribute darf nur Buchstaben, Zahlen und Unterstriche enthalten.',
	'alpha_num' => ':Attribute darf nur Buchstaben und Zahlen enthalten.',
	'array' => ':Attribute muss ein Array sein.',
	'before' => ':Attribute muss ein Datum sein, das vor :date liegt.',
	'between' => [
		'numeric' => ':Attribute muss ein Wert zwischen :min und :max sein.',
		'file' => ':Attribute muss zwischen :min und :max Kilobytes liegen.',
		'string' => ':Attribute muss zwischen :min und :max Zeichen haben.',
		'array' => ':Attribute muss zwischen :min und :max Elemente enthalten.',
	],
	'boolean' => 'Das Feld :Attribute muss true oder false sein',
	'confirmed' => ':Attribute Bestätigung stimmt nicht überein',
	'date' => ':Attribute ist kein gültiges Datum.',
	'date_format' => ':Attribute stimmt nicht mit :format überein.',
	'different' => ':Attribute und :other müssen unterschiedlich sein.',
	'digits' => ':Attribute muss :digits Ziffern haben.',
	'digits_between' => ':Attribute muss zwischen :min und :max Ziffern haben.',
	'distinct' => 'Der Name muss eindeutig sein',
	'email' => ':Attribute muss eine gültige E-Mail-Adresse sein.',
	'exists' => 'Der ausgewählte Wert von ":Attribute" ist ungültig.',
	'gt' => [
		'numeric' => 'Das Feld :Attribute muss größer sein als :value',
		'zero' => 'Das Feld ":attribute" muss größer als 0 sein',
	],
	'gte' => [
		'numeric' => 'Das Feld :Attribute muss größer sein als :value',
	],
	'image' => ':Attribute muss ein Bild sein.',
	'in' => 'das ausgewählte :Attribute ist ungültig.',
	'invalid' => ':attribute ist falsch.',
	'integer' => ':Attribute muss eine ganze Zahl sein.',
	'ip' => ':Attribute muss eine gültige IP-Adresse sein.',
	'max' => [
		'numeric' => 'Das Feld ":Attribute" muss kleiner oder gleich :max sein.',
		'file' => ':Feld :Attribute darf nicht größer sein als :max kilobytes.',
		'string' => "Feld \":Attribute\" darf nicht länger als :max Zeichen sein.",
		'array' => ':Attribute darf nicht mehr als :max Elemente haben.',
	],
	'mimes' => ':Attribute muss ein Dateityp sein: :values.',
	'min' => [
		'numeric' => 'Das Feld ":Attribute" muss größer oder gleich :min sein.',
		'file' => ':Attribute muss mindestens :min Kilobytes groß sein.',
		'string' => 'Feld ‖:Attribute‖ darf nicht kürzer als :min Zeichen sein.',
		'array' => ':Attribute muss mindestens :min Element haben.',
	],
	'not_in' => "Das ausgewählte Feld \":Attribute\" ist ungültig.",
	'numeric' => ':Attribute muss eine Zahl sein.',
	'regex' => 'Format :Attribute ist ungültig',
	'required' => 'Feld ":Attribute" ist erforderlich.',
	'required_if' => 'das Feld :Attribute ist erforderlich, wenn :other den Wert :value hat.',
	'required_with' => 'Das :Attribute Feld ist erforderlich, wenn :values definiert ist.',
	'required_with_all' => 'das :Attribute Feld ist erforderlich, wenn :values definiert ist.',
	'required_without' => 'das :Attribute Feld ist erforderlich, wenn :value nicht definiert sind.',
	'required_without_all' => 'das Feld :Attribute ist erforderlich, wenn keiner der :value definiert ist.',
	'same' => ':Attribute und :other müssen gleich sein.',
	'size' => [
		'numeric' => ':Attribute muss :Größe sein.',
		'file' => ':Attribute muss :size Kilobytes sein.',
		'string' => ':Attribute muss :size Zeichen sein.',
		'array' => ':Attribute muss Elemente der Größe :size enthalten.',
	],
	'string' => 'Das Feld :Attribute muss Text sein.',
	'unique' => ':Attribute ist bereits vergeben.',
	'url' => 'Format :Attribute ist ungültig.',
	'timezone' => ':Attribute muss eine gültige Zeitzone sein.',

	/*
    |--------------------------------------------------------------------------
    | Benutzerdefinierte Validierungssprachenzeilen
    |--------------------------------------------------------------------------
    |
    | Hier können Sie benutzerdefinierte Validierungsmeldungen für Attribute angeben, indem Sie die
    | Konvention "attribute.rule" zur Benennung der Zeilen verwenden. Dies macht es schnell möglich
    | eine bestimmte benutzerdefinierte Sprachzeile für eine bestimmte Attributregel anzugeben.
    |
    */

	'custom' => [
		'attribut-name' => [
			'rule-name' => 'Benutzerdefinierte Nachricht',
		],
	],

	/*
    |--------------------------------------------------------------------------
    | Benutzerdefinierte Validierungsattribute
    |--------------------------------------------------------------------------
    |
    | Die folgenden Sprachzeilen werden verwendet, um unseren Attributplatzhalter auszutauschen
    | Die folgenden Sprachzeilen werden verwendet, um unseren Attributplatzhalter durch etwas Lesefreundlicheres zu ersetzen, z. B. "E-Mail-Adresse" anstelle von "E-Mail".
    | von "E-Mail". Dies hilft uns einfach, unsere Nachricht ausdrucksstärker zu machen.
    |
    */

	'attributes' => [
		'game_id' => 'Spiel',
		'order' => 'Gewicht anzeigen',
		'additional_commission_fixed' => 'zusätzliche feste Provision',
		'additional_commission_percent' => 'zusätzliche prozentuale Provision',
		'invoice_emails' => 'Rechnungs-E-Mails',
		'name' => 'Name',
		'video_url' => 'Video',
		'thumbnail' => 'Vorschaubild',
		'title' => 'Titel',
		'description' => 'description',
		'content' => 'Inhalt',
		'weight' => 'display weight',
		'active' => 'aktiv',
		'payment_method_id' => 'Zahlungsmethode',
		'payment_method_type' => 'Zahlungsart',
		'currency' => 'Währung',
		'locale' => 'Sprache',
		'first_name' => 'Vorname',
		'last_name' => 'Nachname',
		'phone' => 'Telefonnummer',
		'email' => 'E-Mail Adresse',
		'value' => 'Wert',
		'extension' => 'Durchwahl',
		'typ' => 'typ',
		'end_at' => 'end date',
		'data' => 'data',
		'start_at' => 'von',
		'content_top' => 'Inhalt (oben)',
		'content_bottom' => 'Inhalt (unten)',
		'code' => 'code',
		'code_quantity' => 'limit',
		'code_quantity_per_customer' => 'Limit pro Kunde',
		'club_start' => 'club eröffnung',
		'club_end' => 'Clubschließung',
		'club_closed' => 'Club-Schließung',
		'reservation_start' => 'Reservierungseröffnung',
		'reservation_end' => 'Reservierung schließen',
		'reservation_closed' => 'Reservierung schließt',
		'time_from' => 'von',
		'time_to' => 'to',
		'price' => 'price',
		'*.price' => 'price',
		'exceptions.*.price' => 'price',
		'days.*.*.price' => 'Preis',
		'entries.*.price' => 'Preis',
		'color' => 'Farbe',
		'active_week_days' => 'Bereich der Wochentage',
		'time' => 'time',
		'slots' => 'Menge',
		'range_type' => 'Bereichstyp',
		'range_from' => 'Startzeit',
		'range_to' => 'Endzeit',
		'when_applies' => 'wenn gültig',
		'when_not_applies' => 'wenn nicht gültig',
		'club_slug' => 'club slug',
		'password' => 'passwort',
		'current_password' => 'aktuelles Passwort',
		'value.*' => 'wert',
		'icon' => 'icon',
		'photo' => 'photo',
		'mobile_photo' => 'photo',
		'customer_note' => 'Kundennotizen',
		'club_note' => 'Vereinsnotizen',
		'customer.first_name' => 'name',
		'customer.last_name' => 'Nachname',
		'customer.email' => 'e-mail',
		'customer.phone' => 'telefon',
		'reasonType' => 'Grund für die Stornierung',
		'post_code' => 'postleitzahl',
		'vat_number' => 'Mehrwertsteuernummer',
		'first_login_message' => 'Informationen nach der Anmeldung',
		'city' => 'Stadt',
		'address' => 'Adresse',
		'slug' => 'Link-Symbol',
		'phone_number' => 'Telefonnummer',
		'invoice_emails.1' => 'jede Zeile',
		'days.*.*.from' => 'von',
		'days.*.*.to' => 'to',
		'time_range.start' => 'Bereich der Stunden',
		'time_range.end' => 'Bereich von Stunden',
		'*.fee_percent' => 'Prozentuale Provision',
		'*.fee_fixed' => 'Feste Provision',
		'reasonContent' => 'Grund für die Annullierung',
		'billing_name' => 'Firmenname',
		'billing_address' => 'Adresse',
		'billing_postal_code' => 'Postleitzahl',
		'billing_city' => 'Stadt',
	],
];
