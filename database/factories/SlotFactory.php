<?php

namespace Database\Factories;

use App\Models\Slot;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slot>
 */
class SlotFactory extends Factory
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
			'name' => random_int(1, 100),
			'active' => $this->faker->randomNumber(2) % 5 !== 0,
		];
	}
}
