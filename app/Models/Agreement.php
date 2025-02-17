<?php

namespace App\Models;

use App\Enums\AgreementContentType;
use App\Enums\AgreementType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Agreement extends Model
{
	protected $fillable = ['active', 'required', 'content_type', 'type', 'text', 'file'];

	protected $casts = [
		'type' => AgreementType::class,
		'content_type' => AgreementContentType::class,
		'active' => 'boolean',
		'required' => 'boolean',
	];

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function customers(): BelongsToMany
	{
		return $this->belongsToMany(Customer::class)->withTimestamps();
	}

	public function scopeRequired(Builder $query): void
	{
		$query
			->where('required', true)
			->where(function ($query) {
				$query->where(function ($query) {
					$query->where('content_type', AgreementContentType::File->value);
					$query->whereNotNull('file');
				});
				$query->orWhere(function ($query) {
					$query->where('content_type', AgreementContentType::Text->value);
					$query->whereNotNull('text');
					$query->where('text', '!=', '');
				});
			})
			->where('active', true);
	}
}
