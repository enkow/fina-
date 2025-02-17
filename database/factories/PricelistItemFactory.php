<?php

namespace Database\Factories;

use App\Models\PricelistItem;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricelistItem>
 */
class PricelistItemFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 * @throws Exception
	 */
	public function definition(): array
	{
		return [
			'from' => '00:00:00',
			'to' => '23:59:00',
			'price' => $this->faker->randomNumber(4) + 100,
		];
	}
}
