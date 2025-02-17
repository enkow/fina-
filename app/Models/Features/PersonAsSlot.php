<?php

namespace App\Models\Features;

use App\Custom\Timezone;
use App\Jobs\AddRecordToFeaturePayloadTable;
use App\Models\Feature;
use App\Models\FeaturePayloadAction;
use App\Models\Game;
use App\Models\Reservation;
use App\Models\ReservationNumber;
use App\Models\ReservationSlot;
use App\Models\Setting;
use App\Models\Slot;
use App\Models\Translation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JsonException;
use Laravel\Octane\Exceptions\DdException;
use Parental\HasParent;

class PersonAsSlot extends Feature
{
	use HasParent;

	public static string $name = 'person_as_slot';
	public bool $preventReservationProcessing = true;

	public array $conflictedFeatures = [
		BookSingularSlotByCapacity::class,
		HasSlotPeopleLimitSettings::class,
		HasVisibleCalendarSlotsQuantitySetting::class,
		PricePerPerson::class,
		SlotHasLounge::class,
		SlotHasConvenience::class,
		SlotHasType::class,
		SlotHasSubtype::class,
		PersonAsSlot::class,
	];

	public array $defaultTranslations = [
		'slots-quantity' => 'Ilość osób',
		'slots-quantity-default-setting-title' => 'Domyślna ilość osób na rezerwację stolika',
		'slots-quantity-default-setting-description' =>
			'Ustawiając ten parametr określisz domyślną liczbę osób (będzie ona możliwa do edycji) , która wyświetli się klientowi online podczas wyboru szczegółów rezerwacji.',
		'slots-quantity-default-setting-label' => 'Aktualna wartość (osób)',
		'quantity-reservation-view-label' => 'Zajęte miejsca',
		'capacity' => 'Pojemność',
	];

	public array $settings = [
		'global' => [],
		'club' => [
			//                'person_as_slot_quantity_default' => [
			//                    'type'            => 'integer',
			//                    'default'         => 1,
			//                    'validationRules' => [
			//                        'value' => 'nullable|integer|min:1|max:200',
			//                    ],
			//                ],
		],
	];
	public bool $isTaggableIfGameReservationExist = false;

	public function getSlotDataValidationNiceNames(): array
	{
		$translations = Translation::retrieve(countryId: club()->country_id, featureId: $this->id);

		return [
			"features.$this->id.capacity.*" => $translations['capacity'],
		];
	}

	public function getSlotDataValidationRules(): array
	{
		return [
			"features.$this->id.capacity.*" => 'required|numeric|min:0|max:500',
		];
	}

	/**
	 * @throws JsonException
	 */
	public function updateSlotData(Slot $slot, array $data = []): bool
	{
		$featureData = $data['features'][$this->id];
		//In the database, we never remove slots in a game with this feature.
		//Instead, we set them to be inactive on specific or all days of the week.
		//If there are not enough slots created in the database, we create additional slots below.
		$childrenSlots = $slot->childrenSlots;
		$childrenSlotsInsertArray = array_fill(
			0,
			max(0, max($featureData['capacity']) - count($childrenSlots)),
			[
				'slot_id' => $slot->id,
				'active' => 1,
				'pricelist_id' => $slot->pricelist_id,
			]
		);
		Slot::insert($childrenSlotsInsertArray);

		//Assign <person_as_slot> feature to each created slot
		$featureSlots = DB::table('feature_payload')
			->where('feature_id', $this->id)
			->where('describable_type', Slot::class)
			->whereIn('describable_id', $slot->childrenSlots()->pluck('slots.id'))
			->pluck('describable_id')
			->toArray();
		$featureSlotSlotIdsToInsert = Slot::where('slot_id', $slot->id)
			->pluck('id')
			->toArray();
		$insertArray = array_map(function ($item) {
			return [
				'describable_type' => Slot::class,
				'describable_id' => $item,
				'feature_id' => $this->id,
				'data' => json_encode(
					[
						'active' => array_fill(0, 7, false),
					],
					JSON_THROW_ON_ERROR
				),
			];
		}, array_diff($featureSlotSlotIdsToInsert, $featureSlots));

        clock()->event('Adding to queue')->color('purple')->begin();
        AddRecordToFeaturePayloadTable::dispatch($insertArray)->onQueue('high');
        clock()->event('Adding to queue')->end();
		//Set the "active" status for each slot for each day of the week,
		//so that the total number of slots in specific weeks matches the values entered by the user.
		$slot->unsetRelation('childrenSlots');
		$childrenSlots = $slot->childrenSlots;
		$featureSlotsDataArray = array_fill_keys(array_column($childrenSlots->toArray(), 'id'), [
			'active' => array_fill(0, 7, false),
		]);

		foreach ($featureSlotsDataArray as $slotId => $slotData) {
			foreach ($featureData['capacity'] as $key => $value) {
				if ($value) {
					$featureSlotsDataArray[$slotId]['active'][$key - 1] = true;
					$featureData['capacity'][$key]--;
				}
			}
		}

		//We set each repeating array of "active" statuses with one query
		$updateArray = [];
		foreach ($featureSlotsDataArray as $slotId => $slotData) {
			$key = md5(json_encode($slotData, JSON_THROW_ON_ERROR));
			if (!array_key_exists($key, $updateArray)) {
				$updateArray[$key] = [
					'slotIds' => [],
					'data' => $slotData,
				];
			}
			$updateArray[md5(json_encode($slotData, JSON_THROW_ON_ERROR))]['slotIds'][] = $slotId;
		}

		foreach ($updateArray as $updateEntry) {
			DB::table('feature_payload')
				->where('describable_type', Slot::class)
				->whereIn('describable_id', $updateEntry['slotIds'])
				->where('feature_id', $this->id)
				->update([
					'data' => json_encode($updateEntry['data'], JSON_THROW_ON_ERROR),
				]);
		}

		return true;
	}

