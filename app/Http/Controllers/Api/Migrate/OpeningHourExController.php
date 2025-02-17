<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\OpeningHoursException;
use App\Models\Club;

class OpeningHourExController extends Controller
{
	public function store(Request $request)
	{
		$openingHoursExceptionMorph = (new OpeningHoursException())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $openingHoursExceptionMorph)
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

		$data = $request->only([
			'start_at',
			'end_at',
			'open_to_last_customer',
			'reservation_start',
			'reservation_end',
			'club_start',
			'club_end',
			'club_closed',
			'created_at',
		]);

		$data['club_id'] = $assignedClub->model_id;

		$importedOpeningHoursException = $assignedClub->model->openingHoursExceptions()->create($data);
		$assignedClub->model->flushCache();

		ImportedModel::create([
			'model_type' => $openingHoursExceptionMorph,
			'model_id' => $importedOpeningHoursException->id,
			'old_id' => $request->input('id'),
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new OpeningHoursException())->getMorphClass())
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$data = $request->only([
			'start_at',
			'end_at',
			'open_to_last_customer',
			'reservation_start',
			'reservation_end',
			'club_start',
			'club_end',
			'club_closed',
		]);

		$importModel->model->update($data);
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new OpeningHoursException())->getMorphClass())
			->first();

		$importModel->model->delete();
		$importModel->delete();
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
