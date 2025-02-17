<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run(): void
	{
		$methods = [
			[
				'code' => 'cash',
			],
			[
				'code' => 'cashless',
			],
			[
				'code' => 'card',
			],
			[
				'type' => 'stripe',
				'online' => true,
			],
			[
				'type' => 'tpay',
				'online' => true,
			],
		];

		foreach ($methods as $method) {
			PaymentMethod::create(
				$method + [
					'online' => false,
				]
			);
		}
	}
}
