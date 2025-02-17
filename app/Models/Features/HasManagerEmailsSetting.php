<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasManagerEmailsSetting extends Feature
{
	use HasParent;

	public static string $name = 'has_manager_emails_setting';

	public array $defaultTranslations = [
		'setting-title' => 'Powiadomienia mailowe',
		'setting-description' =>
			'Podając tutaj swój adres mailowy spowodujesz, że każdorazowo kiedy klient online będzie dokonywał rezerwacji lub ją anuluje - otrzymasz o tym właściwe powiadomienie mailowe.',
	];

	public array $conflictedFeatures = [HasManagerEmailsSetting::class];
}
