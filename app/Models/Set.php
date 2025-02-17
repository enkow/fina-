<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Set extends BaseModel
{
	use HasFactory, SoftDeletes, HasRelationships;

	public $fillable = [
		'name',
		'active',
		'photo',
		'mobile_photo',
		'quantity',
		'description',
		'active',
		'price',
		'club_id',
		'creator_id',
		'deleted_at',
		'created_at',
	];

	public array $loggable = [
		'name',
		'active',
		'photo',
		'mobile_photo',
		'quantity',
		'description',
		'active',
		'price',
		'creator_id',
	];

	protected $casts = [
		'active' => 'boolean',
		'quantity' => 'array',
	];

	public static function reduce(Collection|array $sets): array
	{
		// Prepare array with ReservationSets reduced string in format {number} x {set_name}
		$reducedSets = array_reduce(
			is_array($sets) ? $sets : $sets->toArray(),
			function ($carry, $set) {
				if (!isset($carry[$set['id']])) {
					$carry[$set['id']] = [
						'name' => $set['name'],
						'count' => 0,
						'price' => 0,
					];
				}
				$carry[$set['id']]['count']++;
				$carry[$set['id']]['price'] += $set['pivot']['price'];

				return $carry;
			},
			[]
		);

		return $reducedSets;
	}

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function reservations(): HasManyDeep
	{
		return $this->hasManyDeep(Reservation::class, ['reservation_slot_set', ReservationSlot::class]);
	}

	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, 'creator_id');
	}

	public function changes(): MorphMany
	{
		return $this->morphMany(DataChange::class, 'changable');
	}

	public function reservationSlots(): BelongsToMany
	{
		return $this->belongsToMany(ReservationSlot::class)->withPivot('price');
	}
}
