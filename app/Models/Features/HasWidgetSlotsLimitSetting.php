<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasWidgetSlotsLimitSetting extends Feature
{
	use HasParent;

	public static string $name = 'has_slot_widget_quantity_limit_setting';

	public array $defaultTranslations = [
		'setting-title' => 'Ilość stołów na wtyczce rezerwacyjnej',
		'setting-description' =>
			'Ustawiając ten parametr - określisz ile slotów maksymalnie będzie mógł zarezerwować klient na wtyczce',
	];

	public array $conflictedFeatures = [HasWidgetSlotsLimitSetting::class, BookSingularSlotByCapacity::class];

	public array $settings = [
		'global' => [],
		'club' => [
			'widget_slots_limit' => [
				'type' => 'json',
				'default' => [5, 5, 5, 5, 5, 5, 5],
				'validationRules' => [
					'value' => 'nullable|array',
					'value.*' => 'nullable|numeric|min:0|max:999',
				],
			],
		],
	];
}
