<?php

namespace App\Models\Features;

use App\Http\Resources\SlotResource;
use App\Models\Feature;
use App\Models\Game;
use App\Models\Pricelist;
use App\Models\Slot;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use JsonException;
use Parental\HasParent;

class BookSingularSlotByCapacity extends Feature
{
	use HasParent;

	public static string $name = 'book_singular_slot_by_capacity';

	public array $defaultTranslations = [
		'slot' => 'Stolik',
		'slots' => 'Stoliki',
		'slot-create-name-label' => 'Stolik nr:',
		'slot-create-capacity-label' => 'Ilość miejsc przy stole',
		'capacity-short' => 'Poj.',
		'capacity-value-postfix' => 'osób',
		'capacity-value-postfix-short' => '-os',
		'free-table-numbers' => 'Numery wolnych stolików',
		'widget-capacity-label' => 'Ilość osób',
		'widget-select-any' => 'Dowolna',
	];

	public array $conflictedFeatures = [
		PersonAsSlot::class,
		BookSingularSlotByCapacity::class,
		PricePerPerson::class,
	];

	public function getSlotDataValidationRules(): array
	{
		return [
			"features.$this->id.capacity" => 'required|numeric|min:1|max:100',
		];
	}

	public function getSlotDataValidationNiceNames(): array
	{
		$translations = Translation::retrieve(countryId: club()->country_id, featureId: $this->id);

		return [
			"features.$this->id.capacity" => $translations['slot-create-capacity-label'],
		];
	}

	public function getReservationDataValidationNiceNames(): array
	{
        $gameTranslations = Translation::retrieveGameTranslations($this->game_id, countryId: (request()->route('club') ?? club())->country_id);

		return [
            "slot_id" => $gameTranslations['slot-name'],
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
			'data' => json_encode(['capacity' => $featureData['capacity']], JSON_THROW_ON_ERROR),
		]);

		return true;
	}

	/**
	 * @throws JsonException
	 */
	public function calculateFeatureReservationPrice(
		int &$basePrice,
		int &$finalPrice,
		array $data,
		int $clubId = null
	): void {
		// do not update price if user entered custom price
		if (!($data['custom_price'] ?? false)) {
			$slot =
				$data['slot'] ??
				Slot::where('id', $data['slot_id'] ?? ($data['slot_ids'][0] ?? null))->first();
			if ($slot) {
				$slotFeatureData = $slot->features->where('type', $this->type)->first()->pivot->data;
				$slotFeatureDataArray = json_decode($slotFeatureData, true, 512, JSON_THROW_ON_ERROR);
				$capacity = $slotFeatureDataArray['capacity'];
				$basePrice *= $capacity;
				$finalPrice *= $capacity;
			}
		}
	}

	public function slotQueryScope(Builder $query, array $data): Builder
	{
		if (!isset($data['features'][$this->id]['capacity'])) {
			return $query;
		}

		return $query->whereHas('features', function ($query) use ($data) {
			$query->where('features.id', $this->id);
			$query->where('feature_payload.data->capacity', (int) $data['features'][$this->id]['capacity']);
		});
	}

	public function getWidgetData(array $data): array
	{
		$game = $this->game;
		$club = $data['club'];
        $pricelistIds = Cache::remember('game:'.$game->id.':club:'.$club->id.':pricelist_ids', 30, function () use ($game, $club) {
            return Pricelist::where('game_id', $game->id)
                ->where('club_id', $club->id)
                ->pluck('id')
                ->toArray();
        });
		$slots = $club
			->slots()
			->whereIn('pricelist_id', $pricelistIds)
			->whereHas('features', function ($query) {
				$query->where('type', 'book_singular_slot_by_capacity');
			})
			->when($game->hasFeature('slot_has_parent'), fn($q) => $q->whereNotNull('slot_id'))
			->with([
				'features' => function ($query) {
					$query->whereIn('type', [
						'book_singular_slot_by_capacity',
						'slot_has_active_status_per_weekday',
					]);
				},
			])
			->where('active', true)
			->get();
		$slotsArray = [];
		foreach ($slots as $slot) {
			$slotsArray[$slot->id] = [
				'parent_slot_id' => $slot->slot_id,
			];

			$bookSingularSlotByCapacityFeature = $slot->features
				->where('type', 'book_singular_slot_by_capacity')
				->first();
			if ($bookSingularSlotByCapacityFeature) {
				$bookSingularSlotByCapacityFeaturePivotData = $bookSingularSlotByCapacityFeature->pivot->data;
				$bookSingularSlotByCapacityFeaturePivotArray = json_decode(
					$bookSingularSlotByCapacityFeaturePivotData,
					true,
					512,
					JSON_THROW_ON_ERROR
				);
				$slotsArray[$slot->id]['capacity'] = $bookSingularSlotByCapacityFeaturePivotArray['capacity'];
			}

			$activePerWeekdaySlotFeature = $slot->features
				->where('type', 'slot_has_active_status_per_weekday')
				->first();
			if ($activePerWeekdaySlotFeature) {
				$activePerWeekdaySlotFeaturePivotData = $activePerWeekdaySlotFeature->pivot->data;
				$activePerWeekdaySlotFeaturePivotArray = json_decode(
					$activePerWeekdaySlotFeaturePivotData,
					true,
					512,
					JSON_THROW_ON_ERROR
				);
				$slotsArray[$slot->id]['active'] = $activePerWeekdaySlotFeaturePivotArray['active'];
			}
		}
		return [
			'slots_data' => $slotsArray,
		];
	}

	public static function prepareSlotVariables($data)
	{
		$game = Game::getCached()->find($data['game_id'] ?? 1);
		if (!$game->hasFeature('person_as_slot')) {
			if (count($data['slot_ids'] ?? []) > (int) $data['slots_count']) {
				$data['slots_count'] = count($data['slot_ids']);
			}
			if (count($data['slot_ids'] ?? []) === 1) {
				$data['slot_id'] = $data['slot_ids'][0];
				//                $data['slot_ids'] = [null];
			}
		}
		return $data;
	}
}
