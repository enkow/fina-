<?php

namespace App\Models;

use App\Enums\AnnouncementType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends BaseModel
{
	protected $fillable = [
		'game_id',
		'type',
		'start_at',
		'end_at',
		'content',
		'content_top',
		'content_bottom',
		'created_at',
	];

	protected $casts = [
		'type' => AnnouncementType::class,
		'data' => 'array',
	];

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function game(): BelongsTo
	{
		return $this->belongsTo(Game::class);
	}
}
