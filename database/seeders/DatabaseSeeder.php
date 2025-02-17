<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 *
	 * @return void
	 */
	public function run(): void
	{
		$this->call(PaymentMethodSeeder::class);
		$this->call(CountrySeeder::class);
		$this->call(GameSeeder::class);
		$this->call(FeatureSeeder::class);
		$this->call(AdminSeeder::class);
		$this->call(ClubSeeder::class);
		$this->call(ClubFeatureSettingSeeder::class);
		$this->call(ProductsSeeders::class);

		if (App::environment('local')) {
			$this->call(TagSeeder::class);
			if (
				$this->command->ask(
					'Do you want to seed help sections? It could take up do 1 minute. (0/1)'
				) === '1'
			) {
				$this->call(HelpSeeder::class);
			}
		}

		User::where('type', 'manager')
			->first()
			?->update([
				'email' => 'manager@bookgame.io',
				'password' => Hash::make('MMnPX8lRQTLihWqta5uF'),
			]);

		User::where('type', 'employee')
			->first()
			?->update([
				'email' => 'employee@bookgame.io',
				'password' => Hash::make('rEO4XwjcFsvShvVeFnMA'),
			]);
	}
}
