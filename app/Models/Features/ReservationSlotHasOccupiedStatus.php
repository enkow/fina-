<?php

namespace App\Models\Features;

use App\Models\Feature;
use App\Models\ReservationNumber;
use App\Models\Translation;
use Parental\HasParent;

class ReservationSlotHasOccupiedStatus extends Feature
{
	use HasParent;

	public static string $name = 'reservation_slot_has_occupied_status';

	public array $defaultTranslations = [
		'slot-occupied-status' => 'Zmniejsz liczbę dostępnych miejsc online',
		'occupied-status-turn-off' => 'Zwolnij miejsce',
		'occupied-status-turn-on' => 'Zablokuj miejsce',
	];

	public array $conflictedFeatures = [ReservationSlotHasOccupiedStatus::class];

	public function getReservationDataValidationRules(): array
	{
		return [
			'occupied_status' => 'required|boolean',
		];
	}

	public function getReservationDataValidationNiceNames(): array
	{
        $club = club() ?? request()->route('club');
		$translations = Translation::retrieve(countryId: $club->country_id, featureId: $this->id);

		return [
			'occupied_status' => $translations['slot-occupied-status'],
		];
	}

	public function updateReservationData(
		ReservationNumber $reservationNumber,
		$data = [],
		bool $initializing = false
	): bool {
		if (!$reservationNumber->numerable->reservation->game->hasFeature($this->type)) {
			$reservationNumber->reservation->reservationSlots()->update([
				'occupied_status' => true,
			]);
		}

		return true;
	}
}
