<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\SpecialOffer;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpecialOffer>
 */
class SpecialOfferFactory extends Factory
{
	/**
	 * @throws Exception
	 */
	private function getRandomTimeRangeArray(): array
	{
		$result = [];
		$iterations = random_int(0, 2);

		for ($i = 0; $i < $iterations; $i++) {
			$baseHour = $i ? (int) explode(':', end($result)['to'])[0] : 8;
			$from = $baseHour + random_int(1, 3);
			$to = $from + random_int(1, 3);

			$result[] = [
				'from' => $from . ':00',
				'to' => $to . ':00',
			];
		}

		return $result;
	}

	/**
	 * @throws Exception
	 */
	public function getRandomDateRangeArray(): array
	{
		$result = [];
		$iterations = random_int(0, 2);

		for ($i = 0; $i < $iterations; $i++) {
			$baseDate = $i ? now()->parse(end($result)['from']) : now()->subWeek();
			$from = $baseDate->addDays(random_int(0, 3))->format('Y-m-d');
			$to = now()
				->parse($from)
				->addDays(random_int(1, 3))
				->format('Y-m-d');

			$result[] = [
				'from' => $from,
				'to' => $to,
			];
		}

		return $result;
	}

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 * @throws Exception
	 */
	public function definition(): array
	{
		$activeWeekDays = [];
		foreach (range(1, 7) as $weekDay) {
			if ($this->faker->boolean) {
				$activeWeekDays[] = $weekDay;
			}
		}

		return [
			'active' => true,
			'active_by_default' => $this->faker->randomNumber(1) > 6,
			'game_id' => ($this->faker->randomNumber(4) % 4) + 1,
			'value' => $this->faker->randomNumber(2),
			'name' => substr(md5($this->faker->randomNumber(9)), 0, 6),
			'description' => $this->faker->realText(20),
			'active_week_days' => $activeWeekDays,
			'duration' => [null, (int) (($this->faker->randomNumber() % 5) + 1) * 30][
				$this->faker->randomNumber(2) % 2
			],
			'time_range_type' => ['start', 'end'][$this->faker->randomNumber(2) % 2],
			'time_range' => [
				'start' => $this->getRandomTimeRangeArray(),
				'end' => $this->getRandomTimeRangeArray(),
			],
			'slots' => [null, (int) (($this->faker->randomNumber() % 5) + 1)][
				$this->faker->randomNumber(2) % 2
			],
			'applies_default' => (bool) $this->faker->randomNumber(2) % 2,
			'when_applies' => $this->getRandomDateRangeArray(),
			'when_not_applies' => $this->getRandomDateRangeArray(),
			'creator_id' => function ($attributes) {
				return Club::find($attributes['club_id'])
					->users()
					->inRandomOrder()
					->first()->id;
			},
		];
	}
}
