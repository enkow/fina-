<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessSetRequest;
use App\Http\Requests\Club\CreateSetRequest;
use App\Http\Requests\Club\StoreSetRequest;
use App\Http\Requests\Club\ToggleActiveSetRequest;
use App\Http\Requests\Club\UpdateSetRequest;
use App\Http\Resources\SetResource;
use App\Models\Set;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SetController extends Controller
{
	public function index(): Response
	{
		$sets = SetResource::collection(
			club()
				->sets()
				->with('creator')
				->withCount([
					'reservationSlots' => fn($q) => $q
						->whereDate('reservation_slots.start_at', now())
						->active(),
				])
				->paginate(request()['perPage']['sets'] ?? 10)
		);

		return Inertia::render('Club/Sets/Index', compact(['sets']));
	}

	public function store(StoreSetRequest $request): RedirectResponse
	{
		$storeArray = $request->only(['name', 'description', 'price', 'quantity']);
		foreach (['photo', 'mobile_photo'] as $fileKey) {
			if ($request->hasFile($fileKey)) {
				$storeArray[$fileKey] = $request
					->file($fileKey)
					?->storePublicly('/', ['disk' => 'setImages']);
			}
		}
		club()
			->sets()
			->create($storeArray);

		return redirect()
			->route('club.sets.index')
			->with('message', [
				'type' => 'success',
				'content' => __('set.successfully-stored'),
			]);
	}

	public function create(CreateSetRequest $request): Response
	{
		return Inertia::render('Club/Sets/Create');
	}

	public function edit(AccessSetRequest $request, Set $set): Response
	{
		return Inertia::render('Club/Sets/Edit', [
			'set' => new SetResource($set),
		]);
	}

	public function update(UpdateSetRequest $request, Set $set): RedirectResponse
	{
		$updateArray = $request->only(['name', 'description', 'price', 'quantity']);
		foreach (['photo', 'mobile_photo'] as $fileKey) {
			if ($request->hasFile($fileKey)) {
				$updateArray[$fileKey] = $request
					->file($fileKey)
					?->storePublicly('/', ['disk' => 'setImages']);
			}
		}
		$set->update($updateArray);

		return redirect()
			->route('club.sets.index')
			->with('message', [
				'type' => 'info',
				'content' => __('set.successfully-updated'),
			]);
	}

	public function destroy(AccessSetRequest $request, Set $set): RedirectResponse
	{
		$set->delete();

		return redirect()
			->route('club.sets.index')
			->with('message', [
				'type' => 'info',
				'content' => __('set.successfully-destroyed'),
			]);
	}

	public function toggleActive(ToggleActiveSetRequest $request, Set $set): RedirectResponse
	{
		$set->active = !$set->active;
		$set->save();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('set.successfully-updated'),
			]);
	}
}
