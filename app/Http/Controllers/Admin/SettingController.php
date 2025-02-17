<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Models\PaymentMethods\Stripe;
use App\Models\PaymentMethods\Tpay;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
	public function index(): Response
	{
		$preparePaymentMethods = PaymentMethod::whereNull('club_id')
			->whereIn('type', ['tpay'])
			->get();

		return Inertia::render('Admin/Settings/Index', [
			'settings' => Setting::retrieve(),
			'paymentMethods' => PaymentMethodResource::collection($preparePaymentMethods),
		]);
	}

	public function update(Request $request, $key): RedirectResponse
	{
		$availableSettings = Setting::getAvailableSettings();
		if (!isset($availableSettings['global'][$key])) {
			return redirect()->back();
		}
		$request->validate($availableSettings['global'][$key]['validationRules'] ?? []);

		if ($availableSettings['global'][$key]['type'] === 'file' && $request->hasFile('value')) {
			$value = $request['value']->storePublicly('/', [
				'disk' => $availableSettings['global'][$key]['disk'],
			]);
		} elseif ($availableSettings['global'][$key]['type'] === 'file' && !$request->hasFile('value')) {
			Storage::disk($availableSettings['global'][$key]['disk'])->delete(
				Setting::whereNull('club_id')
					->where('key', $key)
					->first()->value
			);
			$value = null;
		} else {
			$value = $request->get('value');
		}

		Setting::updateOrCreate(
			[
				'key' => $key,
				'club_id' => null,
				'feature_id' => $request->get('feature_id', null),
			],
			['value' => $value]
		);

		Cache::forget('global_settings');

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('settings.successfully-updated'),
			]);
	}

	public function updatePaymentMethod(Request $request, string $payment_method, string $field)
	{
		$paymentMethods = [
			'tpay' => Tpay::class,
			'stripe' => Stripe::class,
		];

		if (!isset($paymentMethods[$payment_method]) || !$paymentMethods[$payment_method]) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => 'Błędna metoda',
				]);
		}

		$availableFields = ['fee_percentage', 'fee_fixed'];

		if (!in_array($field, $availableFields)) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => 'Błędne pole',
				]);
		}

		$amountFields = ['fee_fixed', 'fee_percentage'];

		$value = $request->value;

		if (in_array($field, $amountFields)) {
			$value = amountToSmallestUnits($value);
		}

		PaymentMethod::whereNull('club_id')
			->where('type', $payment_method)
			->update([
				$field => $value,
			]);

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Edytowano metode płatności',
			]);
	}
}
