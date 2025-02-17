<?php

namespace App\Models;

use App\Enums\BulbReason;
use App\Enums\BulbStatus as EnumsBulbStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class BulbAction extends BaseModel
{
	use SoftDeletes, HasRelationships;

	protected $table = 'bulb_actions';
	protected $guarded = ['id'];

	protected $casts = [
		'bulb_status' => EnumsBulbStatus::class,
		'reason' => BulbReason::class,
		'run_at' => 'datetime',
	];

	public function slot()
	{
		return $this->belongsTo(Slot::class);
	}

	public function feature()
	{
		return $this->hasOneDeep(
			Feature::class,
			[Slot::class, 'feature_payload'],
			['id', ['describable_type', 'describable_id']],
			['slot_id']
		)
			->where('type', 'slot_has_bulb')
			->withPivot('feature_payload', ['data'], Feature::class, 'pivot');
	}

	public function bulb(): Attribute
	{
		return Attribute::make(get: fn() => $this->feature->pivot->data['name'] ?? null);
	}

	public function bulbAdapter()
	{
		return $this->hasOneDeep(
			BulbAdapter::class,
			[Slot::class, Pricelist::class, Club::class, Setting::class],
			['id', 'id', 'id', 'club_id'],
			['slot_id', 'pricelist_id', 'club_id', 'id']
		);
	}
}
