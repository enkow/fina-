<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\Setting;
use App\Models\Club;
use App\Models\Game;

class AdminSettingsController extends Controller
{
	private array $settingsKeyTranslate = [
		'res_max_tables' => [
			'feature' => 'has_offline_reservation_limits_settings',
			'key' => 'offline_reservation_slot_limit',
			'perDay' => true,
		],
		'res_max_minutes' => [
			'feature' => 'has_offline_reservation_limits_settings',
			'key' => 'offline_reservation_duration_limit',
			'perDay' => true,
			'custom' => true,
		],
		'res_max_unpaid' => [
			'feature' => 'has_offline_reservation_limits_settings',
			'key' => 'offline_reservation_daily_limit',
			'perDay' => true,
		],
		'refund_hour' => [
			'feature' => false,
			'key' => 'refund_time_limit',
			'perDay' => false,
		],
		'welcome_info' => [
			'feature' => false,
			'key' => 'first_login_message',
			'clubData' => true,
			'perDay' => false,
		],
		'extras:lounge' => [
			'custom' => true,
			'feature' => 'lounge',
		],
	];

	public function store(Request $request)
	{
		$settingsMorph = (new Setting())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('extra', 'admin')
			->where('model_type', $settingsMorph)
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

		$settingData = $this->settingsKeyTranslate[$request->input('key')] ?? false;

		if (!$settingData) {
			return response(['status' => 'ok', 'action' => 'old setting'], 200);
		}

		$data = $request->only(['created_at', 'value']);

		if (isset($settingData['custom']) && $settingData['custom']) {
			if ($settingData['feature'] === 'lounge') {
				$value = $request->input('value');
				$billiard = Game::where('name', 'billiard ')->first();

				$importedSettings = $assignedClub->model->settings()->updateOrCreate(
					[
						'feature_id' => $billiard->getFeaturesByType('slot_has_lounge')->first()->id,
						'key' => 'lounges_status',
					],
					[
						'value' => $value,
						'created_at' => $data['created_at'],
					]
				);

				ImportedModel::create([
					'model_type' => $settingsMorph,
					'model_id' => $importedSettings->id,
					'old_id' => $request->input('id'),
					'extra' => 'admin',
				]);

				$importedSettings = $assignedClub->model->settings()->updateOrCreate(
					[
						'feature_id' => $billiard->getFeaturesByType('has_widget_slots_selection')->first()
							->id,
						'key' => 'widget_slots_selection_status',
					],
					[
						'value' => !$value,
						'created_at' => $data['created_at'],
					]
				);

				ImportedModel::create([
					'model_type' => $settingsMorph,
					'model_id' => $importedSettings->id,
					'old_id' => $request->input('id'),
					'extra' => 'admin',
				]);

				return response(['status' => 'ok', 'action' => 'created'], 201);
			}
		}

		if (isset($settingData['perDay']) && $settingData['perDay']) {
			$value = $request->input('value');

			if ($settingData['custom'] ?? false) {
				if ($request->input('key') === 'res_max_minutes') {
					$value = (int) $value / 60;
				}
			}

			$data['value'] = [];

			for ($i = 0; $i < 7; $i++) {
				$data['value'][] = (string) $value;
			}
		}

		$data['key'] = $settingData['key'];

		if (isset($settingData['clubData']) && $settingData['clubData']) {
			$assignedClub->model->update([
				$settingData['key'] => $data['value'],
			]);
		} elseif ($settingData['feature']) {
			foreach ($assignedClub->model->games as $game) {
				if (isset($settingData['onlyGames']) && !in_array($game->name, $settingData['onlyGames'])) {
					continue;
				}

				$featureId = $game->getFeaturesByType($settingData['feature'])->first()->id;

				if ($featureId) {
					$data['feature_id'] = $featureId;

					$importedSettings = $assignedClub->model->settings()->updateOrCreate(
						[
							'feature_id' => $data['feature_id'],
							'key' => $data['key'],
						],
						[
							'value' => $data['value'],
							'created_at' => $data['created_at'],
						]
					);

					ImportedModel::create([
						'model_type' => $settingsMorph,
						'model_id' => $importedSettings->id,
						'old_id' => $request->input('id'),
						'extra' => 'admin',
					]);
				}
			}
		} else {
			$importedSettings = $assignedClub->model->settings()->updateOrCreate(
				[
					'feature_id' => null,
					'key' => $data['key'],
				],
				[
					'value' => $data['value'],
					'created_at' => $data['created_at'],
				]
			);

			ImportedModel::create([
				'model_type' => $settingsMorph,
				'model_id' => $importedSettings->id,
				'old_id' => $request->input('id'),
				'extra' => 'admin',
			]);
		}

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModels = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Setting())->getMorphClass())
			->where('extra', 'admin')
			->get();

		if (!$importModels->count()) {
			return $this->store($request);
		}

		$data = $request->only(['value', 'created_at']);
		$settingData = $this->settingsKeyTranslate[$request->input('key')] ?? false;

		if (!$settingData) {
			return response(['status' => 'ok', 'action' => 'old setting'], 200);
		}

		if (isset($settingData['custom']) && $settingData['custom']) {
			if ($settingData['feature'] === 'lounge') {
				$assignedClub = $importModels[0]->model->club;

				$value = $request->input('value');
				$billiard = Game::where('name', 'billiard ')->first();

				$assignedClub->settings()->updateOrCreate(
					[
						'feature_id' => $billiard->getFeaturesByType('slot_has_lounge')->first()->id,
						'key' => 'lounges_status',
					],
					[
						'value' => $value,
						'created_at' => $data['created_at'],
					]
				);
				$assignedClub->settings()->updateOrCreate(
					[
						'feature_id' => $billiard->getFeaturesByType('has_widget_slots_selection')->first()
							->id,
						'key' => 'widget_slots_selection_status',
					],
					[
						'value' => !$value,
						'created_at' => $data['created_at'],
					]
				);

				return response(['status' => 'ok', 'action' => 'updated'], 201);
			}

			if ($request->input('key') === 'res_max_minutes') {
				$data['value'] = (int) $data['value'] / 60;
			}
		}

		if (isset($settingData['perDay']) && $settingData['perDay']) {
			$value = $data['value'];

			$data['value'] = [];

			for ($i = 0; $i < 7; $i++) {
				$data['value'][] = (string) $value;
			}
		}

		$data['key'] = $settingData['key'];

		foreach ($importModels as $importModel) {
			$importModel->model->update($data);
		}
		$importModels[0]->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModels = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Setting())->getMorphClass())
			->where('extra', 'admin')
			->get();

		foreach ($importModels as $importModel) {
			$importModel->model->delete();
			$importModel->delete();
		}
		$importModels[0]->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
