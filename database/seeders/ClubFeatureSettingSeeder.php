<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Feature;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClubFeatureSettingSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function run(): void
	{
		foreach (Feature::where('type', 'full_day_reservations')->get() as $feature) {
			foreach (Club::all() as $club) {
				Setting::create([
					'club_id' => $club->id,
					'feature_id' => $feature->id,
					'key' => 'full_day_reservations_status',
					'value' => 1,
				]);
			}
		}
		foreach (Feature::where('type', 'slot_has_lounges')->get() as $feature) {
			foreach (Club::all() as $club) {
				if ($club->id % 3 === 2) {
					Setting::create([
						'club_id' => $club->id,
						'feature_id' => $feature->id,
						'key' => 'slot_has_lounge',
						'value' => (bool) random_int(0, 1),
					]);
				}
			}
		}
	}
}
