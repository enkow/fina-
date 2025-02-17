<?php

namespace Database\Factories;

use App\Models\ReservationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReservationType>
 */
class ReservationTypeFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		$name = null;
		while (!$name || ReservationType::where('name', $name)->exists()) {
			$name = $this->faker->word;
		}
		return [
			'name' => $name,
			'color' => $this->faker->hexColor(),
		];
	}
}
