<?php

namespace Database\Factories;

use App\Models\HelpSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HelpSection>
 */
class HelpSectionFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'title' => $this->faker->word,
			'description' => $this->faker->realText,
			'weight' => $this->faker->randomNumber(3),
		];
	}
}
