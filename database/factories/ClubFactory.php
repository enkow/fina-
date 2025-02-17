<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Country;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Club>
 */
class ClubFactory extends Factory
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
			'country_id' => Country::where('active', true)
				->inRandomOrder()
				->first()->id,
			'name' => $this->faker->company(),
			'slug' => $this->faker->slug,
			'address' => $this->faker->address,
			'postal_code' => $this->faker->postcode,
			'city' => $this->faker->city,
			'phone_number' => $this->faker->phoneNumber,
			'email' => $this->faker->email,
			'vat_number' =>
				$this->faker->countryCode .
				(string) $this->faker->randomNumber(5) .
				(string) $this->faker->randomNumber(5),
			'description' => $this->faker->realText(400),
			'first_login_message' => $this->faker->realText(500),
			'panel_enabled' => true,
		];
	}
}
