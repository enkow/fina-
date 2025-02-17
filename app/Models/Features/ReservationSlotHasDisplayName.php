<?php

namespace App\Models\Features;

use App\Custom\Timezone;
use App\Models\Feature;
use App\Models\ReservationNumber;
use App\Models\ReservationSlot;
use App\Models\Translation;
use Illuminate\Support\Facades\DB;
use JsonException;
use Parental\HasParent;

class ReservationSlotHasDisplayName extends Feature
{
	use HasParent;

	public static string $name = 'reservation_slot_has_display_name';

	public array $defaultTranslations = [
		'display-name' => 'Nazwa na kalendarzu',
	];

	public array $conflictedFeatures = [PersonAsSlot::class, ReservationSlotHasDisplayName::class];

	public function getReservationDataValidationRules(): array
	{
		return [
			"features.$this->id.display_name" => 'nullable|string|min:1|max:100',
		];
	}

	public function getReservationDataValidationNiceNames(): array
	{
		$club = request()->route('club') ?? club();
		$translations = Translation::retrieve(countryId: $club->country_id, featureId: $this->id);

		return [
			'features.' . $this->id . '.display_name' => $translations['display-name'],
		];
	}

	/**
	 * @throws JsonException
	 */
	public function updateReservationData(
		ReservationNumber $reservationNumber,
		array $data,
		bool $initializing = false
	): bool {
		$featureData = $data['features'][$this->id];
		$numerable = $data['reservationSlot'] ?? $reservationNumber->numerable;
		$iterableModels = [$numerable];
        $query = $numerable instanceof ReservationSlot ? $this->reservationSlots() : $this->reservations();
		$dataChangesInsertArray = [];
		foreach ($iterableModels as $iterableModel) {
			$oldDisplayNameValue = json_decode(
				$query->find($iterableModel)?->pivot?->data ??
					json_encode(['display_name' => null], true),
				true,
				512,
				JSON_THROW_ON_ERROR
			)['display_name'];
			if ($initializing) {
				$query->attach([
                    $iterableModel->id => [
						'data' => json_encode(
							['display_name' => $featureData['display_name']],
							JSON_THROW_ON_ERROR
						),
					],
				]);
			} else {
                $query->detach($numerable);
                $query->attach([
                    $iterableModel->id => [
						'data' => json_encode(
							['display_name' => $featureData['display_name']],
							JSON_THROW_ON_ERROR
						),
					],
				]);
			}
			// save changes to present them in Reservation/ReservationSlot
			if ($oldDisplayNameValue !== $featureData['display_name']) {
				$dataChangesInsertArray[] = [
					'changable_type' => $numerable->getMorphClass(),
					'changable_id' => $iterableModel->id,
					'triggerer_id' => auth()->user()->id,
					'old' => json_encode(
						[
							'features.' . $this->id . '.display_name' => $oldDisplayNameValue,
						],
						JSON_THROW_ON_ERROR | true
					),
					'new' => json_encode(
						[
							'features.' . $this->id . '.display_name' => $featureData['display_name'],
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
}
