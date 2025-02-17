<?php

namespace Database\Factories;

use App\Enums\ReservationSlotStatus;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
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
			'source' => $this->faker->randomNumber(1) % 2,
			'game_id' => ($gameId = ($this->faker->randomNumber(2) % 4) + 1),
			'club_note' => [null, $this->faker->realText(500)][$this->faker->randomNumber(2) % 2],
			'customer_note' => [null, $this->faker->realText(500)][$this->faker->randomNumber(2) % 2],
			'rate_service' => ($this->faker->randomNumber(2) % 5) + 1,
			'rate_atmosphere' => ($this->faker->randomNumber(2) % 5) + 1,
			'rate_staff' => ($this->faker->randomNumber(2) % 5) + 1,
			'rate_content' => $this->faker->realText,
			'currency' => 'PLN',

			'show_club_note_on_calendar' => false,
			'show_customer_note_on_calendar' => false,

			'club_commission' => 0,
			'app_commission' => 0,
			'price' => $this->faker->randomNumber(4) + 1000,
			'payment_method_id' => PaymentMethod::inRandomOrder()->first()->id,
		];
	}
}
