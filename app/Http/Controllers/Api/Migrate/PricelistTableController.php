<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\Club;
use App\Models\PricelistItem;
use App\Models\PricelistException;

class PricelistTableController extends Controller
{
	public function store(Request $request)
	{
		$isException = $request->input('exception') !== null;

		if ($isException) {
			$morph = (new PricelistException())->getMorphClass();
		} else {
			$morph = (new PricelistItem())->getMorphClass();
		}

		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $morph)
			->where('extra', 'table')
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

		$assignedGame = $assignedClub->model
			->games()
			->where('name', $request->input('table_variant'))
			->first();

		if (!$assignedGame) {
			return response(['status' => 'undefined game'], 400);
		}

		$assignedPricelists = $assignedClub->model
			->pricelists()
			->where('game_id', $assignedGame->id)
			->get();

		if (!$assignedPricelists->count()) {
			$pricelist = $assignedClub->model->createEmptyPricelist($assignedGame);
		}

		$data = $request->only(['day', 'price', 'created_at']);
		$data['from'] = '00:00:00';
		$data['to'] = '23:59:00';

		foreach ($assignedPricelists as $assignedPricelist) {
			if ($isException) {
				$importedException = $assignedPricelist->pricelistExceptions()->updateOrCreate(
					[
						'start_at' => $request->input('exception'),
						'end_at' => $request->input('exception'),
						'from' => '00:00:00',
						'to' => '23:59:00',
					],
					$data
				);

				ImportedModel::create([
					'model_type' => $morph,
					'model_id' => $importedException->id,
					'old_id' => $request->input('id'),
					'extra' => 'table',
				]);
			} else {
				$importedItem = $assignedPricelist
					->pricelistItems()
					->updateOrCreate(['day' => $data['day']], $data);

				ImportedModel::create([
					'model_type' => $morph,
					'model_id' => $importedItem->id,
					'old_id' => $request->input('id'),
					'extra' => 'table',
				]);
			}
		}
		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$isException = $request->input('exception') !== null;

		if ($isException) {
			$morph = (new PricelistException())->getMorphClass();
		} else {
			$morph = (new PricelistItem())->getMorphClass();
		}

		$importModels = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $morph)
			->where('extra', 'table')
			->get();

		if (!$importModels->count()) {
			return $this->store($request);
		}

		$data = $request->only(['day', 'price', 'created_at']);
		$data['from'] = '00:00:00';
		$data['to'] = '23:59:00';

		$data = $request->only(['active', 'name', 'description', 'quantity', 'price']);

		foreach ($importModels as $importModel) {
			$importModel->model->update($data);
			$importModel->model->pricelist->club->flushCache();
		}

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$isException = $request->input('exception') !== null;

		if ($isException) {
			$morph = (new PricelistException())->getMorphClass();
		} else {
			$morph = (new PricelistItem())->getMorphClass();
		}

		$importModels = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $morph)
			->where('extra', 'table')
			->get();

		foreach ($importModels as $importModel) {
			$importModel->model->delete();
			$importModel->delete();
		}

		$importModels[0]->model->pricelist->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
