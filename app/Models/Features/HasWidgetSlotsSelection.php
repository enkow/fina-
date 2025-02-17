<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasWidgetSlotsSelection extends Feature
{
	use HasParent;

	public static string $name = 'has_widget_slots_selection';

	public array $settings = [
		'global' => [],
		'club' => [
			'widget_slots_selection_status' => [
				'type' => 'boolean',
				'default' => true,
				'adminOnlyEdit' => true,
				'validationRules' => [
					'value' => 'required|boolean',
				],
			],
		],
	];

	public array $defaultTranslations = [
		'status-setting-title' => 'Wybór stołów na wtyczce',
		'status-setting-description' => 'Wybór stołów na wtyczce - opis',
		'status-setting-option' => 'Wybór stołu',
	];

	public array $conflictedFeatures = [PersonAsSlot::class, BookSingularSlotByCapacity::class];
}
