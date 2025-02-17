<?php

namespace App\Models;

use App\Models\BulbAdapter\DomiqAdapter;
use App\Models\BulbAdapter\HomeAssistant;
use App\Models\BulbAdapter\WebThingsAdapter;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Parental;
use RuntimeException;

class BulbAdapter extends BaseModel
{
	use SoftDeletes, Parental\HasChildren;

	public const TIMEOUT = 5000;
	public const ADMIN_SETTINGS = [];
	public const CHILD_TYPES = [
		'webthings' => WebThingsAdapter::class,
		'domiq' => DomiqAdapter::class,
		'homeassistant' => HomeAssistant::class,
	];

	protected $guarded = ['id'];

	protected $casts = [
		'active' => 'boolean',
		'synchronize' => 'boolean',
		'credentials' => 'encrypted:object',
	];

	public function getChildTypes(): array
	{
		return self::CHILD_TYPES ?? [];
	}

	public function getChildType()
	{
		return self::CHILD_TYPES[$this->setting->value] ?? null;
	}

	public function isBulbOn(string $bulb): ?bool
	{
		throw new RuntimeException('Method isBulbOn() must be implemented in a child class');
	}

	public function changeState(string $bulb, bool $targetProperty): ?bool
	{
		throw new RuntimeException('Method changeState() must be implemented in a child class');
	}

	public function setting()
	{
		return $this->belongsTo(Setting::class);
	}
}
