<?php

namespace App\Observers;

use App\Models\Country;
use App\Models\User;

class CountryObserver
{
	public function updated(Country $country): void
	{
		if ($country->getChanges()['active'] ?? true) {
			return;
		}
		User::where('type', 'admin')
			->where('country_id', $country->id)
			->update([
				'country_id' => Country::where('active', true)->first()->id,
			]);
	}
}
