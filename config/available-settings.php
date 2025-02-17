<?php
use Illuminate\Validation\Rule;

return [
	'global' => [
		'sms_cost' => [
			'type' => 'integer',
			'default' => 0,
		],
		'refund_time_limit' => [
			'type' => 'integer',
			'default' => 5, //minutes
			'validationRules' => [
				'value' => 'integer',
			],
		],
	],
	'club' => [
		'sms_notifications_status' => [
			'type' => 'boolean',
			'default' => false,
			'validationRules' => [
				'value' => 'required|boolean',
			],
		],
		'email_notifications_status' => [
			'type' => 'boolean',
			'default' => false,
			'validationRules' => [
				'value' => 'required|boolean',
			],
		],
		'additional_club_reservation_status' => [
			'type' => 'boolean',
			'default' => false,
			'validationRules' => [
				'value' => 'required|boolean',
			],
		],
		'reservation_number_on_calendar_status' => [
			'type' => 'boolean',
			'default' => false,
			'validationRules' => [
				'value' => 'required|boolean',
			],
		],
		'reservation_notes_on_calendar_status' => [
			'type' => 'boolean',
			'default' => false,
			'validationRules' => [
				'value' => 'required|boolean',
			],
		],
		'calendar_time_scale' => [
			'type' => 'integer',
			'default' => 30, //minutes
			'validationRules' => [
				'value' => [
					'integer',
					'min:15',
					'max:60',
					Rule::in([15, 30, 60]),
					function ($attribute, $value, $fail) {
						if (
							$value === 60 &&
							\App\Models\Setting::getClubGameSetting(
								clubId(),
								'full_hour_start_reservations_status'
							)['value'] === false
						) {
							$fail(
								__(
									'settings.calendar_time_scale-full_hour_start_reservations_status-combination-error'
								)
							);
						}
					},
				],
			],
		],
		'reservation_max_advance_time' => [
			'type' => 'integer',
			'default' => 14, //days
			'validationRules' => [
				'value' => 'required|integer|min:0',
			],
		],
		'reservation_min_advance_time' => [
			'type' => 'json',
			'default' => [0, 0, 0, 0, 0, 0, 0], //hours
			'validationRules' => [
				'value' => 'nullable|array',
				'value.*' => 'nullable|numeric|min:0|max:2147483648',
			],
		],
		'full_hour_start_reservations_status' => [
			'type' => 'boolean',
			'default' => false,
			'validationRules' => [
				'value' => [
					'required',
					'boolean',
					function ($attribute, $value, $fail) {
						if (
							$value === false &&
							\App\Models\Setting::getClubGameSetting(clubId(), 'calendar_time_scale')[
								'value'
							] === 60
						) {
							$fail(
								__(
									'settings.full_hour_start_reservations_status-calendar_time_scale-combination-error'
								)
							);
						}
					},
				],
			],
		],
		'club_terms' => [
			'type' => 'file',
			'disk' => 'clubTerms',
			'default' => null,
			'validationRules' => [
				'value' => 'nullable|file|mimes:pdf|max:5120',
			],
		],
		'widget_color' => [
			'type' => 'string',
			'default' => '#85C767',
			'validationRules' => [
				'value' => 'required|string|regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/',
			],
		],
		'widget_message' => [
			'type' => 'string',
			'default' => null,
			'validationRules' => [
				'value' => 'nullable|string|max:10000',
			],
		],
		'timers_status' => [
			'type' => 'boolean',
			'default' => false,
			'adminOnlyEdit' => true,
			'validationRules' => [
				'value' => 'required|boolean',
			],
		],
		'calendar_sync_status' => [
			'type' => 'boolean',
			'default' => false,
			'adminOnlyEdit' => true,
			'validationRules' => [
				'value' => 'required|boolean',
			],
		],
		'refund_time_limit' => [
			'type' => 'integer',
			'default' => null, //minutes
			'loadGlobalIfEmpty' => true,
			'validationRules' => [
				'value' => 'integer',
			],
		],
		'additional_commission_fixed' => [
			'type' => 'integer',
			'default' => 0,
			'validationRules' => [
				'value' => 'required|integer',
			],
			'prepareForValidation' => function ($value) {
				return amountToSmallestUnits($value);
			},
		],
		'additional_commission_percent' => [
			'type' => 'integer',
			'default' => 0,
			'validationRules' => [
				'value' => 'required|integer',
			],
			'prepareForValidation' => function ($value) {
				return amountToSmallestUnits($value);
			},
		],
	],
];
