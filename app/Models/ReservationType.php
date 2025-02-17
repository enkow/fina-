<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class ReservationType extends BaseModel
{
	use HasFactory, SoftDeletes;

	protected $fillable = ['name', 'color'];

	public static function getReservationType(int $reservationTypeId): ReservationType|null
	{
		return Cache::remember(
			'reservationType:' . $reservationTypeId,
			config('cache.model_cache_time'),
			function () use ($reservationTypeId) {
				return self::where('id', $reservationTypeId)->first();
			}
		);
	}

	public function flushCache(): void
	{
		Cache::forget('reservationType:' . $this->id);
	}

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function reservations(): HasMany
	{
		return $this->hasMany(Reservation::class);
	}
}
