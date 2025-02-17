<?php

namespace App\Models\Features;

use App\Custom\Timezone;
use App\Models\Club;
use App\Models\Feature;
use App\Models\Game;
use App\Models\ReservationNumber;
use App\Models\Setting;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Parental\HasParent;

class FullDayReservations extends Feature
{
	use HasParent;

	public static string $name = 'full_day_reservations';

	public array $conflictedFeatures = [FullDayReservations::class];

	public array $settings = [
		'global' => [],
		'club' => [
			'full_day_reservations_status' => [
				'type' => 'boolean',
				'default' => false,
				'adminOnlyEdit' => true,
				'validationRules' => [
					'value' => 'required|boolean',
				],
			],
		],
	];
	public bool $executablePublicly = false;

	public function updateReservationData(
		ReservationNumber $reservationNumber,
		array $data,
		bool $initializing = false
	): bool {
		$reservationSlots = $reservationNumber->numerable->reservationSlots;
		$club = club() ?? $reservationNumber->numerable->firstReservationSlot->slot->pricelist->club;
		$firstReservationSlot = $reservationSlots[0];
		$clubOpeningHours = $club->getOpeningHoursForDate($firstReservationSlot->start_at);

		$reservationStartAt = $firstReservationSlot->start_at->clone();
		$reservationEndAt = applyTimeToCarbon(
			$firstReservationSlot->start_at->clone(),
			$clubOpeningHours['club_end']
		);
		if ($reservationEndAt->lt($reservationStartAt)) {
			$reservationEndAt->addDay();
		}
		foreach ($reservationSlots as $reservationSlot) {
			$reservationSlot->update([
				'start_at' => Timezone::convertToLocal($reservationStartAt->format('Y-m-d H:i:s')),
				'end_at' => Timezone::convertFromLocal($reservationEndAt->format('Y-m-d H:i:s')),
			]);
		}

		return true;
	}

	public function prepareDataForAction(array $data): array
	{
		// If the game has the full_day_reservations feature and the club has the full_day_reservations_status setting enabled,
		// then set the duration of the reservation to one hour to correctly calculate the reservation amount
		// The price list of such games is given in the currency per person
		$reservationStartAt = now()->parse($data['start_at']);
		$clubOpeningHours = Club::getClub($data['club_id'])->getOpeningHoursForDate($reservationStartAt);
		$reservationEndAt = applyTimeToCarbon($reservationStartAt->clone(), $clubOpeningHours['club_end']);
		if ($reservationEndAt->lt($reservationStartAt)) {
			$reservationEndAt->addDay();
		}
		$data['start_at'] = $reservationStartAt->format('Y-m-d H:i:s');
		$data['duration'] = $reservationEndAt->diffInMinutes($reservationStartAt);

		return $data;
	}

	public function prepareDataForSlotSearch(array $data, bool $initializing = false): array
	{
		$reservationStartAt = now()->parse($data['start_at']);

		$data['start_at'] = $reservationStartAt->format('Y-m-d H:i:s');
		$data['duration'] = 60;

		return $data;
	}
}
