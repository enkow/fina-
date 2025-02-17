<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PricelistException extends BaseModel
{
	public static array $loggable = ['from', 'to', 'start_at', 'end_at', 'price'];
	protected $fillable = ['from', 'to', 'start_at', 'end_at', 'price', 'created_at'];
	protected $casts = [
		'start_at' => 'date',
		'end_at' => 'date',
	];

	public function pricelist(): BelongsTo
	{
		return $this->belongsTo(Pricelist::class);
	}

	public function changes(): MorphMany
	{
		return $this->morphMany(DataChange::class, 'changable');
	}
}
