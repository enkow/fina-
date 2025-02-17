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

	'accepted' => ':Attribute must be accepted.', //yes, 1, true
	'active_url' => ':Attribute is not a valid URL.',
	'after' => ':Attribute must be a date later than :date.',
	'after_or_equal' => ':Attribute must be a date later than or the same as :date.',
	'alpha' => ':Attribute can only contain letters.',
	'alpha_dash' => ':Attribute can only contain letters, numbers and underscores.',
	'alpha_num' => ':Attribute can only contain letters and numbers.',
	'array' => ':Attribute must be an array.',
	'before' => ':Attribute must be a date earlier than :date.',
	'between' => [
		'numeric' => ':Attribute must be a value between :min and :max.',
		'file' => ':Attribute must be between :min and :max kilobytes.',
		'string' => ':Attribute must have between :min and :max characters.',
		'array' => ':Attribute must have between :min and :max items.',
	],
	'boolean' => 'The :attribute value must be true or false',
	'confirmed' => ':Attribute confirmation does not match.',
	'date' => ':Attribute is not a valid date.',
	'date_format' => ':Attribute does not match :format.',
	'different' => ':Attribute and :other must be different.',
	'digits' => ':Attribute must have :digits digits.',
	'digits_between' => ':Attribute must have between :min and :max digits.',
	'distinct' => 'The name must be unique',
	'email' => ':Attribute must be a valid email address.',
	'exists' => 'The selected value of ":attribute" is invalid.',
	'gt' => [
		'numeric' => 'The :attribute value must be greater than :value.',
		'zero' => 'The ":attribute" field must be greater than 0',
	],
	'gte' => [
		'numeric' => 'The :attribute value must be greater than :value',
	],
	'image' => ':Attribute must be an image.',
	'in' => 'the selected :attribute is invalid.',
	'invalid' => ':attribute is invalid.',
	'integer' => ':Attribute must be an integer.',
	'ip' => ':Attribute must be a valid IP address.',
	'max' => [
		'numeric' => 'Value ":attribute" must be less than or equal to :max.',
		'file' => ':Attribute value must not be larger than :max kilobytes.',
		'string' => "Value \":attribute\" must not be longer than :max characters.",
		'array' => ':Attribute cannot have more than :max items.',
	],
	'mimes' => ':Attribute must be a file type: :values.',
	'min' => [
		'numeric' => 'The ":Attribute" value must be greater than or equal to :min.',
		'file' => ':Attribute must be at least :min kilobytes.',
		'string' => "Value \":attribute\" must not be shorter than :min characters.",
		'array' => ':Attribute must have at least :min element.',
	],
	'not_in' => "The selected value \":attribute\" is invalid.",
	'numeric' => ':Attribute must be a number.',
	'regex' => 'format :attribute is invalid',
	'required' => 'The ":attribute" value is required.',
	'required_if' => 'the :attribute value is required when :other has a value of :value.',
	'required_with' => 'the :attribute value is required when :values are defined.',
	'required_with_all' => 'the :attribute value is required when :values are defined.',
	'required_without' => 'the :attribute value is required when :values are not defined.',
	'required_without_all' => 'the :attribute value is required when none of the :values are defined.',
	'same' => ':Attribute and :other must be the same.',
	'size' => [
		'numeric' => ':Attribute must be :size.',
		'file' => ':Attribute must be :size kilobytes.',
		'string' => ':Attribute must be :size characters.',
		'array' => ':Attribute must contain :size items.',
	],
	'string' => "The \":attribute\" value must be text.",
	'unique' => ':Attribute is already taken.',
	'url' => 'format :attribute is invalid.',
	'timezone' => ':Attribute must be a valid time zone.',

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
    | The following language lines are used to swap our attribute placeholder with something more reader friendly such as "E-Mail Address" instead of "email".
    | of "email". This simply helps us make our message more expressive.
    |
    */

	'attributes' => [
		'game_id' => 'game',
		'order' => 'display weight',
		'additional_commission_fixed' => 'additional fixed commission',
		'additional_commission_percent' => 'additional percentage commission',
		'invoice_emails' => 'invoice emails',
		'name' => 'name',
		'video_url' => 'video',
		'thumbnail' => 'thumbnail',
		'title' => 'title',
		'description' => 'description',
		'content' => 'content',
		'weight' => 'display weight',
		'active' => 'active',
		'payment_method_id' => 'payment method',
		'payment_method_type' => 'payment method',
		'currency' => 'currency',
		'locale' => 'language',
		'first_name' => 'name',
		'last_name' => 'last name',
		'phone' => 'phone number',
		'email' => 'email address',
		'value' => 'value',
		'extension' => 'extension',
		'type' => 'type',
		'end_at' => 'end date',
		'data' => 'data',
		'start_at' => 'Date',
		'content_top' => 'content (top)',
		'content_bottom' => 'content (bottom)',
		'code' => 'code',
		'code_quantity' => 'limit',
		'code_quantity_per_customer' => 'limit per customer',
		'club_start' => 'club opening hours',
		'club_end' => 'club closing',
		'club_closed' => 'club closing',
		'reservation_start' => 'reservation opening',
		'reservation_end' => 'reservation closing',
		'reservation_closed' => 'reservation closing',
		'time_from' => 'from',
		'time_to' => 'to',
		'price' => 'price',
		'*.price' => 'price',
		'exceptions.*.price' => 'price',
		'days.*.*.price' => 'price',
		'entries.*.price' => 'price',
		'color' => 'color',
		'active_week_days' => 'range of days of the weekdays',
		'time' => 'time',
		'slots' => 'quantity',
		'range_type' => 'range type',
		'range_from' => 'start time',
		'range_to' => 'end time',
		'when_applies' => 'when valid',
		'when_not_applies' => 'when not in effect',
		'club_slug' => 'club slug',
		'password' => 'password',
		'current_password' => 'current password',
		'value.*' => 'value',
		'icon' => 'icon',
		'photo' => 'photo',
		'mobile_photo' => 'photo',
		'customer_note' => 'customer notes',
		'club_note' => 'club notes',
		'customer.first_name' => 'name',
		'customer.last_name' => 'last name',
		'customer.email' => 'email',
		'customer.phone' => 'phone',
		'reasonType' => 'reason for cancellation',
		'postal_code' => 'postal code',
		'vat_number' => 'VAT number',
		'first_login_message' => 'information after registration',
		'city' => 'city',
		'address' => 'address',
		'slug' => 'link symbol',
		'phone_number' => 'phone number',
		'invoice_emails.1' => 'each line',
		'days.*.*.from' => 'from',
		'days.*.*.to' => 'to',
		'time_range.start' => 'Range of hours',
		'time_range.end' => 'Range of hours',
		'*.fee_percent' => 'Percentage commission',
		'*.fee_fixed' => 'Fixed commission',
		'reasonContent' => 'Reason for cancellation',
		'billing_name' => 'company name',
		'billing_address' => 'address',
		'billing_postal_code' => 'postcode',
		'billing_city' => 'city',
	],
];
