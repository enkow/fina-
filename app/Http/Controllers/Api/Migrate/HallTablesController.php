<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\Slot;
use App\Models\Game;
use App\Models\Club;

class HallTablesController extends Controller
{
	private $features = [
		'numbered_tables' => [
			[
				'type' => 'slot_has_active_status_per_weekday',
				'default' => ['active' => [true, true, true, true, true, true, true]],
			],
			[
				'type' => 'book_singular_slot_by_capacity',
				'default' => ['capacity' => 5],
			],
			[
				'type' => 'parent_slot_has_online_status',
				'key' => 'status',
				'request' => 'online_available',
			],
		],
		'unnumbered_tables' => [
			[
				'type' => 'parent_slot_has_online_status',
				'key' => 'status',
				'request' => 'online_available',
			],
		],
	];

	public function store(Request $request)
	{
		$slotMorph = (new Slot())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $slotMorph)
			->where('extra', 'hall')
			->withTrashed()
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

		$tableVariant = Game::where('name', $request->input('table_variant'))->first();

		$assignedPricelist = $assignedClub->model
			->pricelists()
			->where('game_id', $tableVariant->id)
			->first();

		if (!$assignedPricelist) {
			return response(['status' => 'undefined pricelist'], 400);
		}

		//Create main slot
		$importedSlot = $assignedPricelist->slots()->create($request->only('name', 'deleted_at'));

		//Feature set
		$featuresGame = $this->features[$tableVariant->name];
		$featureData = [];

		foreach ($featuresGame as $featureGame) {
			$feature = $tableVariant->getFeaturesByType($featureGame['type'])->first();

			$featureData[$feature->id] =
				$request->input($featureGame['request'] ?? '') !== null
					? [
						$featureGame['key'] => $request->input($featureGame['request'] ?? ''),
					]
					: $featureGame['default'];
		}

		if ($tableVariant->name === 'numbered_tables') {
			//Create table
			$tables = $request->input('tables');

			foreach ($tables as $table) {
				$importedTable = $assignedPricelist->slots()->create([
					'name' => $table['number'],
					'slot_id' => $importedSlot->id,
				]);

				$featureData = [];

				//Set active days
				$activeDays = [false, false, false, false, false, false, false];
				foreach ($table['day'] as $day) {
					$activeDays[$day - 1] = true;
				}

				$featureModel = $tableVariant
					->getFeaturesByType('slot_has_active_status_per_weekday')
					->first();
				$featureData[$featureModel->id] = ['active' => $activeDays];

				//Set capacity
				$featureModel = $tableVariant->getFeaturesByType('book_singular_slot_by_capacity')->first();
				$featureData[$featureModel->id] = ['capacity' => $table['capacity']];

				//Set capacity
				$featureModel = $tableVariant->getFeaturesByType('parent_slot_has_online_status')->first();
				$featureData[$featureModel->id] = ['status' => true];

				//Set features
				foreach ($tableVariant->features as $feature) {
					if (!isset($featureData[$feature->id])) {
						$featureData[$feature->id] = $feature->data;
					}

					$feature->updateSlotData($importedTable, ['features' => $featureData]);
				}

				foreach ($table['id'] as $_id) {
					ImportedModel::create([
						'model_type' => $slotMorph,
						'model_id' => $importedTable->id,
						'old_id' => $_id,
						'extra' => 'table',
					]);
				}
			}
		} else {
			//select count chair
			$capacity = $request->input('capacity');

			//Set capacity
			$featureModel = $tableVariant->getFeaturesByType('person_as_slot')->first();
			$featureData[$featureModel->id] = [
				'capacity' => [
					0,
					$capacity['1'],
					$capacity['2'],
					$capacity['3'],
					$capacity['4'],
					$capacity['5'],
					$capacity['6'],
					$capacity['7'],
				],
			];

			foreach ($tableVariant->features as $feature) {
				if (!isset($featureData[$feature->id])) {
					continue;
				}

				$feature->updateSlotData($importedSlot, ['features' => $featureData]);
			}
		}

		foreach ($tableVariant->features as $feature) {
			if (!isset($featureData[$feature->id])) {
				continue;
			}

			$feature->updateSlotData($importedSlot, ['features' => $featureData]);
		}

