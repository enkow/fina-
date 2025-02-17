<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessHelpItemRequest;
use App\Http\Resources\HelpItemResource;
use App\Http\Resources\HelpSectionResource;
use App\Models\HelpItem;
use App\Models\HelpSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HelpItemController extends Controller
{
	public function show(
		AccessHelpItemRequest $request,
		HelpSection $helpSection,
		HelpItem $helpItem
	): Response {
		return Inertia::render('Club/HelpItems/Show', [
			'helpSections' => HelpSectionResource::collection(
				club()
					->country->helpSections()
					->where('active', true)
					->get()
			),
			'helpSection' => new HelpSectionResource($helpSection),
			'helpItem' => new HelpItemResource($helpItem),
		]);
	}

	public function search(Request $request, HelpSection $helpSection): JsonResponse
	{
		$searchString = $request->get('search');

		return response()->json(
			HelpItemResource::collection(
				HelpItem::whereHas('helpSection', function ($query) {
					$query->where('country_id', club()->country_id);
					$query->where('active', true);
				})
					->where('active', true)
					->where(function ($query) use ($searchString) {
						$query->where('title', 'like', "%$searchString%");
						$query->orWhere('description', 'like', "%$searchString%");
						$query->orWhere('content', 'like', "%$searchString%");
					})
					->take(5)
					->get()
			)
		);
	}
}
