<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends BaseModel
{
	use HasFactory;

	protected $fillable = ['name'];

	public function customers(): BelongsToMany
	{
		return $this->belongsToMany(Customer::class);
	}
}
