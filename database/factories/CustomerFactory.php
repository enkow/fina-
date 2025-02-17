<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 * @throws \Exception
	 */
	public function definition(): array
	{
		return [
			'password' => [null, $this->faker->password][$this->faker->randomNumber(2) % 2],
			'first_name' => ($firstName = $this->faker->firstName),
			'last_name' => ($lastName = $this->faker->lastName),
			'email' => $firstName . $lastName . random_int(1, 9) . '@gmail.com',
			'phone' => random_int(100, 999) . ' ' . random_int(100, 999) . ' ' . random_int(100, 999),
			'widget_channel' => md5($this->faker->randomNumber(5)),
			'widget_channel_expiration' => now()->subDay(),
		];
	}
}
