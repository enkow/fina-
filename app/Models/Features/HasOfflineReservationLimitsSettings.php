<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasOfflineReservationLimitsSettings extends Feature
{
	use HasParent;

	public static string $name = 'has_offline_reservation_limits_settings';

	public array $defaultTranslations = [
		'offline-reservation-duration-limit-setting-title' => 'Czas trwania rezerwacji online',
		'offline-reservation-duration-limit-setting-description' =>
			'Ustawiając ten parametr - określisz ile godzin maksymalnie będzie mógł zarezerwować klient płacąc w klubie',
		'offline-reservation-daily-limit-setting-title' => 'Ilość rezerwacji online',
		'offline-reservation-daily-limit-setting-description' =>
			'Ustawiając ten parametr - określisz ile maksymalnie rezerwacji będzie mógł wykonać klient płacąc w klubie',
		'offline-reservation-slot-limit-setting-title' => 'Ilość stołów rezerwacji online',
		'offline-reservation-slot-limit-setting-description' =>
			'Ustawiając ten parametr - określisz ile stołów maksymalnie będzie mógł zarezerwować klient płacąc w klubie',
	];

	public array $conflictedFeatures = [HasOfflineReservationLimitsSettings::class];

	public array $settings = [
		'global' => [
			'offline_reservation_slot_limit' => [
				'type' => 'json',
				'default' => [null, null, null, null, null, null, null],
				'validationRules' => [
					'value' => 'nullable|array',
					'value.*' => 'nullable|numeric|min:0|max:999',
				],
			],
			'offline_reservation_daily_limit' => [
				'type' => 'json',
				'default' => [null, null, null, null, null, null, null],
				'validationRules' => [
					'value' => 'nullable|array',
					'value.*' => 'nullable|numeric|min:0|max:20',
				],
			],
			'offline_reservation_duration_limit' => [
				'type' => 'json',
				'default' => [null, null, null, null, null, null, null],
				'validationRules' => [
					'value' => 'nullable|array',
					'value.*' => 'nullable|numeric|min:0|max:600',
				],
			],
		],
		'club' => [
			'offline_reservation_slot_limit' => [
				'type' => 'json',
				'default' => [null, null, null, null, null, null, null],
				'validationRules' => [
					'value' => 'nullable|array',
					'value.*' => 'nullable|numeric|min:0|max:600',
				],
			],
			'offline_reservation_daily_limit' => [
				'type' => 'json',
				'default' => [null, null, null, null, null, null, null],
				'validationRules' => [
					'value' => 'nullable|array',
					'value.*' => 'nullable|numeric|min:0|max:600',
				],
			],
			'offline_reservation_duration_limit' => [
				'type' => 'json',
				'default' => [null, null, null, null, null, null, null],
				'validationRules' => [
					'value' => 'nullable|array',
					'value.*' => 'nullable|numeric|min:0|max:600',
				],
			],
		],
	];

	public function insertDefaultData(): void
	{
		$this->update([
			'data' => [
				'slot_limit_status' => true,
				'duration_limit_status' => true,
				'daily_reservation_limit_status' => true,
			],
		]);
	}
}
