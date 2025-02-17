<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerEmail extends BaseModel
{
	protected $fillable = ['game_id', 'email'];

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function game(): BelongsTo
	{
		return $this->belongsTo(Game::class);
	}
}
