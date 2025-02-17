<?php

namespace App\Models\Features;

use App\Models\Feature;
use App\Models\Game;
use App\Models\ReservationNumber;
use App\Models\Setting;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Parental\HasParent;

class FixedReservationDuration extends Feature
{
	use HasParent;

	public static string $name = 'fixed_reservation_duration';

	public array $conflictedFeatures = [FixedReservationDuration::class];

	public array $settings = [
		'global' => [],
		'club' => [
			'fixed_reservation_duration_status' => [
				'type' => 'boolean',
				'default' => false,
				'adminOnlyEdit' => false,
				'validationRules' => [
					'value' => 'required|boolean|required',
				],
			],
			'fixed_reservation_duration_value' => [
				'type' => 'integer',
				'default' => 2,
				'adminOnlyEdit' => false,
				'validationRules' => [
					'value' => 'gt:0|max:24|required|integer',
				],
			],
		],
	];

	public array $defaultTranslations = [
		'group-setting-title' => 'Stała długość rezerwacji',
		'status-setting-description' =>
			'Włączając to ustawienie sprawisz, że wszystkie wykonane rezerwacje będą miały jednakowy czas trwania. Pojawi się wtedy możliwość wskazania ile dokładnie godzin będzie trwała każda z nich.',
		'value-setting-description' =>
			'Wprowadzona poniżej liczba godzin będzie długością każdej stworzonej od tej pory rezerwacji. Jeśli chcesz, żeby każda rezerwacja zajmowała na kalendarzu cały dzień wprowadź w poniższym polu liczbę 24.',
		'value-setting-label' => 'Obecna wartość',
	];

	private function getRelatedFullDayReservationsFeature()
	{
		return Game::getCached()
			->find($this->game_id)
			->features->where('type', 'full_day_reservations')
			->first();
	}

	public function updateReservationData(
		ReservationNumber $reservationNumber,
		array $data,
		bool $initializing = false
	): bool {
		$reservationSlots = $reservationNumber->numerable->reservationSlots;
		$reservation = $reservationNumber->numerable->reservation;
		$club = club() ?? $reservationNumber->numerable->firstReservationSlot->slot->pricelist->club;
		$fixedReservationDurationStatus =
			Setting::getClubGameSetting(
				$club->id,
				'fixed_reservation_duration_status',
				$reservation->game->id
			)['value'] ?? false;
		$fixedReservationDurationValue =
			Setting::getClubGameSetting(
				$club->id,
				'fixed_reservation_duration_value',
				$reservation->game->id
			)['value'] ?? 0;
		if (!$fixedReservationDurationStatus || $fixedReservationDurationValue === 0) {
			return true;
		}
		if ($fixedReservationDurationValue === 24) {
			return $this->getRelatedFullDayReservationsFeature()->updateReservationData(
				$reservationNumber,
				$data,
				$initializing
			);
		}
		foreach ($reservationSlots as $reservationSlot) {
			$reservationSlot->update([
				'end_at' => $reservationSlot->start_at->clone()->addHours($fixedReservationDurationValue),
			]);
		}

		return true;
	}

	public function prepareDataForAction(array $data, bool $slotSearch = false): array
	{
		$value = Cache::remember(
			'club:' . $data['club_id'] . 'game:' . $data['game_id'] . ':fixed_reservation_duration_value',
			30,
			function () use ($data) {
				return Setting::getClubGameSetting(
					$data['club_id'],
					'fixed_reservation_duration_value',
					$data['game_id']
				)['value'] ?? 0;
			}
		);
		$status = Cache::remember(
			'club:' . $data['club_id'] . 'game:' . $data['game_id'] . ':fixed_reservation_duration_status',
			30,
			function () use ($data) {
				return Setting::getClubGameSetting(
					$data['club_id'],
					'fixed_reservation_duration_status',
					$data['game_id']
				)['value'] ?? 0;
			}
		);
		if ($status && (int) $value === 24) {
			if ($slotSearch) {
				return $this->getRelatedFullDayReservationsFeature()->prepareDataForSlotSearch($data);
			}

			return $this->getRelatedFullDayReservationsFeature()->prepareDataForAction($data);
		}
		if ($status && $value) {
			$data['duration'] = $value * 60;
		}
		unset($status, $value);
		return $data;
	}

	public function prepareDataForSlotSearch($data): array
	{
		return $this->prepareDataForAction($data, true);
	}

	public function calculateFeatureReservationPrice(
		int &$basePrice,
		int &$finalPrice,
		array $data,
		int $clubId = null
	): void {
		// If the game has the fixed_reservation_duration feature assigned to it and the club has a fixed reservation duration,
		// divide the prices by a fixed number of hours.
		// In this case, we calculate the amount of the order without taking into account the duration.
		//        $slot = $data['slot'] ?? Slot::where('id', $data['slot_id'])->first();
		//        $game = $slot->pricelist->game;
		//        $basePrice /= max(Setting::getClubGameSetting($clubId, 'fixed_reservation_duration', $game->id)['value'],1);
		//        $finalPrice /= max(Setting::getClubGameSetting($clubId, 'fixed_reservation_duration', $game->id)['value'],1);
	}
}
