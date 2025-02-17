<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricelistItem extends BaseModel
{
	use HasFactory, SoftDeletes;

	public static array $loggable = ['from', 'to', 'day', 'price'];
	protected $fillable = ['from', 'to', 'day', 'price', 'created_at'];

	public function pricelist(): BelongsTo
	{
		return $this->belongsTo(Pricelist::class);
	}

	public function changes(): MorphMany
	{
		return $this->morphMany(DataChange::class, 'changable');
	}
}
