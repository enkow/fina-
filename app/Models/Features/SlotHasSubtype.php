<?php

namespace App\Models\Features;

use App\Models\Feature;
use App\Models\Slot;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use JsonException;
use Parental\HasParent;

class SlotHasSubtype extends Feature
{
	use HasParent;

	public static string $name = 'slot_has_subtype';

	public array $defaultTranslations = [
		'slots-table-heading' => 'Rodzaj',
		'slot-create-label' => 'Typ stołu',
		'subtype' => 'Rodzaj',
		'mail-label' => 'Rodzaj',
		'widget-select-name' => 'Wielkość stołu',
		'widget-select-any' => 'Dowolna',
	];

	public array $requiredFeatures = [SlotHasType::class];

	public array $conflictedFeatures = [PersonAsSlot::class, SlotHasSubtype::class];

	public function insertDefaultData(): void
	{
		$this->update([
			'data' => [
				'options' => [
					[
						'type' => 'Bilard',
						'name' => '9ft',
					],
					[
						'type' => 'Bilard',
						'name' => '16ft',
					],
					[
						'type' => 'Snooker',
						'name' => '12ft',
					],
				],
			],
		]);
	}

	public function getDynamicTranslations(): array
	{
		$result = [];
		foreach ($this->data['options'] as $option) {
			$result['type-' . $option['name']] = $option['name'];
		}
		return $result;
	}

	public function getSlotDataValidationRules(): array
	{
		$slotHasTypeFeatureInstance = $this->game
			->features()
			->where('type', 'slot_has_type')
			->first();

		return [
			"features.$this->id.name" =>
				'required|in:' .
				implode(
					',',
					array_column(
						array_filter($this->data['options'], static function ($var) use (
							$slotHasTypeFeatureInstance
						) {
							return $var['type'] ===
								request()?->get('features')[$slotHasTypeFeatureInstance->id]['name'];
						}),
						'name'
					)
				),
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
		if (!isset($data['features'][$this->id]['name']) || $data['features'][$this->id]['name'] === null) {
			return $query;
		}

		return $query->whereHas('features', function ($query) use ($data) {
			$query->where('features.id', $this->id);
			$query->where('feature_payload.data->name', $data['features'][$this->id]['name']);
		});
	}

	public function getWidgetData(array $data): array
	{
		return [
			'subtypes' => $data['gamesData'][$this->game_id]['gamesSlots']
				->map(function ($slot) {
					$entry = [];
					$entry['type'] = json_decode(
						$slot->features->where('type', 'slot_has_type')->first()->pivot->data,
						true,
						512,
						JSON_THROW_ON_ERROR
					)['name'];
					$entry['subtype'] = json_decode(
						$slot->features->where('type', 'slot_has_subtype')->first()?->pivot?->data,
						true,
						512,
						JSON_THROW_ON_ERROR
					)['name'];
					return $entry;
				})
				->unique()
				->values()
				->all(),
		];
	}
}
