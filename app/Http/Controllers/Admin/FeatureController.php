<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccessFeatureRequest;
use App\Http\Requests\Admin\StoreFeatureRequest;
use App\Models\Feature;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;

class FeatureController extends Controller
{
	public function store(StoreFeatureRequest $request, Game $game): RedirectResponse
	{
		$feature = $game->features()->create([
			'type' => $request->get('featureType'),
			'code' => $request->get('code'),
			'data' => [],
		]);
		$feature->insertDefaultData();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Poprawnie dodano cechę',
			]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  AccessFeatureRequest  $request
	 * @param  Game                  $game
	 * @param  Feature               $feature
	 *
	 * @return RedirectResponse
	 */
	public function update(AccessFeatureRequest $request, Game $game, Feature $feature): RedirectResponse
	{
		$feature->updateData($request->all());

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Poprawnie zaktualizowano cechę',
			]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  AccessFeatureRequest  $request
	 * @param  Game                  $game
	 * @param  Feature               $feature
	 *
	 * @return RedirectResponse
	 */
	public function destroy(AccessFeatureRequest $request, Game $game, Feature $feature): RedirectResponse
	{
		$feature->delete();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Poprawnie usunięto cechę',
			]);
	}
}
