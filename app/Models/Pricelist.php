<?php

namespace App\Models;

use App\Custom\Timezone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Pricelist extends BaseModel
{
	use HasFactory, SoftDeletes;

	public static array $loggable = ['game_id', 'name', 'active'];
	protected $fillable = ['game_id', 'name', 'active', 'created_at'];

	public static function getPricelist(int $pricelistId): self
	{
		return Cache::remember(
			'pricelist:' . $pricelistId,
			config('cache.model_cache_time'),
			function () use ($pricelistId) {
				return self::where('id', $pricelistId)
					->with('pricelistItems', 'pricelistExceptions')
					->first();
			}
		);
	}

	public function flushCache(): void
	{
		Cache::forget('pricelist:' . $this->id);
	}

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function game(): BelongsTo
	{
		return $this->belongsTo(Game::class);
	}

	public function slots(): HasMany
	{
		return $this->hasMany(Slot::class);
	}

	public function pricelistItems(): HasMany
	{
		return $this->hasMany(PricelistItem::class);
	}

	public function pricelistExceptions(): HasMany
	{
		return $this->hasMany(PricelistException::class);
	}

	public function calculatePrice(Carbon $start, Carbon $end, $withoutRounding = false): int
	{
		$periods = [];

		$startIsEnd = $start->second(0)->equalTo($end->second(0));

		for (
			$dt = $start;
			$startIsEnd ? $dt->lessThanOrEqualTo($end) : $dt->lessThan($end);
			$dt->addMinute()
		) {
			$periods[] = clone $dt;
		}

		$result = array_reduce(
			$periods,
			function ($carry, $period) {
				$dayOfWeek = weekDay($period);
				$time = $period->format('H:i:s');

				$pricelistItem = $this->pricelistItems
					->where('day', $dayOfWeek)
					->where('from', '<=', $period->format('H:i:s'))
					->where('to', '>', $period->format('H:i:s'))
					->first();

				// price list entry closing the day lasts until 23:59:00 - we have to take this into account
				if ($pricelistItem === null && $period->format('H:i:s') === '23:59:00') {
					$pricelistItem = $this->pricelistItems
						->where('day', $dayOfWeek)
						->where('from', '<=', $period->format('H:i:s'))
						->where('to', '23:59:00')
						->first();
				}

				if ($pricelistItem) {
					$pricelistException = $this->pricelistExceptions
						->filter(function ($value, $key) use ($period, $time) {
							return $value->start_at
								->clone()
								->startOfDay()
								->lte(now()->parse($period->format('Y-m-d'))) &&
								$value->end_at
									->clone()
									->startOfDay()
									->gte(now()->parse($period->format('Y-m-d'))) &&
								now()
									->parse($value->from)
									->lte(now()->parse($time)) &&
								now()
									->parse($value->to)
									->gt(now()->parse($time));
						})
						->first();

					$carry += ($pricelistException->price ?? $pricelistItem->price) / 60;
				}

				return $carry;
			},
			0
		);

		return $withoutRounding ? $result : round($result);
	}

	public function changes(): MorphMany
	{
		return $this->morphMany(DataChange::class, 'changable');
	}
}
