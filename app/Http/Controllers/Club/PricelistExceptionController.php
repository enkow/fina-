<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\StorePricelistExceptionRequest;
use App\Models\Game;
use App\Models\Pricelist;
use Illuminate\Http\RedirectResponse;

class PricelistExceptionController extends Controller
{
	public function store(
		StorePricelistExceptionRequest $request,
		Game $game,
		Pricelist $pricelist
	): RedirectResponse {
		$pricelist->pricelistExceptions()->delete();
		foreach ($request->all()['entries'] ?? [] as $pricelistException) {
			$pricelistExceptionModel = $pricelist->pricelistExceptions()->create($pricelistException);
			$pricelistExceptionModel->update([
				'start_at' => $pricelistException['start_at'],
				'end_at' => $pricelistException['end_at'],
			]);
		}

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('pricelist-exception.successfully-stored'),
			]);
	}
}
