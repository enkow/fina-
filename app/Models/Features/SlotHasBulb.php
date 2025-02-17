<?php

namespace App\Models\Features;

use App\Custom\Timezone;
use App\Enums\BulbReason;
use App\Enums\BulbStatus;
use App\Events\CalendarDataChanged;
use App\Jobs\BulbRunAction;
use App\Models\BulbAction;
use App\Models\Club;
use App\Models\Feature;
use App\Models\Game;
use App\Models\Reservation;
use App\Models\ReservationNumber;
use App\Models\ReservationSlot;
use App\Models\Setting;
use App\Models\Slot;
use Carbon\Carbon;
use Parental\HasParent;

class SlotHasBulb extends Feature
{
	use HasParent;

	public static string $name = 'slot_has_bulb';

	public array $defaultTranslations = [
		'bulb-name' => 'Żarówka',
		'bulb-status' => 'Żarówka',
	];

	public array $conflictedFeatures = [PersonAsSlot::class];

	public array $settings = [
		'global' => [],
		'club' => [
			'bulb_status' => [
				'type' => 'boolean',
				'default' => false,
				'adminOnlyEdit' => true,
				'validationRules' => [
					'value' => 'required|boolean',
				],
			],
		],
	];

	/**
	 * @throws JsonException
	 */
	public function updateSlotData(Slot $slot, array $data = []): bool
	{
		if (isset($data['features'][$this->id])) {
			$featuresData = $data['features'][$this->id];
			$this->slots()->detach($slot);
			$this->slots()->attach($slot, [
				'data' => json_encode(
					[
						'name' => $featuresData['name'],
					],
					JSON_THROW_ON_ERROR
				),
			]);

			$result = $slot->changeBulbStatus(BulbStatus::OFFLINE, BulbReason::AUTOSYNC);
			BulbRunAction::dispatch($result);
		}

		return true;
	}

	public function updateReservationData(ReservationNumber $reservationNumber, array $data): bool
	{
		$reservation = $data['reservation'] ?? $reservationNumber->numerable->reservation;
		$bulbType = $data['features'][$this->id]['type'] ?? null;
        $clubId = request()->route('club')->id ?? clubId();

		if (!isset($data['start_at'])) {
			$data['start_at'] = $reservation->firstReservationSlot->getOriginal('start_at');
		}
		if (!isset($data['duration'])) {
			$data['duration'] = $reservation->firstReservationSlot->duration;
		}

		self::clearReservationBulbActions($reservationNumber->numerable->reservation);
		$bulbsCondition =
			(Setting::getClubGameSetting(
                $clubId,
				'bulb_status',
				$reservation->game_id
			)['value'] ??
				false) ===
				true && $bulbType !== 'nothing';
		if ($bulbsCondition && $bulbType === 'duration') {
			$data['start_at'] = now();
		}
        $startAt = $data['start_at'];

		foreach ($reservationNumber->numerable->reservationSlots as $reservationSlot) {
			$this->reservationSlots()->detach($reservationSlot);
			$this->reservationSlots()->attach($reservationSlot, [
				'data' => json_encode(
					[
						'type' => $bulbType,
						'time' => $bulbsCondition
							? now()->parse($data['start_at'])
								->addMinutes($data['duration'])
								->format('H:i')
							: null,
					],
					JSON_THROW_ON_ERROR
				),
			]);

            if ($bulbsCondition) {
                self::prepareStartReservationSlotBulbAction(
                    $reservationSlot,
                    $data,
                    $reservationNumber->numerable->reservation
                );
                self::prepareEndReservationSlotBulbAction(
                    $reservationSlot,
                    $data,
                    $reservationNumber->numerable->reservation
                );
            }
		}
        event(new CalendarDataChanged(Club::getClub($clubId)));

		return true;
	}

	public static function prepareStartReservationSlotBulbAction(
		ReservationSlot|array $reservationSlot,
		array $data,
		Reservation|null $reservation = null
	): void {
		if (is_array($reservationSlot)) {
			$reservationSlot = (object) $reservationSlot;
		}
		$slot = Slot::getSlot($reservationSlot->slot_id);
        self::deleteOldBulbActions($slot);
		if ($slot->bulbAdapter) {
			$slot->changeBulbStatus(
				BulbStatus::ON,
				BulbReason::RESERVATION,
				Timezone::convertFromLocal($data['start_at']),
				$reservation ?? $reservationSlot->reservation
			);
		}
	}

	public static function prepareEndReservationSlotBulbAction(
		ReservationSlot|array $reservationSlot,
		array $data,
		Reservation|null $reservation = null
	): void {
		if (is_array($reservationSlot)) {
			$reservationSlot = (object) $reservationSlot;
		}
		$slot = Slot::getSlot($reservationSlot->slot_id);
		if ($slot->bulbAdapter) {
			$slot->changeBulbStatus(
				BulbStatus::OFF,
				BulbReason::RESERVATION,
				Timezone::convertFromLocal($data['start_at'])->addMinutes($data['duration']),
				$reservation ?? $reservationSlot->reservation
			);
		}
	}

	public static function clearReservationBulbActions(
		Reservation|array $reservation,
		Slot|null $slot = null
	): void {
		if (is_array($reservation)) {
			$reservation = (object) $reservation;
		}
		BulbAction::where('assigned_to_type', (new Reservation())->getMorphClass())
			->where('assigned_to_id', $reservation->id)
			->when($slot, function ($query) use ($slot) {
				$query->where('slot_id', $slot->id);
			})
			->forcedelete();
		/*->update([
                'run_at' => null,
                'deleted_at' => now(),
            ])*/
	}

    public static function deleteOldBulbActions(Slot $slot): void
    {
        BulbAction::where('slot_id', $slot->id)->where('reason', BulbReason::MANUAL)->where('run_at', '<=', now('UTC'))->withTrashed()->forcedelete();
        BulbAction::create([
            'slot_id' => $slot->id,
            'reason' => BulbReason::MANUAL,
            'run_at' => now('UTC')->subDay(),
            'deleted_at' => now('UTC')->subDay(),
            'bulb_status' => BulbStatus::OFF
        ]);
    }
}
