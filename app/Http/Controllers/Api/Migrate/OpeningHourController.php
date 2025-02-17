<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\ImportedModel;
use App\Models\OpeningHours;
use App\Models\Club;

class OpeningHourController extends Controller
{
	public function store(Request $request)
	{
		$openingHoursMorph = (new OpeningHours())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $openingHoursMorph)
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
			'reservation_start',
			'reservation_end',
			'club_start',
			'club_end',
			'open_to_last_customer',
		]);

		$data['reservation_closed'] = $request->input('reservation_start') === null;
		$data['club_closed'] = false;

		$updatedDay = $assignedClub->model->openingHours()->where('day', $request->input('day'));

		$data['created_at'] = Carbon::parse($request->input('created_at'));

		$updatedDay->update($data);
		$assignedClub->model->flushCache();

		ImportedModel::create([
			'model_type' => $openingHoursMorph,
			'model_id' => $updatedDay->first()->id,
			'old_id' => $request->input('id'),
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new OpeningHours())->getMorphClass())
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$data = $request->only([
			'reservation_start',
			'reservation_end',
			'club_start',
			'club_end',
			'open_to_last_customer',
		]);

		$data['reservation_closed'] = $request->input('reservation_start') === null;

		$importModel->model()->update($data);
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new OpeningHours())->getMorphClass())
			->first();

		$importModel->model->reservation_closed = true;
		$importModel->model->club_closed = true;
		$importModel->model->saveQuietly();
		$importModel->delete();
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
