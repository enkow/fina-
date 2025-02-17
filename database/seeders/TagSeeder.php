<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Tag;
use Exception;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function run(): void
	{
		Tag::factory()
			->count(20)
			->create();
	}
}
