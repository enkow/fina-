<?php

namespace App\Models;

use App\Custom\Timezone;
use App\Traits\Sortable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class SpecialOffer extends BaseModel
{
	use HasFactory, SoftDeletes, Sortable;

	public static array $loggable = [
		'active',
		'active_by_default',
		'game_id',
		'value',
		'name',
		'description',
		'active_week_days',
		'duration',
		'time_range_type',
		'time_range',
		'slots',
		'when_applies',
		'applies_default',
		'when_not_applies',
		'photo',
		'creator_id',
	];
	protected $fillable = [
		'active',
		'active_by_default',
		'game_id',
		'value',
		'name',
		'description',
		'active_week_days',
		'duration',
		'time_range_type',
		'time_range',
		'slots',
		'when_applies',
		'applies_default',
		'when_not_applies',
		'photo',
		'creator_id',
		'created_at',
	];
	protected $casts = [
		'active' => 'boolean',
		'active_by_default' => 'boolean',
		'active_week_days' => 'array',
		'time_range' => 'array',
		'when_applies' => 'array',
		'applies_default' => 'boolean',
		'when_not_applies' => 'array',
	];

    public static function getSpecialOffer(int|null $specialOfferId): self|null
    {
            return $specialOfferId ? self::where('id', $specialOfferId)->first() : null;
    }

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function game(): BelongsTo
	{
		return $this->belongsTo(Game::class);
	}

	public function reservations(): HasMany
	{
		return $this->hasMany(Reservation::class);
	}

	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, 'creator_id');
	}

	public function changes(): MorphMany
	{
		return $this->morphMany(DataChange::class, 'changable');
	}

	public function calculatePrice(int $price, Carbon|string $startAt, Carbon|string $endAt): int
	{
		$startAt = is_string($startAt) ? now()->parse($startAt) : $startAt;
		if ($startAt->getTimezone()->getName() === 'UTC') {
			$startAt = Timezone::convertToLocal($startAt);
		}
		$endAt = is_string($endAt) ? now()->parse($endAt) : $endAt;
		if ($endAt->getTimezone()->getName() === 'UTC') {
			$endAt = Timezone::convertToLocal($endAt);
		}

		$reservationDurationInMinutes = $endAt->diffInMinutes($startAt);

		if (!$reservationDurationInMinutes) {
			return $price;
		}
		if (!in_array(weekDay($startAt->clone()), $this->active_week_days, true)) {
			return $price;
		}
		if ($this->duration && $endAt->clone()->diffInMinutes($startAt->clone()) !== $this->duration) {
			return $price;
		}
		if ($this->applies_default) {
			$filteredRanges = array_filter($this->when_not_applies, static function ($range) use ($startAt) {
				return $range['from'] <= $startAt && $range['to'] >= $startAt;
			});

			if (!empty($filteredRanges)) {
				return $price;
			}
		} else {
			$doesNotApply = array_reduce(
				$this->when_not_applies,
				static function ($carry, $range) use ($startAt) {
					return $carry ||
						($range['from'] <= $startAt->clone()->format('H:i') &&
							$range['to'] >= $startAt->clone()->format('H:i'));
				},
				false
			);

			if ($doesNotApply) {
				return $price;
			}
		}
		if ($this->time_range_type === 'start') {
			$filteredRanges = array_filter($this->time_range['start'], function ($range) use ($startAt) {
				return $range['from'] <= $startAt->clone()->format('H:i') &&
					$range['to'] >= $startAt->clone()->format('H:i');
			});

			if (!empty($filteredRanges)) {
				return ($price * (100 - $this->value)) / 100;
			}
		}
		if ($this->time_range_type === 'end') {
			$specialOfferEndRanges = $this->time_range['end'];
			$overlapMinutes = 0;

			foreach ($specialOfferEndRanges as $range) {
				$rangeFromMinutes = now()
					->parse($range['from'])
					->diffInMinutes(now()->startOfDay());
				$rangeToMinutes = now()
					->parse($range['to'])
					->diffInMinutes(now()->startOfDay());

				$overlapStart = max(
					$startAt->diffInMinutes($startAt->clone()->startOfDay()),
					$rangeFromMinutes
				);
				$overlapEnd = min($endAt->diffInMinutes($endAt->clone()->startOfDay()), $rangeToMinutes);

				if ($overlapStart < $overlapEnd) {
					$overlapMinutes += $overlapEnd - $overlapStart;
				}
			}

			$discountRatio = $this->value / 100;
			$overlapRatio = $reservationDurationInMinutes
				? $overlapMinutes / $reservationDurationInMinutes
				: 0;

			return $price * (1 - $discountRatio * $overlapRatio);
		}

		return $price;
	}

	protected function displayName(): Attribute
	{
		return Attribute::make(get: fn() => $this->name . ' - ' . $this->value . '%');
	}
}
