<?php

namespace App\Models;

use App\Custom\Timezone;
use App\Enums\BulbReason;
use App\Enums\ReservationSlotStatus;
use App\Jobs\BulbRunAction;
use App\Models\Features\SlotHasBulb;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Slot extends BaseModel
{
	use HasFactory, SoftDeletes, HasRelationships;

	protected $fillable = ['pricelist_id', 'active', 'name', 'slot_id', 'deleted_at', 'created_at'];

	protected $casts = [
		'active' => 'boolean',
	];

	public static function getSlot($slotId)
	{
		return Cache::remember('slot:' . $slotId, config('cache.model_cache_time'), function () use (
			$slotId
		) {
			return $slotId
				? self::where('id', $slotId)
					->with('features', 'bulbAdapter')
					->first()
				: null;
		});
	}

	public function parentSlot(): BelongsTo
	{
		return $this->belongsTo(__CLASS__, 'slot_id');
	}

	public function childrenSlots(): HasMany
	{
		return $this->hasMany(__CLASS__);
	}

	public function childrenSlotsReservationSlots(): HasManyThrough
	{
		return $this->hasManyThrough(ReservationSlot::class, __CLASS__);
	}

	public function pricelist(): BelongsTo
	{
		return $this->belongsTo(Pricelist::class);
	}

	public function features(): MorphToMany
	{
		return $this->morphToMany(Feature::class, 'describable', 'feature_payload')->withPivot('data');
	}

	/**
	 * @throws \JsonException
	 */
	public function scopeVacant(
		$query,
		$clubId,
		$gameId,
		Carbon|string $startAt,
		Carbon|string $endAt,
		array $excludeReservationSlotsIds = [],
        $preloadedReservationSlots = null
	): void {
		$startAt = is_string($startAt) ? now()->parse($startAt) : $startAt;
		$endAt = is_string($endAt) ? now()->parse($endAt) : $endAt;
		$startAt = Timezone::convertFromLocal($startAt);
		$endAt = Timezone::convertFromLocal($endAt);

		// remember result to preserve multiple calls
		$reservedSlotIds = Cache::remember(
			md5(
				$startAt->unix() .
					$endAt->unix() .
					$clubId .
					$gameId .
					json_encode($excludeReservationSlotsIds, JSON_THROW_ON_ERROR)
			),
			30,
			function () use ($startAt, $endAt, $clubId, $gameId, $excludeReservationSlotsIds) {
				$pricelistIds = Cache::remember(
					'club:' . $clubId . 'game:' . $gameId . ':pricelist_ids',
					30,
					function () use ($clubId, $gameId) {
						return Pricelist::where('club_id', $clubId)
							->where('game_id', $gameId)
							->pluck('id')
							->toArray();
					}
				);

				$pricelistSlotsIds = Cache::remember(
					'pricelists:' . json_encode($pricelistIds) . ':slots_ids',
					30,
					function () use ($pricelistIds) {
						return Slot::whereIn('pricelist_id', $pricelistIds)
							->pluck('id')
							->toArray();
					}
				);

				return ReservationSlot::whereIn('slot_id', $pricelistSlotsIds)
					->where('occupied_status', true)
					->whereNotIn('id', $excludeReservationSlotsIds)
                    ->whereIn('status', [ReservationSlotStatus::Confirmed, ReservationSlotStatus::Pending])
                    ->whereNull('cancelation_type')
					->where(function ($query) use ($startAt, $endAt) {
						$query
							->where(function ($query) use ($startAt, $endAt) {
								$query->where(function ($query) use ($startAt, $endAt) {
									$query->where('start_at', '>=', $startAt);
									$query->where('start_at', '<', $endAt);
								});
								$query->orWhere(function ($query) use ($startAt, $endAt) {
									$query->where('end_at', '>', $startAt);
									$query->where('end_at', '<=', $endAt);
								});
							})
							->orWhere(function ($query) use ($startAt, $endAt) {
								$query->where('start_at', '<=', $startAt)->where('end_at', '>=', $endAt);
							});
					})
					->groupBy('slot_id')
					->pluck('slot_id')
					->toArray();
			}
		);

		$query->whereNotIn('slots.id', $reservedSlotIds);
	}

	public function hasFutureReservationSlots(bool $active = null, bool $canceled = null)
	{
		$reservationSlotsQuery = $this->reservationSlots()->where('start_at', '>', now('UTC'));
		if ($active === true) {
			$reservationSlotsQuery = $reservationSlotsQuery->active();
		}
		if ($canceled !== null) {
			$reservationSlotsQuery = $reservationSlotsQuery->canceled($canceled);
		}

		return $reservationSlotsQuery->exists();
	}

	public function reservationSlots(): HasMany
	{
		return $this->hasMany(ReservationSlot::class);
	}

	public static function getAvailable(array $data)
	{
		$data['game_id'] = $data['game_id'] ?? 1;
		$data['club_id'] = $data['club_id'] ?? 1;

		$gameFeatures = Game::getCached()->find($data['game_id'] ?? 1)->features;
		foreach ($gameFeatures as $feature) {
			if (!$feature->executablePublicly) {
				continue;
			}
			$data = $feature?->prepareDataForAction($data) ?? $data;
		}

		$pricelistIds = Cache::remember(
			'club:' . $data['club_id'] . 'game:' . $data['game_id'] . ':pricelist_ids',
			30,
			function () use ($data) {
				return Pricelist::where('club_id', $data['club_id'])
					->where('game_id', $data['game_id'])
					->pluck('id')
					->toArray();
			}
		);

		$startAt = now()->parse($data['start_at']);
		$endAt = $startAt->clone()->addMinutes($data['duration'] ?? 60);
		$slots = self::whereIn('pricelist_id', $pricelistIds)
			->when(isset($data['pricelist_id']), fn($q) => $q->where('pricelist_id', $data['pricelist_id']))
			->when(isset($data['active']), fn($q) => $q->where('active', $data['active']))
			->when(isset($data['vacant']) && $data['vacant'] === true, function ($query) use (
				$data,
				$startAt,
				$endAt
			) {
				$query->vacant(
					$data['club_id'],
					$data['game_id'],
					$startAt,
					$endAt,
					$data['reservation_slot_to_exclude'] ?? []
				);
			})
			->when(array_key_exists('parent_slot_id', $data) && $data['parent_slot_id'] === null, function (
				$query
			) {
				$query->whereNull('slot_id');
			})
			->with('features');

		foreach ($gameFeatures as $gameFeature) {
			$slots = $gameFeature?->slotQueryScope($slots, $data) ?? $slots;
		}

		$slots = Cache::remember('md5:' . getMd5FromQuery($slots) . ':query_result', 60, function () use (
			$slots
		) {
			return $slots->get();
		});

        if(isset($data['include_parent_slots']) && ((bool)$data['include_parent_slots'])) {
            $data['include_slot_ids'] = array_merge(
                $data['include_slot_ids'] ?? [], self::whereIn('pricelist_id', $pricelistIds)->whereNull('slot_id')->when(isset($data['active']), fn($q) => $q->where('active', $data['active']))->pluck('id')->toArray()
            );
        }

		if (isset($data['include_slot_ids'])) {
			$slotsToInclude = self::whereIn('id', $data['include_slot_ids'])
				->with('features')
				->get();
			foreach ($data['include_slot_ids'] as $slotIdToInclude) {
				if ($slotIdToInclude > 0 && !count($slots->where('id', $slotIdToInclude))) {
					$slots = $slots->push($slotsToInclude->where('id', $slotIdToInclude)->first());
				}
			}
		}
		return $slots;
	}


    public static function getAvailableOptimized(array $data, $gameFeatures)
    {
        $data['game_id'] = $data['game_id'] ?? 1;
        $data['club_id'] = $data['club_id'] ?? 1;

        foreach ($gameFeatures as $feature) {
            if (!$feature->executablePublicly) {
                continue;
            }
            $data = $feature?->prepareDataForAction($data) ?? $data;
        }

        $pricelistIds = Cache::remember(
            'club:' . $data['club_id'] . 'game:' . $data['game_id'] . ':pricelist_ids',
            300,
            function () use ($data) {
                return Pricelist::where('club_id', $data['club_id'])
                    ->where('game_id', $data['game_id'])
                    ->pluck('id')
                    ->toArray();
            }
        );

        $startAt = now()->parse($data['start_at']);
        $endAt = $startAt->clone()->addMinutes($data['duration'] ?? 60);
        $slots = self::whereIn('pricelist_id', $pricelistIds)
            ->when(isset($data['pricelist_id']), fn($q) => $q->where('pricelist_id', $data['pricelist_id']))
            ->when(isset($data['active']), fn($q) => $q->where('active', $data['active']))
            ->when(isset($data['vacant']) && $data['vacant'] === true, function ($query) use (
                $data,
                $startAt,
                $endAt
            ) {
                $query->vacant(
                    $data['club_id'],
                    $data['game_id'],
                    $startAt,
                    $endAt,
                    $data['reservation_slot_to_exclude'] ?? []
                );
            })
            ->when(array_key_exists('parent_slot_id', $data) && $data['parent_slot_id'] === null, function (
                $query
            ) {
                $query->whereNull('slot_id');
            })
            ->with('features');

        foreach ($gameFeatures as $gameFeature) {
            $slots = $gameFeature?->slotQueryScope($slots, $data) ?? $slots;
        }

        $slots = Cache::remember('md5:' . getMd5FromQuery($slots) . ':query_result', 60, function () use (
            $slots
        ) {
            return $slots->get();
        });

        if(isset($data['include_parent_slots']) && ((bool)$data['include_parent_slots'])) {
            $data['include_slot_ids'] = array_merge(
                $data['include_slot_ids'] ?? [], self::whereIn('pricelist_id', $pricelistIds)->whereNull('slot_id')->when(isset($data['active']), fn($q) => $q->where('active', $data['active']))->pluck('id')->toArray()
            );
        }

        if (isset($data['include_slot_ids'])) {
            $slotsToInclude = self::whereIn('id', $data['include_slot_ids'])
                ->with('features')
                ->get();
            foreach ($data['include_slot_ids'] as $slotIdToInclude) {
                if ($slotIdToInclude > 0 && !count($slots->where('id', $slotIdToInclude))) {
                    $slots = $slots->push($slotsToInclude->where('id', $slotIdToInclude)->first());
                }
            }
        }
        return $slots;
    }



	public function bulbActions()
	{
		return $this->hasMany(BulbAction::class)
			->whereNotNull('run_at')
			->orderByDesc('run_at');
	}

	protected function bulbStatus(): Attribute
	{
		$result = null;
        $slotBulbFeaturePivotData = Cache::remember('slot:'.$this->getOriginal('id').':bulb_feature_pivot_data', 10, function () {
            return json_decode(
                $this->features->where('type', 'slot_has_bulb')->first()->pivot->data ?? '[]',
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        });
		if (
			!in_array(
                $slotBulbFeaturePivotData['name'] ?? null,
				[null, ''],
				true
			)
		) {
			$result = $this->bulbActions->whereNotNull('deleted_at')->first()->bulb_status ?? null;
		}
		return Attribute::make(fn() => $result);
	}

	public function changeBulbStatus($status, $reason, $runAt = null, $assignTo = null, $withDeletedAt = null)
	{
        SlotHasBulb::deleteOldBulbActions($this);
        $bulbAction = $this->bulbActions()->create([
			'bulb_status' => $status,
			'reason' => $reason,
			'run_at' => $runAt ?? now('UTC'),
			'deletedAt' => $withDeletedAt ? now('UTC') : null,
			'assigned_to_type' => $assignTo?->getMorphClass(),
			'assigned_to_id' => $assignTo?->id,
		]);
		if (now('UTC')->gt($bulbAction->run_at->format('Y-m-d H:i:s'))) {
			BulbRunAction::dispatch($bulbAction);
		}
		return $bulbAction;
	}

	public function bulbAdapter()
	{
		return $this->hasOneDeep(
			BulbAdapter::class,
			[Pricelist::class, Club::class, Setting::class],
			['id', 'id', 'club_id'],
			['pricelist_id', 'club_id', 'id']
		);
	}
}
