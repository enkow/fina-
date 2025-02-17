<?php

namespace App\Models\Features;

use App\Models\Feature;
use App\Models\Slot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use JsonException;
use Parental\HasParent;

class SlotHasActiveStatusPerWeekday extends Feature
{
	use HasParent;

	public static string $name = 'slot_has_active_status_per_weekday';

	public array $conflictedFeatures = [PersonAsSlot::class, SlotHasActiveStatusPerWeekday::class];

	public array $defaultTranslations = [
		'slot-not-active-validation-error' => 'Slot nie jest tego dnia aktywny',
		'slot-not-active-validation-error-value' => 'Slot ":value" nie jest tego dnia aktywny',
	];

	public function getSlotDataValidationRules(): array
	{
		return [
			"features.$this->id.active.0" => 'required|boolean',
			"features.$this->id.active.1" => 'required|boolean',
			"features.$this->id.active.2" => 'required|boolean',
			"features.$this->id.active.3" => 'required|boolean',
			"features.$this->id.active.4" => 'required|boolean',
			"features.$this->id.active.5" => 'required|boolean',
			"features.$this->id.active.6" => 'required|boolean',
		];
	}

	/**
	 * @throws JsonException
	 */
	public function updateSlotData(Slot $slot, array $data = []): bool
	{
		$featureData = $data['features'][$this->id];
		$featureDataJson = json_encode(
			[
				'active' => array_slice($featureData['active'], 0, 7),
			],
			JSON_THROW_ON_ERROR
		);

		$modifyMethod = $slot->features?->contains($this) ? 'updateExistingPivot' : 'attach';
		$this->slots()->$modifyMethod($slot, [
			'data' => $featureDataJson,
		]);

		return true;
	}

	public function slotQueryScope(Builder $query, array $data): Builder
	{
		return $query->whereHas('features', function ($query) use ($data) {
			$query->where('features.id', $this->id);
			$query->whereRaw(
				"JSON_EXTRACT(feature_payload.data->'$.active', '$[" .
					(weekDay($data['start_at']) - 1) .
					"]') = true"
			);
		});
	}
}
