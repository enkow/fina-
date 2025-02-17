<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasMapSetting extends Feature
{
	use HasParent;

	public static string $name = 'has_map_setting';

	public array $conflictedFeatures = [HasMapSetting::class];

	public array $settings = [
		'global' => [],
		'club' => [
			'club_map' => [
				'type' => 'file',
				'disk' => 'clubMaps',
				'default' => null,
				'validationRules' => [
					'value' => 'nullable|file|mimes:png,jpg,jpeg,gif|max:5120',
				],
			],
		],
	];
}