		ImportedModel::create([
			'model_type' => $slotMorph,
			'model_id' => $importedSlot->id,
			'old_id' => $request->input('id'),
			'extra' => 'hall',
		]);

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$slotMorph = (new Slot())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $slotMorph)
			->where('extra', 'hall')
			->withTrashed()
			->first();

		if (!$importModel) {
			return $this->store($request);
		}

		$tableVariant = Game::where('name', $request->input('table_variant'))->first();

		$assignedPricelist = $importModel->model->pricelist;

		//Update hall
		$importModel->model->update($request->only('name', 'deleted_at'));

		//Feature set
		$featuresGame = $this->features[$tableVariant->name];
		$featureData = [];

		foreach ($featuresGame as $featureGame) {
			$feature = $tableVariant->getFeaturesByType($featureGame['type'])->first();

			$featureData[$feature->id] =
				$request->input($featureGame['request'] ?? '') !== null
					? [
						$featureGame['key'] => $request->input($featureGame['request'] ?? ''),
					]
					: $featureGame['default'];
		}

		if ($tableVariant->name === 'numbered_tables') {
			//remove all slots
			foreach ($assignedPricelist->slots as $slot) {
				$importedSlot = ImportedModel::where('old_id', $slot->id)
					->where('model_type', $slotMorph)
					->where('extra', 'table')
					->first();
				$importedSlot?->delete();
			}
			$assignedPricelist
				->slots()
				->whereNotNull('slot_id')
				->delete();

			//Create table
			$tables = $request->input('tables');

			foreach ($tables as $table) {
				$importedTable = $assignedPricelist->slots()->create([
					'name' => $table['number'],
					'slot_id' => $importModel->model->id,
				]);

				$featureData = [];

				//Set active days
				$activeDays = [false, false, false, false, false, false, false];
				foreach ($table['day'] as $day) {
					$activeDays[$day - 1] = true;
				}

				$featureModel = $tableVariant
					->getFeaturesByType('slot_has_active_status_per_weekday')
					->first();
				$featureData[$featureModel->id] = ['active' => $activeDays];

				//Set capacity
				$featureModel = $tableVariant->getFeaturesByType('book_singular_slot_by_capacity')->first();
				$featureData[$featureModel->id] = ['capacity' => $table['capacity']];

				//Set capacity
				$featureModel = $tableVariant->getFeaturesByType('parent_slot_has_online_status')->first();
				$featureData[$featureModel->id] = ['status' => true];

				//Set features
				foreach ($tableVariant->features as $feature) {
					if (!isset($featureData[$feature->id])) {
						$featureData[$feature->id] = $feature->data;
					}

					$feature->updateSlotData($importedTable, ['features' => $featureData]);
				}

				foreach ($table['id'] as $_id) {
					ImportedModel::create([
						'model_type' => $slotMorph,
						'model_id' => $importModel->model->id,
						'old_id' => $_id,
						'extra' => 'table',
					]);
				}
			}
		} else {
			//select count chair
			$capacity = $request->input('capacity');

			//Set capacity
			$featureModel = $tableVariant->getFeaturesByType('person_as_slot')->first();
			$featureData[$featureModel->id] = [
				'capacity' => [
					0,
					$capacity['1'],
					$capacity['2'],
					$capacity['3'],
					$capacity['4'],
					$capacity['5'],
					$capacity['6'],
					$capacity['7'],
				],
			];

			foreach ($tableVariant->features as $feature) {
				if (!isset($featureData[$feature->id])) {
					continue;
				}

				$feature->updateSlotData($importModel->model, ['features' => $featureData]);
			}
		}

		foreach ($tableVariant->features as $feature) {
			if (!isset($featureData[$feature->id])) {
				continue;
			}

			$feature->updateSlotData($importModel->model, ['features' => $featureData]);
		}

		$importModel->model->pricelist->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', (new Slot())->getMorphClass())
			->where('extra', 'hall')
			->first();

		foreach ($importModel->model->pricelist->slots as $slot) {
			$importedSlot = ImportedModel::where('old_id', $slot->id)
				->where('model_type', (new Slot())->getMorphClass())
				->where('extra', 'table')
				->first();
			$importedSlot->delete();
		}
		$importModel->model->pricelist->slots()->delete();

		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
