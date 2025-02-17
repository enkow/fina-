<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Set;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Set>
 */
class SetFactory extends Factory
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
			'name' => substr(md5($this->faker->randomNumber(9)), 0, 4),
			'description' => $this->faker->realText(100),
			'price' => $this->faker->randomNumber(4),
			'photo' => $this->faker->image(storage_path('app/setImages'), 176, 75, 'city', false, true),
			'mobile_photo' => $this->faker->image(storage_path('app/setImages'), 95, 95, 'city', false, true),
			'quantity' => [
				$this->faker->randomNumber(3) % 11,
				$this->faker->randomNumber(3) % 11,
				$this->faker->randomNumber(3) % 11,
				$this->faker->randomNumber(3) % 11,
				$this->faker->randomNumber(3) % 11,
				$this->faker->randomNumber(3) % 11,
				$this->faker->randomNumber(3) % 11,
			],
			'creator_id' => function ($attributes) {
				return Club::find($attributes['club_id'])
					->users()
					->inRandomOrder()
					->first()->id;
			},
		];
	}
}
