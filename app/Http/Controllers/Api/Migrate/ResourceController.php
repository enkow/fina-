<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\Slot;
use App\Models\Pricelist;

class ResourceController extends Controller
{
	private $features = [
		'bowling' => [
			[
				'type' => 'slot_has_convenience',
				'default' => ['status' => false],
			],
		],
		'billiard' => [
			[
				'type' => 'slot_has_lounge',
				'default' => ['status' => false, 'min' => null, 'max' => null],
			],
			[
				'type' => 'slot_has_type',
				'default' => ['name' => null],
			],
			[
				'type' => 'slot_has_subtype',
				'default' => ['name' => null],
			],
		],
	];

	public function store(Request $request)
	{
		$slotMorph = (new Slot())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $slotMorph)
			->where('extra', 'game')
			->withTrashed()
			->first();

		if ($importModel) {
			return $this->update($request, $request->input('id'));
		}

		$assignedPricelist = ImportedModel::where('old_id', $request->input('pricelist_id'))
			->where('model_type', (new Pricelist())->getMorphClass())
			->first();

		if (!$assignedPricelist) {
			return response(['status' => 'undefined pricelist'], 400);
		}

		$data = [
			'name' => $request->input('number'),
			'active' => $request->input('active'),
		];

		$importedSlot = $assignedPricelist->model->slots()->create($data);

		// Feature set

		$game = $assignedPricelist->model->game;

		$featuresGame = $this->features[$game->name];
		$featureData = [];

		foreach ($featuresGame as $featureGame) {
			$featureModel = $game->getFeaturesByType($featureGame['type'])->first();

			$featureData[$featureModel->id] =
				$request->input('features.' . $featureGame['type']) ?? $featureGame['default'];
		}

		foreach ($game->features as $feature) {
			$feature->updateSlotData($importedSlot, ['features' => $featureData]);
		}

		ImportedModel::create([
			'model_type' => $slotMorph,
			'model_id' => $importedSlot->id,
			'old_id' => $request->input('id'),
			'extra' => 'game',
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Slot())->getMorphClass())
			->where('extra', 'game')
			->withTrashed()
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$data = [
			'name' => $request->input('number'),
			'active' => $request->input('active'),
		];

		$importModel->model->update($data);

		// Feature set

		$game = $importModel->model->pricelist->game;

		$featuresGame = $this->features[$game->name];
		$featureData = [];

		foreach ($featuresGame as $featureGame) {
			$featureModel = $game->getFeaturesByType($featureGame['type'])->first();

			$featureData[$featureModel->id] =
				$request->input('features.' . $featureGame['type']) ?? $featureGame['default'];
		}

		foreach ($game->features as $feature) {
			$feature->updateSlotData($importModel->model, ['features' => $featureData]);
		}

		// Refresh
		$importModel->model->pricelist->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Slot())->getMorphClass())
			->where('extra', 'game')
			->withTrashed()
			->first();

		if ($importModel) {
			$importModel->model->update(['active' => 0]);
			$importModel->model->pricelist->club->flushCache();

			return response(['status' => 'ok', 'action' => 'deleted'], 200);
		}

		return response(['status' => 'fail', 'action' => 'deleted'], 200);
	}
}
