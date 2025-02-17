<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpItemImage extends BaseModel
{
	protected $fillable = ['path'];

	public function helpItem(): BelongsTo
	{
		return $this->belongsTo(HelpItem::class);
	}
}
