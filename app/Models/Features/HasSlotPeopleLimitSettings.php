<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasSlotPeopleLimitSettings extends Feature
{
	use HasParent;

	public static string $name = 'has_slot_people_limit_settings';

	public array $defaultTranslations = [
		'max-setting-title' => 'Maksymalna ilość osób na 1 slocie',
		'max-setting-description' =>
			'Ustawiając ten parametr - określisz ile maksymalnie osób do gry na jednym torze będzie mógł wybrać klient online podczas dokonywania rezerwacji.',
		'max-setting-label' => 'Aktualna wartość (osoby)',
		'min-setting-title' => 'Minimalna ilość osób na 1 slocie',
		'min-setting-description' =>
			'Ustawiając ten parametr - określisz ile minimalnie osób do gry na jednym torze będzie mógł wybrać klient online podczas dokonywania rezerwacji.',
		'min-setting-label' => 'Aktualna wartość (osoby)',
	];

	public array $conflictedFeatures = [
		HasSlotPeopleLimitSettings::class,
		BookSingularSlotByCapacity::class,
		PersonAsSlot::class,
	];

	public array $settings = [
		'global' => [],
		'club' => [
			'slot_people_max_limit' => [
				'type' => 'integer',
				'default' => 5,
				'validationRules' => [
					'value' => 'nullable|integer|min:1|max:100',
				],
			],
			'slot_people_min_limit' => [
				'type' => 'integer',
				'default' => null,
				'validationRules' => [
					'value' => 'nullable|integer|min:0|max:100',
				],
			],
		],
	];
}
