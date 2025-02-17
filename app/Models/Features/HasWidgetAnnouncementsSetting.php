<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasWidgetAnnouncementsSetting extends Feature
{
	use HasParent;

	public static string $name = 'has_widget_announcements_setting';

	public array $defaultTranslations = [
		'setting-title' => 'Komunikaty na wtyczce rezerwacyjnej',
		'setting-description' =>
			'Możesz ustawić komunikat dla konkretnej daty. Komunikat ten będzie widoczny dla klientów online podczas procesu rezerwacji na etapie kroku “Wyniki wyszukiwania“',
		'setting-see-more-content' => 'Dowiedz się więcej',
		'setting-see-mode-link' => 'https://example.com',
	];

	public array $conflictedFeatures = [HasWidgetAnnouncementsSetting::class];
}
