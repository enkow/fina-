<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\ManagerEmailResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\ReservationTypeResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
	public function reservation(): Response
	{
		club()->flushCache();

		return Inertia::render('Club/Settings/ReservationSettings', [
			'games' => GameResource::collection(club()->games->load('features')),
			'settings' => Setting::retrieve(scope: 'club', clubId: clubId()),
			'managerEmails' => ManagerEmailResource::collection(club()->managerEmails),
			'announcements' => AnnouncementResource::collection(
				club()
					->announcements()
					->get()
			),
		]);
	}

	public function calendar(): Response
	{
		return Inertia::render('Club/Settings/CalendarSettings', [
			'settings' => Setting::retrieve(scope: 'club', clubId: clubId()),
			'reservationTypes' => ReservationTypeResource::collection(
				club()
					->reservationTypes()
					->paginate(200)
			),
		]);
	}

	public function update(UpdateSettingRequest $request, string $key): RedirectResponse|JsonResponse
	{
		if (
			!array_key_exists($key, Setting::getAvailableSettings()['club']) ||
			(Setting::getAvailableSettings()['club']['adminOnlyEdit'] ?? false) === true
		) {
			return redirect()->back();
		}
		if (isset(config("available-settings.club.$key")['prepareForValidation'])) {
			$request->merge([
				'value' => config("available-settings.club.$key.prepareForValidation")(
					$request->get('value')
				),
			]);
		}
		$request->validate(
			array_merge(
				['feature_id' => 'nullable|exists:features,id'],
				Setting::getAvailableSettings()['club'][$key]['validationRules'] ?? []
			)
		);

		if (
			(config("available-settings.club.$key.type") === 'file' ||
				Setting::getAvailableSettings()['club']['club_map']['type'] === 'file') &&
			$request->hasFile('value')
		) {
			$disk = Setting::getAvailableSettings()['club'][$key]['disk'];
			$value = $request['value']->storePublicly('/', ['disk' => $disk]);
		} elseif (
			(config("available-settings.club.$key.type") === 'file' ||
				Setting::getAvailableSettings()['club'][$key]['type'] === 'file') &&
			!$request->hasFile('value')
		) {
			$disk = Setting::getAvailableSettings()['club'][$key]['disk'];
			Storage::disk($disk)->delete(
				club()
					->settings()
					->where('key', $key)
					->whereNotNull('value')
					->first()->value
			);
			$value = null;
		} else {
			$value = $request->all()['value'];
		}

		club()
			->settings()
			->updateOrCreate(
				['key' => $key, 'feature_id' => $request->get('feature_id')],
				['value' => $value]
			);
		club()->flushCache();

		if (($request->all()['returnType'] ?? 'redirect') === 'json') {
			return response()->json();
		}
		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('settings.successfully-updated'),
			]);
	}
}
