<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasVisibleCalendarSlotsQuantitySetting extends Feature
{
	use HasParent;

	public static string $name = 'has_visible_calendar_slots_quantity_setting';

	public array $defaultTranslations = [
		'setting-title' => 'Liczba stołów bilardowych bez scrolla na kalendarzu',
		'setting-description-bolder' =>
			'Ustawiając ten parametr określisz ile stołów bilardowych będzie widocznych bez scrolla na kalendarzu klubu.',
		'setting-description' =>
			'Jest to bardzo przydatne jeśli masz dużo stołów bilardowych a pracujesz np. na tablecie gdzie jest mały ekran. I ustawienie wszystkich dostępnych stołów spowoduje,że kolumny będą bardzo wąskie i praca na kalendarzu będzie utrudniona.',
		'setting-label' => 'Ilość stołów bilardowych bez scrolla',
	];

	public array $conflictedFeatures = [PersonAsSlot::class, HasVisibleCalendarSlotsQuantitySetting::class];

	public array $settings = [
		'global' => [],
		'club' => [
			'visible_calendar_slots_quantity' => [
				'type' => 'integer',
				'default' => 8,
				'validationRules' => [
					'value' => 'nullable|integer|min:1|max:100',
				],
			],
		],
	];
}