	public function getReservationDataValidationNiceNames(): array
	{
        $club = club() ?? request()->route('club');
		$translations = Translation::retrieve(countryId: $club->country_id, featureId: $this->id);

		return [
			"features.$this->id.persons_count" => $translations['slots-quantity'],
		];
	}

	public function prepareDataForAction(array $data): array
	{
		return $data;
	}

	/**
	 * @throws DdException
	 * @throws JsonException
	 */
	public function getReservationDataValidationRules(array $data = null): array
	{
		$currentOccupiedSlotsCount = 0;
		if (isset($data['reservation_number_id'])) {
			$reservationNumber = ReservationNumber::find($data['reservation_number_id']);
			$currentOccupiedSlotsCount = $reservationNumber->numerable
				->reservationSlots()
				->where('occupied_status', true)
				->count();
		}
		$data['parent_slot_id'] = $data['features'][$this->id]['new_parent_slot_id'];
		$data['active'] = true;
		$data['vacant'] = true;
		$vacantSlots = Slot::getAvailable($data);

		$game = Game::getCached()
			->where('id', $data['game_id'])
			->first();
		if (!$data['occupied_status'] && $game->hasFeature('reservation_slot_has_occupied_status')) {
			return [];
		}

		$capacity = 0;
        $arr = [];
		foreach ($vacantSlots as $slot) {
            $arr[] = json_decode(
                $slot->features->where('type', 'person_as_slot')->first()?->pivot?->data ??
                json_encode([true, true, true, true, true, true, true], JSON_THROW_ON_ERROR),
                true,
                512,
                JSON_THROW_ON_ERROR
            )['active'];
			if (
				json_decode(
					$slot->features->where('type', 'person_as_slot')->first()?->pivot?->data ??
						json_encode([true, true, true, true, true, true, true], JSON_THROW_ON_ERROR),
					true,
					512,
					JSON_THROW_ON_ERROR
				)['active'][weekDay($data['start_at']) - 1] ?? false
			) {
				$capacity++;
			}
		}
//        dd($arr, weekDay($data['start_at']), $capacity, $currentOccupiedSlotsCount);

		return [
			"features.$this->id.persons_count" =>
				'required|numeric|min:1|max:' . ($capacity + $currentOccupiedSlotsCount),
		];
	}

	/**
	 * @throws JsonException
	 */
	public static function getParentSlotCapacity(
		Slot $parentSlot,
		string|Carbon $date,
		Collection $slots = null
	): int {
		$capacity = 0;
		$slotsCollection =
			$slots ??
			$parentSlot
				->childrenSlots()
				->with('features')
				->select('slots.id')
				->get();
		foreach ($slotsCollection as $slot) {
			if (
                $slot->features->where('type', 'person_as_slot')->first() !== null &&
				json_decode(
					$slot->features->where('type', 'person_as_slot')->first()->pivot->data,
					true,
					512,
					JSON_THROW_ON_ERROR
				)['active'][weekDay($date) - 1]
			) {
				$capacity++;
			}
		}

		return $capacity;
	}

