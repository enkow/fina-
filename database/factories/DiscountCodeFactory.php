<?php

namespace Database\Factories;

use App\Enums\DiscountCodeType;
use App\Models\Club;
use App\Models\Customer;
use App\Models\DiscountCode;
use App\Models\Game;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DiscountCode>
 */
class DiscountCodeFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 * @throws Exception
	 */
	public function definition(): array
	{
		$discountCodeTypes = DiscountCodeType::cases();
		$discountCodeType = $discountCodeTypes[$this->faker->randomNumber() % count($discountCodeTypes)];
		$discountCodeValue = ($this->faker->randomNumber(3) % 99) + 1;
		if ($discountCodeType === 1) {
			$discountCodeValue *= 100;
		}

		return [
			'game_id' => Game::inRandomOrder()->first()->id,
			'active' => $this->faker->boolean,
			'type' => $discountCodeType,
			'code' => substr(md5($this->faker->randomNumber(9)), 0, 6),
			'value' => $discountCodeValue,
			'code_quantity' => ($codeQuantity = ($this->faker->randomNumber(2) % 90) + 10),
			'code_quantity_per_customer' => [null, $this->faker->randomNumber(1)][
				$this->faker->randomNumber(2) % 2
			],
			'start_at' => ($start_at = now()
				->addDays(50)
				->subDays($this->faker->randomNumber(2))),
			'end_at' => $start_at->clone()->addDays(10),
			'creator_id' => function ($attributes) {
				return Club::find($attributes['club_id'])
					->users()
					->inRandomOrder()
					->first()->id;
			},
		];
	}
}
