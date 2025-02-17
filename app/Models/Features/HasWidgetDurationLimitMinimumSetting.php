<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasWidgetDurationLimitMinimumSetting extends Feature
{
	use HasParent;

	public static string $name = 'has_widget_duration_limit_minimum_setting';

	public array $defaultTranslations = [
		'setting-title' => 'Minimalny czas rezerwacji na wtyczce rezerwacyjnej',
		'setting-description' =>
			'Ustawiając ten parametr - określisz ile godzin minimalnie będzie mógł zarezerwować klient na wtyczce',
	];

	public array $conflictedFeatures = [
		HasWidgetDurationLimitMinimumSetting::class,
		BookSingularSlotByCapacity::class,
	];

	public array $settings = [
		'global' => [],
		'club' => [
			'widget_duration_limit_minimum' => [
				'type' => 'json',
				'default' => [0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5],
				'validationRules' => [
					'value' => 'nullable|array',
					'value.*' => 'nullable|numeric|min:0|max:20',
				],
			],
		],
	];
}