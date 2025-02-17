<?php

namespace App\Models\Features;

use App\Models\Feature;
use App\Models\Pricelist;
use App\Models\ReservationNumber;
use App\Models\ReservationSlot;
use App\Models\Slot;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use JsonException;
use Parental\HasParent;

class SlotHasConvenience extends Feature
{
	use HasParent;

	public static string $name = 'slot_has_convenience';

	public array $defaultTranslations = [
		'slots-table-heading' => 'Bandy dla dzieci',
		'slot-create-label' => 'Udogodnienie',
		'reservation-checkbox-label' => 'Z bandami dla dzieci',
		'slot-with-convenience' => 'Tor z bandami',
		'slot-without-convenience' => 'Tor bez band',
	];

	public array $conflictedFeatures = [PersonAsSlot::class];

	public function insertDefaultData(): void
	{
		$this->update([
			'data' => [],
		]);
	}

	public function getSlotDataValidationNiceNames(): array
	{
		$translations = Translation::retrieve(countryId: club()->country_id, featureId: $this->id);

		return [
			"features.$this->id.status" => $translations['slot-create-label'],
		];
	}

	public function getSlotDataValidationRules(): array
	{
		return [
			"features.$this->id.status" => 'required|boolean',
		];
	}

	/**
	 * @throws JsonException
	 */
	public function updateSlotData(Slot $slot, array $data = []): bool
	{
		$featureData = $data['features'][$this->id] ?? ['status' => false];
		$this->slots()->detach($slot);
		$this->slots()->attach($slot, [
			'data' => json_encode(['status' => $featureData['status']], JSON_THROW_ON_ERROR),
		]);

		return true;
	}

	public function slotQueryScope(Builder $query, array $data): Builder
	{
		if ((bool) $data['features'][$this->id]['status'] === false) {
			return $query;
		}
		return $query->whereHas('features', function ($query) use ($data) {
			$query->where('features.id', $this->id);
			$query->where('feature_payload.data->status', (bool) $data['features'][$this->id]['status']);
		});
	}

	/**
	 * @throws JsonException
	 */
	public function updateReservationData(
		ReservationNumber $reservationNumber,
		array $data,
		bool $initializing = false,
		int|null $reservationOrder = 1
	): bool {
		$featureData = $data['features'][$this->id];
		$reservationSlot = $data['reservationSlot'] ?? $reservationNumber->numerable;
		$this->reservationSlots()->detach($reservationSlot);
		$this->reservationSlots()->attach($reservationSlot, [
			'data' => json_encode($featureData, JSON_THROW_ON_ERROR),
		]);
		return true;
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
		return [
			'available' => Slot::whereIn('pricelist_id', $pricelistIds)
				->where('active', true)
				->when($game->hasFeature('slot_has_parent'), fn($q) => $q->whereNotNull('slot_id'))
				->whereHas('features', function ($query) {
					$query->where('feature_payload.data->status', true);
				})
				->exists(),
		];
	}
}
