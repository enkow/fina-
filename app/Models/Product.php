<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends BaseModel
{
	use HasFactory, SoftDeletes, Searchable;

	protected $fillable = ['name_pl', 'name_en', 'fakturownia_id_pl', 'fakturownia_id_en', 'system_label'];

	public function clubs(): BelongsToMany
	{
		return $this->belongsToMany(Club::class, 'club_product', 'product_id', 'club_id');
	}

	public function invoiceItems(): MorphMany
	{
		return $this->morphMany(InvoiceItem::class, 'items');
	}
}
