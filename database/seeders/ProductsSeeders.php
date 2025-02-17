<?php

namespace Database\Seeders;

use App\Custom\Fakturownia;
use App\Models\Club;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeders extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run(): void
	{
		$products = [
			[
				'name_pl' => 'SMS online',
				'name_en' => 'SMS online',
				'system_label' => 'sms_online',
			],
			[
				'name_pl' => 'SMS offline',
				'name_en' => 'SMS offline',
				'system_label' => 'sms_offline',
			],
		];

		$productsToSync = [];
		$fakturownia = new Fakturownia();

		foreach ($products as $product) {
			$product['fakturownia_id_pl'] = $fakturownia->createProduct($product['name_pl'])['id'];
			$product['fakturownia_id_en'] = $fakturownia->createProduct($product['name_en'])['id'];

			$createdProduct = Product::create($product);

			$productsToSync[$createdProduct->id] = [
				'period' => 'month',
				'cost' => 0,
			];
		}

		foreach (Club::get() as $club) {
			$club->products()->syncWithoutDetaching($productsToSync);
		}
	}
}
