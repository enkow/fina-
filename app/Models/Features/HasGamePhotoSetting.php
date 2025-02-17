<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasGamePhotoSetting extends Feature
{
	use HasParent;

	public static string $name = 'has_game_photo_setting';

	public array $conflictedFeatures = [HasGamePhotoSetting::class];

	public array $defaultTranslations = [
		'game-photo' => 'Zdjęcie na wtyczce',
		'game-photo-description' =>
			'Dodaj zdjęcie gry, które będzie widoczne dla klientów robiących rezerwacje online',
		'game-photo-no-file-alert' => 'Serwis wykrył brak pliku ze zdjęciem gry.',
		'drop-game-photo-there' =>
			'Upuść plik lub kliknij tutaj, aby dodać zdjęcie gry (png, jpg, jpeg, gif)',
	];

	public array $settings = [
		'global' => [],
		'club' => [
			'game_photo' => [
				'type' => 'file',
				'disk' => 'gamePhotos',
				'default' => null,
				'validationRules' => [
					'value' => 'nullable|file|mimes:png,jpg,jpeg,gif|max:5120',
				],
			],
		],
	];
}
