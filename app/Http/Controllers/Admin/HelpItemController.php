<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHelpItemRequest;
use App\Http\Requests\Admin\UpdateHelpItemRequest;
use App\Http\Resources\HelpItemImageResource;
use App\Http\Resources\HelpItemResource;
use App\Http\Resources\HelpSectionResource;
use App\Models\HelpItem;
use App\Models\HelpSection;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HelpItemController extends Controller
{
	public function index(HelpSection $helpSection): Response
	{
		$helpItems = HelpItemResource::collection(
			$helpSection
				->helpItems()
				->orderByDesc('weight')
				->paginate(20)
		);

		return Inertia::render('Admin/HelpItems/Index', [
			'helpItems' => $helpItems,
			'helpSection' => new HelpSectionResource($helpSection),
		]);
	}

	public function store(StoreHelpItemRequest $request, HelpSection $helpSection): RedirectResponse
	{
		$storeArray = $request->only(['video_url', 'title', 'description', 'content', 'weight']);
		if ($request->hasFile('thumbnail')) {
			$storeArray['thumbnail'] = $request['thumbnail']->storePublicly('/', [
				'disk' => 'helpItemThumbnails',
			]);
		}
		$helpSection->helpItems()->create($storeArray);

		return redirect()
			->route('admin.help-sections.help-items.index', [
				'help_section' => $helpSection,
			])
			->with('message', [
				'type' => 'success',
				'content' => 'Dodano wpis',
			]);
	}

	public function create(HelpSection $helpSection): Response
	{
		return Inertia::render('Admin/HelpItems/Create', [
			'helpSection' => new HelpSectionResource($helpSection),
		]);
	}

	public function edit(HelpSection $helpSection, HelpItem $helpItem): mixed
	{
		return Inertia::render('Admin/HelpItems/Edit', [
			'helpSection' => new HelpSectionResource($helpSection),
			'helpItem' => new HelpItemResource($helpItem->load('helpItemImages')),
			'helpItemImages' => HelpItemImageResource::collection($helpItem->helpItemImages()->paginate(10)),
		]);
	}

	public function destroy(HelpSection $helpSection, HelpItem $helpItem): RedirectResponse
	{
		$helpItem->delete();

		return redirect()
			->route('admin.help-sections.help-items.index', [
				'help_section' => $helpSection,
				'help_item' => $helpItem,
			])
			->with('message', [
				'type' => 'info',
				'content' => 'Usunięto wpis',
			]);
	}

	public function toggleActive(HelpSection $helpSection, HelpItem $helpItem): RedirectResponse
	{
		$helpItem->update([
			'active' => !$helpItem->active,
		]);

		return redirect()
			->route('admin.help-sections.help-items.index', [
				'help_section' => $helpSection,
				'help_item' => $helpItem,
			])
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano status wpisu',
			]);
	}

	public function update(
		UpdateHelpItemRequest $request,
		HelpSection $helpSection,
		HelpItem $helpItem
	): RedirectResponse {
		$updateArray = $request->only(['video_url', 'title', 'description', 'content', 'weight']);
		if ($request->hasFile('thumbnail')) {
			$updateArray['thumbnail'] = $request['thumbnail']->storePublicly('/', [
				'disk' => 'helpItemThumbnails',
			]);
		}
		$helpItem->update($updateArray);

		return redirect()
			->route('admin.help-sections.help-items.index', [
				'help_section' => $helpSection,
				'help_item' => $helpItem,
			])
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano wpis',
			]);
	}
}
