<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\User;
use App\Models\Club;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
	public function store(Request $request)
	{
		$userMorph = (new User())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $userMorph)
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

		$data = $request->only(['first_name', 'last_name', 'email', 'password', 'last_login', 'created_at']);

		$data['club_id'] = $assignedClub->model_id;
		$data['country_id'] = $assignedClub->model->country_id;

		$data['type'] = $request->input('manager') ? 'manager' : 'employee';

		$lastUser = User::latest()->first();
		if (
			!empty(
				DB::table('users')
					->where('email', $data['email'])
					->first()
			)
		) {
			$data['email'] = 'new_' . $assignedClub->model->id . '_' . $data['email'];
		}

		$importedUser = $assignedClub->model->users()->create($data);
		ImportedModel::create([
			'model_type' => $userMorph,
			'model_id' => $importedUser->id,
			'old_id' => $request->input('id'),
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new User())->getMorphClass())
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$data = $request->only(['first_name', 'last_name', 'email', 'password', 'last_login']);
		$data['type'] = $request->input('manager') ? 'manager' : 'employee';

		$importModel->model->update($data);
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new User())->getMorphClass())
			->first();

		$importModel->model->delete();
		$importModel->delete();
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
