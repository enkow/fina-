<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasCustomViews extends Feature
{
	use HasParent;

	public static string $name = 'has_custom_views';
	public static array $defaultData = [
		'custom_views' => [
			'slots.index' => null,
			'slots.create' => null,
			'slots.edit' => null,
			'reservations.calendar' => null,
			'pricelists.index' => null,
			'pricelists.create' => null,
			'pricelists.edit' => null,
			'reservations.create-form' => null,
			'reservations.edit-form' => null,
		],
	];
	public array $conflictedFeatures = [HasCustomViews::class];

	public function updateData($data): void
	{
		$data['custom_views'] = array_map(static function ($v) {
			return $v === '-1' ? null : $v;
		}, $data['custom_views']);
		parent::updateData($data);
	}

	public function insertDefaultData(): void
	{
		$this->update([
			'data' => self::$defaultData,
		]);
	}
}
