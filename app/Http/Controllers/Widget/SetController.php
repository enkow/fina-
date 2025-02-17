<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Http\Resources\SetResource;
use App\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetController extends Controller
{
	public function index(Request $request, Club $club): JsonResponse
	{
		$sets = $club
			->sets()
			->where('active', true)
			->withCount([
				'reservationSlots' => fn($query) => $query->whereDate(
					'reservation_slots.start_at',
					$request->get('date')
				),
			])
			->get();
		$sets = SetResource::collection($sets);

		return response()->json($sets);
	}
}
