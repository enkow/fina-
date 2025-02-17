<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiscountCodeResource;
use App\Models\Club;
use App\Models\Customer;
use App\Models\DiscountCode;
use Illuminate\Http\JsonResponse;

class DiscountCodeController extends Controller
{
	public function show(Club $club, string $discountCode): JsonResponse
	{
		$result = DiscountCode::where('code', $discountCode)
			->where(function ($query) {
				$query->where('game_id', request()->get('game_id'))->orWhereNull('game_id');
			})
			->where('club_id', $club->id)
			->first();
		$customerId = Customer::find(session()->get('customer_id', null))?->id ?? null;

		if (empty($result) || !$result->isAvailable($customerId)) {
			abort(404);
		}
		return response()->json(new DiscountCodeResource($result));
	}
}
