<?php

namespace App\Models\Features;

use App\Http\Resources\SlotResource;
use App\Models\Club;
use App\Models\Feature;
use App\Models\Slot;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use JsonException;
use Parental\HasParent;

class SlotHasType extends Feature
{
	use HasParent;

	public static string $name = 'slot_has_type';

	public array $defaultTranslations = [
		'slots-table-heading' => 'Gra',
		'slot-create-label' => 'Gra',
		'type' => 'Gra',
		'mail-label' => 'Typ gry',
		'widget-select-name' => 'Dowolny typ gry',
		'widget-select-any' => 'Dowolny',
	];

	public array $conflictedFeatures = [PersonAsSlot::class, SlotHasType::class];

	public function insertDefaultData(): void
	{
		$this->update([
			'data' => [
				'options' => ['Bilard', 'Snooker'],
			],
		]);
	}

	public function getDynamicTranslations(): array
	{
		$result = [];
		foreach ($this->data['options'] as $option) {
			$result['type-' . $option] = $option;
		}
		return $result;
	}

	public function getSlotDataValidationRules(): array
	{
		return [
			"features.$this->id.name" => 'required|in:' . implode(',', $this->data['options']),
		];
	}

	public function getSlotDataValidationNiceNames(): array
	{
		$translations = Translation::retrieve(countryId: club()->country_id, featureId: $this->id);

		return [
			"features.$this->id.name" => $translations['slot-create-label'],
		];
	}

	/**
	 * @throws JsonException
	 */
	public function updateSlotData(Slot $slot, array $data = []): bool
	{
		$featureData = $data['features'][$this->id];
		$this->slots()->detach($slot);
		$this->slots()->attach($slot, [
			'data' => json_encode(['name' => $featureData['name']], JSON_THROW_ON_ERROR),
		]);

		return true;
	}

	public function slotQueryScope(Builder $query, array $data): Builder
	{
		if (!isset($data['features'][$this->id]['name']) || !$data['features'][$this->id]['name']) {
			return $query;
		}

		return $query->whereHas('features', function ($query) use ($data) {
			$query->where('features.id', $this->id);
			$query->where('feature_payload.data->name', $data['features'][$this->id]['name']);
		});
	}

	public function getWidgetData(array $data): array
	{
		if (!isset($data['gamesData'][$this->game_id])) {
			return [];
		}
		return [
			'types' => $data['gamesData'][$this->game_id]['gamesSlots']
				->map(function ($slot) {
					return json_decode(
						$slot->features->where('type', 'slot_has_type')->first()->pivot->data,
						true,
						512,
						JSON_THROW_ON_ERROR
					)['name'];
				})
				->unique()
				->values()
				->all(),
		];
	}
}