	public function updateReservationData(
		ReservationNumber $reservationNumber,
		array $data,
		bool $initializing = false
	): bool {
		$featureData = $data['features'][$this->id];
		$reservation = $reservationNumber->numerable;
		$firstReservationSlot = $reservation->firstReservationSlot;
		$pricelist = $firstReservationSlot->slot->pricelist;
		$slotsToCreate = (int) $featureData['persons_count'];
		$reservationSlotSetIds = $firstReservationSlot->sets()->pluck('sets.id');
		$parentSlotId =
			$featureData['new_parent_slot_id'] ??
			($data['parent_slot_id'] ?? $reservation->firstReservationSlot->slot->slot_id);
		$firstReservationSlotArray = Arr::except($firstReservationSlot->toArray(), [
			'id',
			'reservation',
			'slot',
			'reservation_number',
			'created_at',
			'updated_at',
			'discount_code',
			'special_offer',
			'reservation_type',
			'sets',
			'features',
            'timer_entries'
		]);
		$reservation->reservationSlots()->delete();
		// if the game has the <person_as_slot> feature, then the first slot reservation reflects all others
		$slotsIdsToAttach = [];
		// If the first slot is occupied, all subsequent slots must be occupied.
		// In this case, we cannot reserve more slots than are physically available
		if ($firstReservationSlot->occupied_status || $data['occupied_status']) {
			$vacantSlots = Slot::where('pricelist_id', $pricelist->id)
				->when($reservation->game->hasFeature('slot_has_parent'), function ($query) use (
					$parentSlotId
				) {
					$query->where('slot_id', $parentSlotId);
				})
				->vacant(
					$pricelist->club_id,
					$pricelist->game_id,
					$firstReservationSlot->start_at,
					$firstReservationSlot->end_at
				)
				->take($slotsToCreate)
				->pluck('id')
				->toArray();
			if (count($vacantSlots) === $slotsToCreate) {
				$slotsIdsToAttach = $vacantSlots;
			}
		}
		// If the first slot is not occupied, neither will any subsequent slots.
		// In this case, we do not have to worry about the physical capacity of the rooms.
		// We reserve slots as many times as necessary so that the final number of people
		// agrees with the value provided in the form.
		else {
			while (count($slotsIdsToAttach) !== $slotsToCreate) {
				$slotsIds = Slot::where('pricelist_id', $pricelist->id)
					->when($reservation->game->hasFeature('slot_has_parent'), function ($query) use (
						$parentSlotId
					) {
						$query->where('slot_id', $parentSlotId);
					})
					->take($slotsToCreate - count($slotsIdsToAttach))
					->pluck('id')
					->toArray();
				$slotsIdsToAttach = array_merge($slotsIds, $slotsIdsToAttach);
			}
		}
		foreach (['start_at', 'end_at'] as $keyToDateParse) {
			$firstReservationSlotArray[$keyToDateParse] = now()
				->parse($firstReservationSlotArray[$keyToDateParse])
				->format('Y-m-d H:i:s');
		}
		$insertData = [];
		$reservationOrder = 1;
        $data['price'] *= 100;
		foreach ($slotsIdsToAttach as $slotIdToAttach) {
			if ($data['price'] ?? false) {
				$customPrice = $data['price'] ?? null;
				$customPrice = $customPrice ? floatval(str_replace(',', '.', $customPrice)) * 1 : null;
				$priceToThisSlot = round(
					($customPrice / ($featureData['persons_count'] ?? ($data['slots_count'] ?? 1))) *
						($reservationOrder ?? 1)
				);
				$priceToLastSlot = round(
					($customPrice / ($featureData['persons_count'] ?? ($data['slots_count'] ?? 1))) *
						(($reservationOrder ?? 1) - 1)
				);
				$reservation->update([
					'price' => $customPrice,
				]);
				$customPrice = $customPrice ? $priceToThisSlot - $priceToLastSlot : null;
			}
			$insertData[] = array_merge($firstReservationSlotArray, [
				'reservation_id' => $reservation->id,
				'slot_id' => $slotIdToAttach,
				'price' => $customPrice ?? $firstReservationSlotArray['price'],
				'final_price' => $customPrice ?? $firstReservationSlotArray['price'],
				'created_at' => now(),
			]);
			$reservationOrder++;
		}
		ReservationSlot::insert($insertData);

		DB::table('reservation_slot_set')
			->where('reservation_slot_id', $firstReservationSlot->id)
			->whereIn('set_id', $reservationSlotSetIds)
			->update([
				'reservation_slot_id' => Reservation::find($reservation?->id)->firstReservationSlot?->id,
			]);

		return true;
	}

	public function insertDefaultData(): void
	{
		$this->update([
			'data' => [
				'slots_limit_setting' => true,
				'slots_default_setting' => true,
				'parent_has_capacity_by_week_day' => false,
			],
		]);
	}

	public function slotQueryScope(Builder $query, array $data): Builder
	{
		return $query->where(function ($query) use ($data) {
			$query
				->whereHas('features', function ($query) use ($data) {
					$query->where('features.id', $this->id);
					$query->whereRaw(
						"JSON_EXTRACT(feature_payload.data->'$.active', '$[" .
							(weekDay($data['start_at']) - 1) .
							"]') = true"
					);
				})
				->when($data['include_parent_slots'] ?? false, function ($query) {
					$query->orWhereNull('slot_id');
				});
		});
	}

	/**
	 * @throws JsonException
	 */
	public function getWidgetData(array $data): array
	{
		$club = $data['club'];
		$parentSlots = $this->game
			->slots()
			->whereHas('pricelist', function ($query) use ($club) {
				$query->where('club_id', $club->id);
			})
			->whereNull('slot_id')
			->with('childrenSlots', 'childrenSlots.features')
			->get();

		$parentSlotsCapacities = [];

		foreach ($parentSlots as $parentSlot) {
			$parentSlotsCapacities[$parentSlot->id] = [];
			for ($i = 1; $i <= 7; $i++) {
				$parentSlotsCapacities[$parentSlot->id][$i - 1] = self::getParentSlotCapacity(
					$parentSlot,
					now()->next($i % 7),
					$parentSlot->childrenSlots
				);
			}
		}

		return [
			'parent_slots_capacities' => $parentSlotsCapacities,
		];
	}
}
