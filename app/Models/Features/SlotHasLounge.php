<?php

namespace App\Models\Features;

use App\Models\Feature;
use App\Models\Slot;
use App\Models\Translation;
use JsonException;
use Parental\HasParent;

class SlotHasLounge extends Feature
{
	use HasParent;

	public static string $name = 'slot_has_lounge';

	public array $defaultTranslations = [
		'slots-capacity' => 'Ilość osób',
		'slots-others' => 'Pozostałe',
		'slots-table-heading' => 'Loża',
		'slot-create-status-label' => 'Loża',
		'slot-create-min-label' => 'Minimum gości',
		'slot-create-max-label' => 'Maksimum gości',
		'lounge-capacity-items' => 'osób',
		'status-setting-title' => 'Wybór stołów na wtyczce',
		'status-setting-description' => 'Wybór stołów na wtyczce - opis',
		'status-setting-option' => 'Wybór loży',
	];

	public array $conflictedFeatures = [PersonAsSlot::class, SlotHasLounge::class];

	public array $settings = [
		'global' => [],
		'club' => [
			'lounges_status' => [
				'type' => 'boolean',
				'default' => false,
				'adminOnlyEdit' => true,
				'validationRules' => [
					'value' => 'required|boolean',
				],
			],
		],
	];

	public function prepareSlotDataForValidation(array|null $data = []): array
	{
		$data['min'] = is_numeric($data['min']) ? (int) $data['min'] : $data['min'];
		$data['max'] = is_numeric($data['max']) ? (int) $data['max'] : $data['max'];

		return $data;
	}

	public function getSlotDataValidationNiceNames(): array
	{
		$translations = Translation::retrieve(countryId: club()->country_id, featureId: $this->id);

		return [
			"features.$this->id.min" => $translations['slot-create-min-label'],
			"features.$this->id.max" => $translations['slot-create-max-label'],
			"features.$this->id.status" => $translations['slot-create-status-label'],
		];
	}

	public function getSlotDataValidationRules(): array
	{
		return [
			"features.$this->id.status" => 'required|boolean',
			"features.$this->id.min" =>
				'nullable|required_if:features.' . $this->id . '.status,true|integer|min:1|max:100',
			"features.$this->id.max" => "nullable|required_if:features.$this->id.status,true|integer|min:1|max:100|gte:features.$this->id.min",
		];
	}

	/**
	 * @throws JsonException
	 */
	public function updateSlotData(Slot $slot, array $data = []): bool
	{
		$featuresData = $data['features'][$this->id];
		$this->slots()->detach($slot);
		$this->slots()->attach($slot, [
			'data' => json_encode(
				[
					'status' => $featuresData['status'],
					'min' => $featuresData['min'],
					'max' => $featuresData['max'],
				],
				JSON_THROW_ON_ERROR
			),
		]);

		return true;
	}
}
