<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportedModel extends Model
{
	use HasFactory, SoftDeletes;

	protected $fillable = ['old_id', 'model_id', 'model_type', 'extra'];

	public function model(): BelongsTo
	{
		return $this->morphTo()->withTrashed();
	}
}
