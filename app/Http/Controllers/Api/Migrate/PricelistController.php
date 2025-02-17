<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\Pricelist;
use App\Models\Club;
use App\Models\Game;

class PricelistController extends Controller
{
	public function store(Request $request)
	{
		info(json_encode($request->all()));
		$pricelistMorph = (new Pricelist())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $pricelistMorph)
			->first();

		if ($importModel) {
			return $this->update($request, $request->input('id'));
		}

		$assignedClub = ImportedModel::where('old_id', $request->input('club_id'))
			->where('model_type', (new Club())->getMorphClass())
			->first();

		if (!$assignedClub) {
			return response(['status' => 'undefined club'], 400);
		}

		$game = Game::where('name', $request->input('game'))->first();

		$data = $request->only(['name', 'created_at']);

		$data['game_id'] = $game->id ?? 1;

		$importedPricelist = $assignedClub->model->pricelists()->create($data);

		ImportedModel::create([
			'model_type' => $pricelistMorph,
			'model_id' => $importedPricelist->id,
			'old_id' => $request->input('id'),
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Pricelist())->getMorphClass())
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$game = Game::where('name', $request->input('game'))->first();

		$data = $request->only(['name']);

		$data['game_id'] = $game->id ?? 1;

		$importModel->model->update($data);
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Pricelist())->getMorphClass())
			->first();

		$importModel->model->delete();
		$importModel->delete();
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
