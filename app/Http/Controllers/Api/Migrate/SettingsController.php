<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ImportedModel;
use App\Models\Setting;
use App\Models\Club;

class SettingsController extends Controller
{
	private array $settingsKeyTranslate = [
		'res_email_sent' => [
			'key' => 'email_notifications_status',
		],
		'res_sms_sent' => [
			'key' => 'sms_notifications_status',
		],
		'res_equal_hours' => [
			'key' => 'full_hour_start_reservations_status',
		],
		'widget_max_days_calendar' => [
			'key' => 'reservation_max_advance_time',
		],
		'bowl_res_email' => [
			'custom' => true,
			'onlyGames' => ['bowling'],
		],
		'pool_res_email' => [
			'custom' => true,
			'onlyGames' => ['billiard'],
		],
		'tables_res_email' => [
			'custom' => true,
			'onlyGames' => ['numbered_tables', 'unnumbered_tables'],
		],
		'no_scroll_table' => [
			'feature' => 'has_visible_calendar_slots_quantity_setting',
			'key' => 'visible_calendar_slots_quantity',
			'onlyGames' => ['billiard', 'bowling'],
		],
		'calendar_interval' => [
			'key' => 'calendar_time_scale',
		],
		'searcher_qty' => [
			'feature' => 'has_offline_reservation_limits_settings',
			'key' => 'offline_reservation_slot_limit',
			'json' => true,
			'onlyGames' => ['billiard'],
		],
		'min_time_before_reservation' => [
			'key' => 'reservation_min_advance_time',
			'json' => true,
		],
		'max_reservation_time' => [
			'feature' => 'has_widget_duration_limit_setting',
			'key' => 'widget_duration_limit',
			'json' => true,
			'onlyGames' => ['billiard', 'bowling'],
		],
		'bowl_extra' => [
			'feature' => 'price_per_person',
			'key' => 'price_per_person',
			'onlyGames' => ['bowling'],
		],
	];

	public function store(Request $request)
	{
		$settingsMorph = (new Setting())->getMorphClass();
		$importModel = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', $settingsMorph)
			->where('extra', 'club')
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

		$settingsData = $this->settingsKeyTranslate[$request->input('key')] ?? null;

		if ($settingsData === null) {
			return response(['status' => 'ok', 'action' => 'old setting'], 200);
		}

		$data = $request->only(['value', 'created_at']);

		if (isset($settingsData['json']) && $settingsData['json']) {
			$data['value'] = json_decode($data['value']);
		}

		if (isset($settingsData['custom']) && $settingsData['custom']) {
			$value = str_replace(' ', '', $data['value']);
			$emails = explode(',', $value);
			$emailsInDb = [];
			$game = null;

			foreach ($assignedClub->model->managerEmails as $managerEmail) {
				if (!in_array($managerEmail->game->name, $settingsData['onlyGames'] ?? [])) {
					continue;
				}

				if (!in_array($managerEmail->email, $emails)) {
					$managerEmail->delete();
				} else {
					$emailsInDb[] = $managerEmail->email;
				}
			}

			foreach ($assignedClub->model->games as $_game) {
				if (in_array($_game->name, $settingsData['onlyGames'])) {
					$game = $_game;
				}
			}

			if ($game === null) {
				return response(['status' => 'undefined game'], 201);
			}

			foreach (array_diff($emails, $emailsInDb) as $email) {
				$assignedClub->model->managerEmails()->create([
					'game_id' => $game->id,
					'email' => $email,
				]);
			}
		} else {
			$data['key'] = $settingsData['key'];

			if (isset($settingsData['feature']) && $settingsData['feature']) {
				foreach ($assignedClub->model->games as $game) {
					if (
						isset($settingsData['onlyGames']) &&
						!in_array($game->name, $settingsData['onlyGames'])
					) {
						continue;
					}

					$featureId = $game->getFeaturesByType($settingsData['feature'])->first()->id;

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
							'extra' => 'club',
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
					'extra' => 'club',
				]);
			}
		}

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importModels = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Setting())->getMorphClass())
			->where('extra', 'club')
			->get();

		if (!$importModels->count()) {
			return $this->store($request);
		}

		$data = $request->only(['value', 'created_at']);

		$settingData = $this->settingsKeyTranslate[$request->input('key')] ?? false;

		if (isset($settingData['json']) && $settingData['json']) {
			$data['value'] = json_decode($data['value']);
		}

		if (!$settingData) {
			return response(['status' => 'ok', 'action' => 'old setting'], 200);
		}

		$data['key'] = $settingData['key'];
		info(json_encode($importModels->first()->model));
		info(json_encode($data));

		foreach ($importModels as $importModel) {
			$importModel->model->update($data);
		}
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Setting())->getMorphClass())
			->first();

		$importModel->model->delete();
		$importModel->delete();
		$importModel->model->club->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
