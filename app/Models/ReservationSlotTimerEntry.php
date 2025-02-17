<?php

namespace App\Models;

use App\Custom\Timezone;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReservationSlotTimerEntry extends Model
{
	protected $fillable = ['start_at', 'end_at', 'stopped'];

	protected $casts = [
		'start_at' => 'datetime',
		'end_at' => 'datetime',
		'stopped' => 'boolean',
	];

	protected $table = 'reservation_slot_timer_entries';

	public static function sumSecondsByReservationSlotId(int $reservationSlotId): int
	{
		return ReservationSlotTimerEntry::where('reservation_slot_id', $reservationSlotId)->sum(
			DB::raw('TIMESTAMPDIFF(SECOND, start_at, IFNULL(end_at, NOW()))')
		);
	}

	public function reservationSlot(): BelongsTo
	{
		return $this->belongsTo(ReservationSlot::class);
	}

	public function scopeStopped(Builder $query, bool $status): void
	{
		$query->where('stopped', $status);
	}

	public function scopePaused(Builder $query, bool $status): void
	{
		if ($status) {
			$query->whereNotNull('end_at');
		} else {
			$query->whereNull('end_at');
		}
	}

	protected function startAt(): Attribute
	{
		return Timezone::getClubLocalizedDatetimeAttribute();
	}

	protected function endAt(): Attribute
	{
		return Timezone::getClubLocalizedDatetimeAttribute();
	}
}
