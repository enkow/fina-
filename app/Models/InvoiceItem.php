<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

use App\Models\Invoice;
use App\Models\Product;

class InvoiceItem extends Model
{
	use HasFactory;

	protected $fillable = ['details', 'settings', 'total', 'model_id', 'model_type'];

	protected $casts = [
		'settings' => 'array',
		'details' => 'array',
		'total' => 'integer',
	];

	public function invoice()
	{
		return $this->belongsTo(Invoice::class);
	}

	public function model(): MorphTo
	{
		return $this->morphTo();
	}
}
