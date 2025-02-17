<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run(): void
	{
		User::factory()->create([
			'type' => 'admin',
			'email' => 'administrator@bookgame.io',
			'password' => Hash::make('i7rP76Joh8LLwBuv2KI'),
			'country_id' => Country::where('code', 'PL')->first()->id,
		]);
	}
}
