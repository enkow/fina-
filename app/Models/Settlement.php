<?php

namespace App\Models;

use App\Enums\SettlementStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Settlement extends BaseModel
{
	use SoftDeletes;

	protected $casts = [
		'status' => SettlementStatus::class,
	];

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}
}
