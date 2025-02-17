<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpItem extends BaseModel
{
	use HasFactory;

	protected $fillable = [
		'active',
		'country_id',
		'title',
		'description',
		'content',
		'weight',
		'video_url',
		'thumbnail',
	];

	protected $casts = [
		'active' => 'boolean',
	];

	public function helpSection(): BelongsTo
	{
		return $this->belongsTo(HelpSection::class)->orderByDesc('weight');
	}

	public function helpItemImages(): HasMany
	{
		return $this->hasMany(HelpItemImage::class);
	}

	protected function content(): Attribute
	{
		return Attribute::make(
			get: fn(string|null $value) => auth()
				->user()
				?->isType('admin')
				? $value
				: $this->contentToPrint($value),
			set: fn(string|null $value) => $value
		);
	}

	protected function contentToPrint($value): string|null
	{
		//Insert grid divs
		$replacements = [
			'[2-<' => '<div class="grid grid-cols-1 md:grid-cols-2 gap-x-3 gap-y-2">',
			'[3-<' => '<div class="grid grid-cols-1 md:grid-cols-3 gap-x-3 gap-y-2">',
			'[4-<' => '<div class="grid grid-cols-1 md:col-span-2 xl:grid-cols-4 gap-x-3 gap-y-2">',
			'>-]' => '</div>',
			'!>' => '</div>',
			'<!' => '<div>',
		];
		$value = strtr($value, $replacements);

		//Insert images
		preg_match_all('/\[image:(\d)]/', $value, $imageIds);
		$helpItemImages = HelpItemImage::whereIn('id', $imageIds[1])->get();

		return preg_replace_callback(
			'/\[image:(\d)]/',
			static function ($matches) use ($helpItemImages) {
				$helpItemImage = $helpItemImages->where('id', $matches[1])->first();

				return !empty($helpItemImage)
					? "<img class='w-full' src='/images/help-item-images/{$helpItemImage->path}'/>"
					: $matches[0];
			},
			$value
		);
	}
}
