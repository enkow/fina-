<?php

namespace App\Models;

use App\Enums\DiscountCodeType;
use App\Traits\Searchable;
use App\Traits\Sortable;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCode extends BaseModel
{
	use HasFactory, SoftDeletes, Searchable, Sortable;

	public static array $loggable = [
		'active',
		'game_id',
		'type',
		'code',
		'value',
		'code_quantity',
		'code_quantity_per_customer',
		'creator_id',
		'start_at',
		'end_at',
	];
	protected $fillable = [
		'active',
		'game_id',
		'type',
		'code',
		'value',
		'code_quantity',
		'code_quantity_per_customer',
		'creator_id',
		'start_at',
		'end_at',
		'created_at',
	];
	protected $casts = [
		'active' => 'boolean',
		'type' => DiscountCodeType::class,
		'start_at' => 'datetime',
		'end_at' => 'datetime',
	];

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function game(): BelongsTo
	{
		return $this->belongsTo(Game::class);
	}

	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, 'creator_id');
	}

	public function isAvailable(int $customerId = null, string $startAt = null, string $endAt = null): bool
	{
		$customerReservationsCount =
			$this->reservations_count ??
			$this->reservations()
				->where('customer_id', $customerId)
				->count();

		$startAt = $startAt ? now()->parse($startAt) : now();
		$endAt = $endAt ? now()->parse($endAt) : now();

		return match (true) {
			!$this->active => false,
			$this->code_quantity && $this->code_quantity <= $this->reservations()->count() => false,
			$this->code_quantity_per_customer &&
				$this->code_quantity_per_customer <= $customerReservationsCount
				=> false,
			$this->start_at &&
				now()
					->parse($this->start_at->format('Y-m-d H:i:s'))
					->gt(now()->parse($startAt->format('Y-m-d H:i:s')))
				=> false,
			$this->end_at &&
				now()
					->parse($this->end_at->format('Y-m-d H:i:s'))
					->lt(now()->parse($startAt->format('Y-m-d H:i:s')))
				=> false,
			default => true,
		};
	}

	public function reservations(): HasManyThrough
	{
		return $this->hasManyThrough(
			Reservation::class,
			ReservationSlot::class,
			'discount_code_id',
			'id',
			'id',
			'reservation_id'
		);
	}

	public function calculatePrice($price): int
	{
		if ($this->type === DiscountCodeType::Amount) {
			return $price - $this->value;
		}

		return round(($price * (100 - min($this->value, 100))) / 100);
	}

	public function changes(): MorphMany
	{
		return $this->morphMany(DataChange::class, 'changable');
	}

	protected function displayName(): Attribute
	{
		return Attribute::make(get: fn() => $this->code . ' - ' . $this->formatted_value);
	}

	protected function formattedValue(): Attribute
	{
		return Attribute::make(
			get: fn() => match ($this->type) {
				DiscountCodeType::Amount => $this->value / 100 .
					' ' .
					Club::getClub($this->club_id)->country->currency,
				DiscountCodeType::Percent => $this->value . '%',
			}
		);
	}
}
