<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Enums\AgreementType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\Customer;
use App\Models\Club;
use Carbon\Carbon;

class UserController extends Controller
{
	public function group(Request $request)
	{
		$customerMorph = (new Customer())->getMorphClass();
		$result = [];
		$clubId = $request->all()[0]['club_id'];

		$importModels = ImportedModel::where('model_type', $customerMorph)
			->where('extra', $clubId)
			->get();

		$assignedClub = ImportedModel::where('old_id', $clubId)
			->where('model_type', (new Club())->getMorphClass())
			->first();

		if (!$assignedClub) {
			return response(['status' => 'undefined club'], 400);
		}

		$insertModels = [];

		foreach ($request->all() as $key => $user) {
			$importModel = $importModels->where('old_id', $user['id'])->first();

			$fakeRequest = new Request();
			$fakeRequest->replace($user);

			if ($importModel) {
				$res = $this->update($fakeRequest, $user['id']);
				$result[$key] = $res->getOriginalContent();
				continue;
			}

			$insertUser = $fakeRequest->only([
				'email',
				'password',
				'first_name',
				'last_name',
				'phone',
				'created_at',
			]);
			$insertUser['created_at'] = Carbon::parse($user['created_at']);
			$insertUser['verified'] = 1;
			$insertUser['club_id'] = $assignedClub->id;

			$insertedUser = $assignedClub->model->customers()->create($insertUser);
			$clubGeneralTerms = $assignedClub->model
				->agreements()
				->where('type', AgreementType::GeneralTerms)
				->first();
			$clubGeneralTerms->customers()->attach($insertedUser->id);

			$insertModels[] = [
				'model_type' => $customerMorph,
				'model_id' => $insertedUser->id,
				'old_id' => $user['id'],
				'extra' => $clubId,
			];

			$result[] = ['status' => 'ok', 'action' => 'created'];
		}

		ImportedModel::insert($insertModels);

		return $result;
	}

	public function store(Request $request)
	{
		$customerMorph = (new Customer())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $customerMorph)
			->where('extra', $request->input('club_id'))
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

		$data = $request->only(['email', 'password', 'first_name', 'last_name', 'phone', 'created_at']);

		$data['verified'] = 1;

		$importedCustomer = $assignedClub->model->customers()->create($data);

		ImportedModel::create([
			'model_type' => $customerMorph,
			'model_id' => $importedCustomer->id,
			'old_id' => $request->input('id'),
			'extra' => $request->input('club_id'),
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModels = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Customer())->getMorphClass())
			->get();

		$importModel = $importModels->where('extra', $request->input('club_id'))->first();

		if (!$importModel && $request->input('club_id')) {
			return $this->store($request);
		}

		$data = $request->only(['email', 'password', 'first_name', 'last_name', 'phone']);

		foreach ($importModels as $importModel) {
			$importModel->model->update($data);
		}
		$importModels[0]->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModels = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Customer())->getMorphClass())
			->where('extra', $request->input('club_id'))
			->first();

		foreach ($importModels as $importModel) {
			$importModel->model->delete();
			$importModel->delete();
		}
		$importModels[0]->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
