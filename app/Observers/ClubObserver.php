<?php

namespace App\Observers;

use App\Enums\AgreementContentType;
use App\Enums\AgreementType;
use App\Models\Club;
use App\Models\Game;
use App\Models\OpeningHours;
use App\Custom\Fakturownia;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use JsonException;

class ClubObserver
{
	/**
	 * Handle the Club "created" event.
	 *
	 * @param  Club  $club
	 *
	 * @return void
	 * @throws JsonException
	 */
	public function created(Club $club): void
	{
		OpeningHours::passDefaultToClub($club->id);

		foreach (Game::whereIn('id', [1])->get() as $game) {
			$greatestWeightGame = $club
				->games()
				->orderByPivot('weight', 'desc')
				->first();
			$greatestWeight = !empty($greatestWeightGame) ? $greatestWeightGame->pivot->weight : 0;
			$club->games()->attach($game, [
				'weight' => $greatestWeight + 1,
				'custom_names' => json_encode([], JSON_THROW_ON_ERROR),
			]);
		}

		foreach (array_column(AgreementType::cases(), 'value') as $agreementType) {
			$club->agreements()->create([
				'type' => $agreementType,
				'content_type' => AgreementContentType::Text->value,
				'required' => match (true) {
					$agreementType === AgreementType::MarketingAgreement->value => false,
					default => true,
				},
				'active' => false,
			]);
		}

		$club->users()->create([
			'type' => 'manager',
			'email' => "administrator{$club->id}@bookgame.io",
			'first_name' => 'Administrator',
			'last_name' => 'Account',
		]);

		//Create sms club products
		$products = Product::whereIn('system_label', ['sms_offline', 'sms_online'])->get();

		foreach ($products as $product) {
			$club->products()->attach($product, [
				'period' => 'month',
				'cost' => 0,
			]);
		}
	}

	public function updated(Club $club): void
	{
		$fakturownia = new Fakturownia();
		$clubData = [
			'name' => $club->invoice_autopay ? $club->billing_name : $club->name,
			'tax_no' => $club->vat_number,
			'phone' => $club->phone_number,
			'email' => $club->email,
			'country' => $club->country->code,
			'post_code' => $club->invoice_autopay ? $club->billing_postal_code : $club->postal_code,
			'city' => $club->invoice_autopay ? $club->billing_city : $club->city,
			'street' => $club->invoice_autopay ? $club->billing_address : $club->address,
		];

		$fakturownia->updateClient($club->getAttribute('fakturownia_id'), $clubData);
		$club->flushCache();
	}

	public function deleted(Club $club): void
	{
		$club->users()->delete();
	}
}
