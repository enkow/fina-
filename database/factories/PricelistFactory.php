<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Pricelist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pricelist>
 */
class PricelistFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'game_id' => Game::inRandomOrder()->first()->id,
			'name' => $this->faker->company,
		];
	}
}
