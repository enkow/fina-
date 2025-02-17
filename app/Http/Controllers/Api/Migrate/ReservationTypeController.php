<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\Club;
use App\Models\ReservationType;

class ReservationTypeController extends Controller
{
	public function store(Request $request)
	{
		$typeMorph = (new ReservationType())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $typeMorph)
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

		$data = $request->only(['name', 'color', 'created_at']);

		$importedType = $assignedClub->model->reservationTypes()->create($data);

		ImportedModel::create([
			'model_type' => $typeMorph,
			'model_id' => $importedType->id,
			'old_id' => $request->input('id'),
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new ReservationType())->getMorphClass())
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$data = $request->only(['name', 'color', 'created_at']);

		$importModel->model->update($data);
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new ReservationType())->getMorphClass())
			->first();

		$importModel->model->delete();
		$importModel->delete();
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
