<?php

namespace App\Models;

use App\Custom\Timezone;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OpeningHoursException extends BaseModel
{
	public static array $loggable = [
		'start_at',
		'end_at',
		'club_start',
		'club_end',
		'club_closed',
		'reservation_start',
		'reservation_end',
		'reservation_closed',
		'creator_id',
	];
	public $fillable = [
		'start_at',
		'end_at',
		'club_start',
		'club_end',
		'club_closed',
		'reservation_start',
		'reservation_end',
		'reservation_closed',
		'creator_id',
		'created_at',
	];
	protected $guarded = ['id'];

	protected $casts = [
		'start_at' => 'date',
		'end_at' => 'date',
		'club_closed' => 'boolean',
		'open_to_last_customer' => 'boolean',
		'reservation_closed' => 'boolean',
	];

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, 'creator_id');
	}

	public function changes(): MorphMany
	{
		return $this->morphMany(DataChange::class, 'changable');
	}
}
