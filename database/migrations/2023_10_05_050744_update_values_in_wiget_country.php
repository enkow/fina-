<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Club;
use App\Models\Country;

return new class extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$clubs = Club::all();
		$countries = Country::select(DB::raw('MIN(code) as code'), 'locale')
			->where('active', 1)
			->groupBy('locale')
			->pluck('locale', 'code')
			->all();

		$countriesCode = array_flip($countries);

		foreach ($clubs as $club) {
			$widget_countries = [];
			foreach (json_decode($club->widget_locales ?? '[]') as $locale) {
				if (isset($countriesCode[$locale])) {
					$widget_countries[] = $countriesCode[$locale];
				}
			}

			$club->widget_countries = $widget_countries;
			$club->save();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}
};
