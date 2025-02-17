<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasPriceAnnouncementsSettings extends Feature
{
	use HasParent;

	public static string $name = 'has_price_announcements_settings';

	public array $defaultTranslations = [
		'price-zero-setting-title' => 'Komunikat jeśli cena = 0',
		'price-zero-setting-description' =>
			'Możesz ustawić komunikat dla konkretnej daty. Komunikat ten będzie widoczny dla klientów online podczas procesu rezerwacji na etapie kroku "Wyniki wyszukiwania"',
		'price-non-zero-setting-title' => 'Komunikat jeśli cena > 0',
		'price-non-zero-setting-description' =>
			'Możesz ustawić komunikat dla konkretnej daty. Komunikat ten będzie widoczny dla klientów online podczas procesu rezerwacji na etapie kroku "Wyniki wyszukiwania"',
	];

	public array $conflictedFeatures = [HasPriceAnnouncementsSettings::class];

	public array $settings = [
		'global' => [],
		'club' => [
			'price_non_zero_announcement' => [
				'type' => 'string',
				'default' => null,
				'validationRules' => [
					'value' => 'nullable|string|max:1000',
				],
			],
			'price_zero_announcement' => [
				'type' => 'string',
				'default' => null,
				'validationRules' => [
					'value' => 'nullable|string|max:1000',
				],
			],
		],
	];
}
