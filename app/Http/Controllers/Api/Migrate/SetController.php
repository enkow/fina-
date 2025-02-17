<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use App\Models\ImportedModel;
use App\Models\Set;
use App\Models\Club;

class SetController extends Controller
{
	public function store(Request $request)
	{
		$setMorph = (new Set())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $setMorph)
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

		$data = $request->only(['active', 'name', 'quantity', 'price', 'created_at', 'deleted_at']);

		$data['description'] = $request->input('description') ?? $request->input('name');

		$photoUrl = $request->input('photo');
		$photoResponse = Http::get($photoUrl);

		if ($photoResponse->successful()) {
			$fileName = uniqid() . '.' . pathinfo($photoUrl, PATHINFO_EXTENSION);
			$data['photo'] = $fileName;
			Storage::disk('setImages')->put($fileName, $photoResponse->body());
		}

		$data['club_id'] = $assignedClub->model_id;

		$importedSet = Set::create($data);

		ImportedModel::create([
			'model_type' => $setMorph,
			'model_id' => $importedSet->id,
			'old_id' => $request->input('id'),
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Set())->getMorphClass())
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$data = $request->only(['active', 'name', 'quantity', 'price', 'deleted_at']);
		$data['description'] = $request->input('description') ?? $request->input('name');

		$photoUrl = $request->input('photo');
		$photoResponse = Http::get($photoUrl);

		if ($photoResponse->successful()) {
			$fileName = uniqid() . '.' . pathinfo($photoUrl, PATHINFO_EXTENSION);
			$data['photo'] = $fileName;
			Storage::disk('setImages')->put($fileName, $photoResponse->body());
		}

		$importModel->model->update($data);
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Set())->getMorphClass())
			->first();

		$importModel->model->delete();
		$importModel->delete();
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
