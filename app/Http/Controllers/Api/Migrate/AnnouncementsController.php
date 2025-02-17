<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\Announcement;
use App\Models\Club;

class AnnouncementsController extends Controller
{
	public function store(Request $request)
	{
		$announcementMorph = (new Announcement())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $announcementMorph)
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

		$data = $request->only(['content', 'created_at']);

		$data['start_at'] = $request->input('date');
		$data['end_at'] = $request->input('date');

		if ($request->input('on_widget')) {
			$data['type'] = 1;

			foreach ($assignedClub->model->games as $game) {
				$data['game_id'] = $game->id ?? 1;

				$importedAnnouncement = $assignedClub->model->announcements()->create($data);

				ImportedModel::create([
					'model_type' => $announcementMorph,
					'model_id' => $importedAnnouncement->id,
					'old_id' => $request->input('id'),
				]);
			}
		} else {
			$importedAnnouncement = $assignedClub->model->announcements()->create($data);

			ImportedModel::create([
				'model_type' => $announcementMorph,
				'model_id' => $importedAnnouncement->id,
				'old_id' => $request->input('id'),
			]);
		}

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModels = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Announcement())->getMorphClass())
			->get();

		if (!$importModels->count()) {
			return $this->store($request);
		}

		$data = $request->only(['content', 'created_at']);

		$data['start_at'] = $request->input('date');
		$data['end_at'] = $request->input('date');

		foreach ($importModels as $importModel) {
			$importModel->model->update($data);
		}
		$importModels[0]->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModels = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Announcement())->getMorphClass())
			->get();

		foreach ($importModels as $importModel) {
			$importModel->model->delete();
			$importModel->delete();
		}
		$importModels[0]->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
