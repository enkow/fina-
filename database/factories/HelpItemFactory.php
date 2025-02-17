<?php

namespace Database\Factories;

use App\Models\HelpItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HelpItem>
 */
class HelpItemFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'thumbnail' => $this->faker->image(
				storage_path('app/helpItemThumbnails'),
				640,
				480,
				'sports',
				false,
				true
			),
			'video_url' => [
				'https://www.youtube.com/embed/jQRI3b2SX8c',
				'https://www.youtube.com/embed/RMLBjvvtXyk',
				'https://www.youtube.com/embed/ddeAyYF_uwg',
				'https://www.youtube.com/embed/8SCM_YeNOos',
				'https://www.youtube.com/embed/buCD-_1UPn4',
			][$this->faker->randomNumber(2) % 5],
			'title' => $this->faker->realText(20),
			'description' => $this->faker->realText(100),
			'content' => $this->faker->realText(4000),
		];
	}
}
