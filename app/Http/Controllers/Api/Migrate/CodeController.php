<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\DiscountCodeType;

use App\Models\ImportedModel;
use App\Models\DiscountCode;
use App\Models\User;
use App\Models\Game;
use App\Models\Club;

class CodeController extends Controller
{
	public function store(Request $request)
	{
		$codeMorph = (new DiscountCode())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $codeMorph)
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

		$assignedUser = ImportedModel::where('old_id', $request->input('creator_id'))
			->where('model_type', (new User())->getMorphClass())
			->first();

		if (!$assignedUser) {
			$assignedUserId = null;
		} else {
			$assignedUserId = $assignedUser->model_id;
		}

		$game = Game::where('name', $request->input('game'))->first();

		$data = $request->only([
			'type',
			'value',
			'code',
			'code_quantity',
			'code_quantity_per_customer',
			'start_at',
			'active',
			'end_at',
			'created_at',
		]);

		if ($request->input('type') === DiscountCodeType::Amount->value) {
			$data['value'] = ((int) $data['value']) * 100;
		}

		$data['game_id'] = $game->id ?? 1;
		$data['creator_id'] = $assignedUserId;

		$importedCode = $assignedClub->model->discountCodes()->create($data);

		ImportedModel::create([
			'model_type' => $codeMorph,
			'model_id' => $importedCode->id,
			'old_id' => $request->input('id'),
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new DiscountCode())->getMorphClass())
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$game = Game::where('name', $request->input('game'))->first();

		$data = $request->only([
			'type',
			'value',
			'code',
			'code_quantity',
			'code_quantity_per_customer',
			'start_at',
			'active',
			'end_at',
		]);

		$data['game_id'] = $game->id ?? 1;

		$importModel->model->update($data);
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new DiscountCode())->getMorphClass())
			->first();

		$importModel->model->delete();
		$importModel->delete();
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
