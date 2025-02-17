<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\ImportedModel;
use App\Models\SpecialOffer;
use App\Models\Game;
use App\Models\Club;

class DiscountController extends Controller
{
	private function translateExceptions(array $exceptionsData): array
	{
		$result = [];

		foreach ($exceptionsData as $exceptionData) {
			$exception = [
				'from' => $exceptionData['s'],
				'to' => $exceptionData['e'],
			];

			$result[] = $exception;
		}

		return $result;
	}

	public function store(Request $request)
	{
		$discountMorph = (new SpecialOffer())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $discountMorph)
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

		$game = Game::where('name', $request->input('game'))->first();

		$data = $request->only([
			'description',
			'active_week_days',
			'time_range_type',
			'time_range',
			'slots',
			'value',
			'created_at',
		]);

		$data['name'] = $request->input('name') ?? 'promocja';

		if ($request->input('duration') === null) {
			$data['duration'] = null;
		} else {
			$durationCarbon = Carbon::parse($request->input('duration'));
			$data['duration'] = $durationCarbon->hour * 60 + $durationCarbon->minute;
		}

		$data['when_not_applies'] = $this->translateExceptions($request->input('exceptions') ?? []);
		$data['when_applies'] = [];

		$data['game_id'] = $game->id ?? 1;

		$importedDiscount = $assignedClub->model->specialOffers()->create($data);

		ImportedModel::create([
			'model_type' => $discountMorph,
			'model_id' => $importedDiscount->id,
			'old_id' => $request->input('id'),
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new SpecialOffer())->getMorphClass())
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$game = Game::where('name', $request->input('game'))->first();

		$data = $request->only([
			'description',
			'active_week_days',
			'time_range_type',
			'time_range',
			'slots',
			'value',
		]);

		$data['name'] = $request->input('name') ?? 'promocja';

        if($request->input('valid')) {
            $data['applies_default'] = true;
            $data['when_not_applies'] = $this->translateExceptions($request->input('exceptions') ?? []);
            $data['when_applies'] = [];
        }
        else {
            $data['applies_default'] = false;
            $data['when_not_applies'] = [];
            $data['when_applies'] = $this->translateExceptions($request->input('exceptions') ?? []);
        }

		if ($request->input('duration') === null) {
			$data['duration'] = null;
		} else {
			$durationCarbon = Carbon::parse($request->input('duration'));
			$data['duration'] = $durationCarbon->hour * 60 + $durationCarbon->minute;
		}

		$data['game_id'] = $game->id ?? 1;

		$importModel->model->update($data);
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new SpecialOffer())->getMorphClass())
			->first();

		$importModel->model->delete();
		$importModel->delete();
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
