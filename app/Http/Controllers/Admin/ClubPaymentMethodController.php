<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Club;
use App\Models\PaymentMethods\Stripe;
use App\Models\PaymentMethods\Tpay;
use Illuminate\Http\Request;

class ClubPaymentMethodController extends Controller
{
	public function paymentMethodSelect(Request $request, Club $club)
	{
		$paymentMethodName = $request->value;

		$paymentMethods = [
			'tpay' => Tpay::class,
			'stripe' => Stripe::class,
		];

		if (!isset($paymentMethods[$paymentMethodName]) || !$paymentMethods[$paymentMethodName]) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => 'Błędna metoda',
				]);
		}

		$paymentMethods[$paymentMethodName]::enable($club);

		$club->flushCache();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Edytowano metode płatności',
			]);
	}

	public function paymentMethodConnect(Request $request, Club $club, string $payment_method)
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

		return $paymentMethods[$payment_method]::connect($request, $club);
	}

	public function paymentMethodDisconnect(Request $request, Club $club, string $payment_method)
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

		return $paymentMethods[$payment_method]::disconnect($request, $club);
	}

	public function update(Request $request, Club $club, string $payment_method, string $field)
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

		$club
			->paymentMethods()
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
