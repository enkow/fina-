<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\HelpItem;
use App\Models\HelpSection;
use Illuminate\Database\Seeder;

class HelpSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run(): void
	{
		$countries = Country::where('active', true)->get();
		HelpSection::factory()
			->sequence(
				['country_id' => $countries->skip(0)->first()->id],
				['country_id' => $countries->skip(1)->first()->id],
				['country_id' => $countries->skip(2)->first()->id]
			)
			->has(HelpItem::factory()->count(4))
			->count(3 * count($countries))
			->create();
	}
}
