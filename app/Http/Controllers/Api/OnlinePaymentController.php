<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class OnlinePaymentController extends Controller
{
	public function webhook(Request $request, $type)
	{
		$class = PaymentMethod::CHILD_TYPES[$type] ?? null;

		if (!$class || !method_exists($class, 'webhook')) {
			return response()->json(
				[
					'error' => 'Invalid payment method type',
				],
				400
			);
		}

		return $class::webhook($request);
	}
}
