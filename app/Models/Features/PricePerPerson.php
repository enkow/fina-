<?php

namespace App\Models\Features;

use App\Custom\Timezone;
use App\Models\Feature;
use App\Models\Pricelist;
use App\Models\Reservation;
use App\Models\ReservationNumber;
use App\Models\ReservationSlot;
use App\Models\Setting;
use App\Models\Slot;
use App\Models\Translation;
use Illuminate\Support\Facades\DB;
use Parental\HasParent;

class PricePerPerson extends Feature
{
	use HasParent;

	public static string $name = 'price_per_person';

	public array $conflictedFeatures = [PricePerPerson::class, PersonAsSlot::class];

	public array $defaultTranslations = [
		'pricelist-title' => 'Cena za osobę',
		'pricelist-description' => 'Opis ustawienia "Cena za osobę"',
		'person-count' => 'Liczba osób',
		'person-short' => 'os',
		'persons' => 'osób',
		'reservation-form-person-count-name' => 'Ilość osób -',
		'calendar-cost-included' => 'Cena za buty wliczona',
		'calendar-cost-label' => 'Cena za obuwie',
	];
	public array $settings = [
		'global' => [],
		'club' => [
			'price_per_person_type' => [
				'type' => 'integer',
				'default' => 0, // 0 - none, 1 - price addon, 2 - price base
				'validationRules' => [
					'value' => 'required|numeric|min:0|max:100000',
				],
				'adminOnlyEdit' => true,
			],
			'price_per_person' => [
				'type' => 'integer',
				'default' => 100,
				'validationRules' => [
					'value' => 'required|numeric|min:0|max:100000',
				],
			],
		],
	];
	public bool $isTaggableIfGameReservationExist = false;

	public function updateData($data): void
	{
		$this->update(['data' => $data]);
	}

	public function insertDefaultData(): void
	{
		$this->update([
			'data' => [
				'icon' => null,
			],
		]);
	}

	public function getPricePerPersonType($clubId = null)
	{
		return Setting::getClubGameSetting($clubId ?? clubId(), 'price_per_person_type', $this->game_id)[
			'value'
		] ?? 0;
	}

	public function getSlotDataValidationNiceNames(): array
	{
		if ($this->getPricePerPersonType() === 0) {
			return [];
		}
		$translations = Translation::retrieve(countryId: club()->country_id, featureId: $this->id);

		return [
			"features.$this->id.person_count" => $translations['person-count'],
		];
	}

	public function prepareReservationDataForValidation($data = []): array
	{
		if ($this->getPricePerPersonType(clubId() ?? request()->route('club')->id) !== 0) {
			//the club did not have the price_per_person feature enabled previously
			if (!isset($data['person_count'])) {
				$data['person_count'] = 0;
			}
			$data['person_count'] =
				is_numeric($data['person_count']) &&
				(string) ((int) $data['person_count']) === $data['person_count']
					? (int) $data['person_count']
					: $data['person_count'];
		}
		return $data;
	}

	public function getReservationDataValidationRules(): array
	{
		$clubId = clubId() ?? request()->route('club')->id;
		if ($this->getPricePerPersonType($clubId) === 0) {
			return [];
		}
		$personCountValidationRules = ['required', 'integer', 'min:0'];
		if ($this->game->hasFeature('has_slot_people_limit_settings')) {
			$personCountValidationRules[] =
				'max:' .
				(Setting::getClubGameSetting($clubId, 'slot_people_max_limit', $this->game_id)['value'] ??
					100) *
					request()->all()['slots_count'];
		}

		return [
			"features.$this->id.person_count" => $personCountValidationRules,
		];
	}

	public function getReservationDataValidationNiceNames(): array
	{
		$club = club() ?? request()->route('club');
		if ($this->getPricePerPersonType($club->id) === 0) {
			return [];
		}
		$translations = Translation::retrieve(countryId: $club->country_id, featureId: $this->id);

		return [
			"features.$this->id.person_count" => $translations['person-count'],
		];
	}

