<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlotResource;
use App\Models\Club;
use App\Models\Game;
use App\Models\Slot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlotController extends Controller
{
	public function index(Request $request, Club $club): JsonResponse
	{
		return response()->json(
			SlotResource::collection(
				Slot::getAvailable(
					array_merge($request->all(), [
						'active' => true,
						'vacant' => true,
						'club_id' => $club->id,
						'parent_slot_id' => 0,
					])
				)
			)
		);
	}
}
