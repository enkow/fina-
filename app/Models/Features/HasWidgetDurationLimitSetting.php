<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasWidgetDurationLimitSetting extends Feature
{
	use HasParent;

	public static string $name = 'has_widget_duration_limit_setting';

	public array $defaultTranslations = [
		'setting-title' => 'Maksymalny czas rezerwacji na wtyczce rezerwacyjnej',
		'setting-description' =>
			'Ustawiając ten parametr - określisz ile godzin maksymalnie będzie mógł zarezerwować klient na wtyczce',
	];

	public array $conflictedFeatures = [
		HasWidgetDurationLimitSetting::class,
		BookSingularSlotByCapacity::class,
	];

	public array $settings = [
		'global' => [],
		'club' => [
			'widget_duration_limit' => [
				'type' => 'json',
				'default' => [10, 10, 10, 10, 10, 10, 10],
				'validationRules' => [
					'value' => 'nullable|array',
					'value.*' => 'nullable|numeric|min:0|max:20',
				],
			],
		],
	];
}