	public function updateReservationData(
		ReservationNumber $reservationNumber,
		array $data,
		bool $initializing = false,
		int|null $reservationOrder = 1
	): bool {
        $numerables = $reservationNumber->numerable;
		$featureData = $data['features'][$this->id];
		$reservationOrder = 1;
		$reservationSlots = $data['reservationSlots'] ?? $numerables->reservationSlots;
		$reservationSlot = $data['reservationSlot'] ?? $numerables->firstReservationSlot;
		$dataChangesInsertArray = [];

		$slot = Slot::getSlot($reservationSlot->slot_id);
		if ($this->getPricePerPersonType(Pricelist::getPricelist($slot->pricelist_id)->club_id) === 0) {
			return true;
		}

		// If we are creating reservations, it is possible that it has many slots reservations that we need to take into account when allocating the number of people.
		// User can only edit reservations for a specific track, so the number of reservations for tracks in the collective booking must be omitted here.
		$reservationSlotsCount =
            ($data['creation'] ?? false) || $reservationSlots[0]->created_at->diffInSeconds(now('UTC')) < 3 ? count($reservationSlots) : 1;
		foreach ($reservationSlots as $reservationSlot) {
			$oldPersonCountValue =
				json_decode(
					$this->reservationSlots()->find($reservationSlot)->pivot->data ?? '[]',
					true,
					512,
					JSON_THROW_ON_ERROR
				)['person_count'] ?? 0;
			$this->reservationSlots()->detach($reservationSlot);
			$personCount = $featureData['person_count'];

			$reservationOrder = $reservationSlotsCount - $reservationOrder + 1;
			$personCount =
				floor(($personCount / $reservationSlotsCount) * $reservationOrder) -
				floor(($personCount / $reservationSlotsCount) * ($reservationOrder - 1));
			$this->reservationSlots()->attach($reservationSlot, [
				'data' => json_encode(['person_count' => $personCount], JSON_THROW_ON_ERROR),
			]);
			// save changes to present them in Reservation/ReservationSlot
			if ((int) $oldPersonCountValue !== (int) $personCount) {
				$dataChangesInsertArray[] = [
					'changable_type' => ReservationSlot::class,
					'changable_id' => $reservationSlot->id,
					'triggerer_id' => auth()->user()?->id,
					'old' => json_encode(
						[
							'features.' . $this->id . '.person_count' => $oldPersonCountValue,
						],
						JSON_THROW_ON_ERROR
					),
					'new' => json_encode(
						[
							'features.' . $this->id . '.person_count' => $personCount,
						],
						JSON_THROW_ON_ERROR
					),
					'created_at' => Timezone::convertFromLocal(now()),
					'updated_at' => Timezone::convertFromLocal(now()),
				];
			}
		}
		DB::table('data_changes')->insert($dataChangesInsertArray);

		return true;
	}

	public function calculateFeatureReservationPrice(
		int &$basePrice,
		int &$finalPrice,
		array $data,
		int $clubId = null,
		int|null $reservationOrder = 1
	): void {
		$reservationOrder = $reservationOrder ?? 1;

		$pricePerPersonType =
			Setting::getClubGameSetting(clubId() ?? $clubId, 'price_per_person_type', $this->game_id)[
				'value'
			] ?? 1;

		$slotsCount = $data['slots_count'] ?? 1;
		$reservationOrder = $slotsCount - $reservationOrder + 1;
		$personCount =
			floor(($data['features'][$this->id]['person_count'] / $slotsCount) * $reservationOrder) -
			floor(($data['features'][$this->id]['person_count'] / $slotsCount) * ($reservationOrder - 1));

		// If $pricePerPersonType === 1, the per person amount is just an addition to the base amount. Otherwise, we calculate the base booking amount based on the number of people.
		if ($pricePerPersonType === 1) {
			// if setting not exists method is executed from seeder - place default 100 value
			$pricePerPersonValue =
				Setting::getClubGameSetting(clubId() ?? $clubId, 'price_per_person', $this->game_id)[
					'value'
				] ?? 100;

			$finalPrice += $personCount * $pricePerPersonValue;
		} elseif ($pricePerPersonType === 2) {
			$basePrice *= $personCount;
			$finalPrice *= $personCount;
		}
	}
}
