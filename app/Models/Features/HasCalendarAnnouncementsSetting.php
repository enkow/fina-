<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasCalendarAnnouncementsSetting extends Feature
{
	use HasParent;

	public static string $name = 'has_calendar_announcements_setting';

	public array $defaultTranslations = [
		'setting-title' => 'Komunikaty na Kalendarzu Online',
		'setting-description' =>
			'Możesz ustawić komunikaty, które będą widoczne na głównym widoku kalendarza na którym klienci online dokonują rezerwacji.',
		'setting-see-more-content' => 'Dowiedz się więcej',
		'setting-see-mode-link' => 'https://example.com',
	];

	public array $conflictedFeatures = [HasCalendarAnnouncementsSetting::class];
}
