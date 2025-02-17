<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\PricelistException;
use App\Models\Pricelist;

class PricelistExceptionsController extends Controller
{
	public function store(Request $request)
	{
		$pricelistExceptionMorph = (new PricelistException())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $pricelistExceptionMorph)
			->where('extra', 'game')
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

		$data = $request->only(['start_at', 'end_at', 'from', 'to', 'price', 'created_at']);

		if ($data['to'] === '24:00:00') {
			$data['to'] = '23:59:00';
		}

		$importedPricelistItem = $assignedPricelist->model->pricelistExceptions()->create($data);

		ImportedModel::create([
			'model_type' => $pricelistExceptionMorph,
			'model_id' => $importedPricelistItem->id,
			'old_id' => $request->input('id'),
			'extra' => 'game',
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new PricelistException())->getMorphClass())
			->where('extra', 'game')
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$assignedPricelist = ImportedModel::where('old_id', $request->input('pricelist_id'))
			->where('model_type', (new Pricelist())->getMorphClass())
			->first();

		if (!$assignedPricelist) {
			return response(['status' => 'undefined pricelist'], 400);
		}

		$data = $request->only(['start_at', 'end_at', 'from', 'to', 'price']);

		if ($data['to'] === '24:00:00') {
			$data['to'] = '23:59:00';
		}

		$data['pricelist_id'] = $assignedPricelist->model_id;

		$importModel->model->update($data);
		$importModel->model->pricelist->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new PricelistException())->getMorphClass())
			->first();

		$importModel->model->delete();
		$importModel->delete();
		$importModel->model->pricelist->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
