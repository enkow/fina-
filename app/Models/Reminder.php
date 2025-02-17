<?php

namespace App\Models;

use App\Enums\ReminderMethod;
use App\Enums\ReminderType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends BaseModel
{
	protected $fillable = ['method', 'type', 'real', 'invoice_item_id'];

	protected $casts = [
		'type' => ReminderType::class,
		'method' => ReminderMethod::class,
	];

	public function remindable(): BelongsTo
	{
		return $this->morphTo();
	}
}
