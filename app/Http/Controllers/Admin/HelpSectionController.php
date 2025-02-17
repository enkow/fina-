<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CopyHelpSectionRequest;
use App\Http\Requests\Admin\StoreHelpSectionRequest;
use App\Http\Resources\HelpSectionResource;
use App\Models\Country;
use App\Models\HelpSection;
use App\Services\HelpSectionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HelpSectionController extends Controller
{
	public function index(): Response
	{
		$helpSections = HelpSectionResource::collection(
			HelpSection::where('country_id', auth()->user()->country_id)
				->orderByDesc('weight')
				->paginate(20)
		);

		return Inertia::render('Admin/HelpSections/Index', compact(['helpSections']));
	}

	public function store(StoreHelpSectionRequest $request): RedirectResponse
	{
		HelpSection::create(
			array_merge($request->only(['title', 'description', 'weight']), [
				'country_id' => auth()->user()->country_id,
			])
		);

		return redirect()
			->route('admin.help-sections.index')
			->with('message', [
				'type' => 'success',
				'content' => 'Dodano sekcje pomocy',
			]);
	}

	public function create(): Response
	{
		return Inertia::render('Admin/HelpSections/Create');
	}

	public function edit(HelpSection $helpSection): Response
	{
		return Inertia::render('Admin/HelpSections/Edit', [
			'helpSection' => new HelpSectionResource($helpSection),
		]);
	}

	public function destroy(HelpSection $helpSection): RedirectResponse
	{
		$helpSection->delete();

		return redirect()
			->route('admin.help-sections.index')
			->with('message', [
				'type' => 'info',
				'content' => 'Usunięto sekcje pomocy',
			]);
	}

	public function toggleActive(HelpSection $helpSection): RedirectResponse
	{
		$helpSection->update([
			'active' => !$helpSection->active,
		]);

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano status sekcji',
			]);
	}

	public function update(StoreHelpSectionRequest $request, HelpSection $helpSection): RedirectResponse
	{
		$helpSection->update($request->only(['title', 'description', 'weight']));

		return redirect()
			->route('admin.help-sections.index')
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano sekcje pomocy',
			]);
	}

	public function copy(CopyHelpSectionRequest $request): RedirectResponse
	{
		$from = Country::find($request->all()['from']);
		$to = Country::find($request->all()['to']);

		foreach ($to->helpSections as $helpSection) {
			$helpSection->delete();
		}
		$to->unsetRelation('helpSections');
		$to->copyHelpSections($from);

		Cache::flush();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Skopiowano sekcje pomocy',
			]);
	}
}
