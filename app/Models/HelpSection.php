<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HelpSection extends BaseModel
{
	use HasFactory;

	protected $fillable = ['active', 'country_id', 'title', 'description', 'weight'];

	protected $casts = [
		'active' => 'boolean',
	];

	public function country(): BelongsTo
	{
		return $this->belongsTo(Country::class);
	}

	public function helpItems(): HasMany
	{
		return $this->hasMany(HelpItem::class)->orderByDesc('weight');
	}
}
