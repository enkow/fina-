<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OpeningHours extends BaseModel
{
	public static array $loggable = [
		'club_open',
		'club_close',
		'club_closed',
		'reservation_open',
		'reservation_close',
		'reservation_closed',
	];
	protected $guarded = ['id'];
	protected $fillable = [
		'club_open',
		'club_close',
		'club_closed',
		'reservation_open',
		'reservation_close',
		'reservation_closed',
		'created_at',
	];
	protected $casts = [
		'club_closed' => 'boolean',
		'open_to_last_customer' => 'boolean',
		'reservation_closed' => 'boolean',
	];

	public static function passDefaultToClub($clubId): bool
	{
		$insertArray = [];
		foreach (range(1, 7) as $weekDay) {
			$insertArray[] = [
				'club_id' => $clubId,
				'day' => $weekDay,
				'club_start' => '10:00',
				'club_end' => '22:00',
				'reservation_start' => '12:00',
				'reservation_end' => '20:00',
			];
		}
		self::insert($insertArray);

		return true;
	}

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function changes(): MorphMany
	{
		return $this->morphMany(DataChange::class, 'changable');
	}
}
