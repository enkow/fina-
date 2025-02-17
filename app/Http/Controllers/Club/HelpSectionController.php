<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessHelpSectionRequest;
use App\Http\Resources\HelpSectionResource;
use App\Models\HelpSection;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HelpSectionController extends Controller
{
	public function index(): RedirectResponse
	{
		return redirect()->route('club.help-sections.show', [
			'help_section' => club()
				->country->helpSections()
				->where('active', 1)
				->first(),
		]);
	}

	public function show(AccessHelpSectionRequest $request, HelpSection $helpSection): Response
	{
		return Inertia::render('Club/HelpSections/Show', [
			'helpSections' => HelpSectionResource::collection(
				club()
					->country->helpSections()
					->where('active', true)
					->get()
			),
			'helpSection' => new HelpSectionResource($helpSection->load('helpItems')),
		]);
	}
}
