<?php

namespace App\Http\Controllers\Club;

use App\Enums\OnlinePayments;
use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Response;
use Illuminate\Support\Facades\Cache;

class OnlinePaymentController extends Controller
{
	public function index(): Response
	{
		$club = Club::with(['paymentMethod', 'country'])
			->where('online_payments_enabled', OnlinePayments::External)
			->findOrFail(auth()->user()->club_id);

		return inertia('Club/OnlinePayments/Index', [
			'requiredType' => $club->country->payment_method_type,

			'current' => $club->paymentMethod,
		]);
	}

	public function connect(Request $request, string $type)
	{
		$class = PaymentMethod::CHILD_TYPES[$type] ?? null;

		if (!$class || !method_exists($class, 'connect')) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => __('online-payments.invalid-method-type'),
				]);
		}

		$club = Club::with('country')->findOrFail($request->user()->club_id);

		if ($club->online_payments_enabled !== OnlinePayments::External) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => __('online-payments.invalid-method-type'),
				]);
		}

		return $class::connect($request, $club);
	}

	public function disconnect(Request $request, string $type)
	{
		$class = PaymentMethod::CHILD_TYPES[$type] ?? null;

		if (!$class || !method_exists($class, 'disconnect')) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => __('online-payments.invalid-method-type'),
				]);
		}

		$club = Club::with('country')->findOrFail($request->user()->club_id);

		if ($club->online_payments_enabled !== OnlinePayments::External) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => __('online-payments.invalid-method-type'),
				]);
		}

		return $class::disconnect($request, $club);
	}

	public function return(Request $request, string $type)
	{
		$class = PaymentMethod::CHILD_TYPES[$type] ?? null;

		if (!$class || !method_exists($class, 'return')) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => __('online-payments.invalid-method-type'),
				]);
		}

		$club = Club::with('country')->findOrFail($request->user()->club_id);

		if ($club->online_payments_enabled !== OnlinePayments::External) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => __('online-payments.invalid-method-type'),
				]);
		}

		return $class::return($request, $club);
	}
}
