<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessSpecialOfferRequest;
use App\Http\Requests\Club\StoreSpecialOfferRequest;
use App\Http\Resources\SpecialOfferResource;
use App\Models\Club;
use App\Models\SpecialOffer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SpecialOfferController extends Controller
{
	public function __construct()
	{
		$this->middleware('isUserType:manager', ['only' => ['edit', 'update', 'destroy', 'toggleActive']]);
	}

	public function index(): Response
	{
		$specialOffers = SpecialOfferResource::collection(
			club()
				->specialOffers()
				->sortable('special_offers', ['name', 'active'])
				->with('creator', 'game')
				->paginate(request()['perPage']['special_offers'] ?? 10)
		);

		return Inertia::render('Club/SpecialOffers/Index', compact(['specialOffers']));
	}

	public function store(StoreSpecialOfferRequest $request): RedirectResponse
	{
		$createData = $request->only([
			'active',
			'active_by_default',
			'game_id',
			'value',
			'name',
			'description',
			'active_week_days',
			'time_range_type',
			'time_range',
			'duration',
			'slots',
			'when_applies',
			'applies_default',
			'when_not_applies',
		]);

		if (!$createData['applies_default']) {
			$createData['when_not_applies'] = [];
		} else {
			$createData['when_applies'] = [];
		}

		if ($request->hasFile('photo')) {
			$createData['photo'] = $request->file('photo')?->storePublicly('/', ['disk' => 'setImages']);
		}

		club()
			->specialOffers()
			->create($createData);

		return redirect()
			->route('club.special-offers.index')
			->with('message', [
				'type' => 'success',
				'content' => __('special-offer.successfully-stored'),
			]);
	}

	public function create(): Response
	{
		return Inertia::render('Club/SpecialOffers/Create');
	}

	public function edit(AccessSpecialOfferRequest $request, SpecialOffer $specialOffer): Response
	{
		return Inertia::render('Club/SpecialOffers/Edit', [
			'specialOffer' => new SpecialOfferResource($specialOffer->load('game')),
		]);
	}

	public function destroy(AccessSpecialOfferRequest $request, SpecialOffer $specialOffer): RedirectResponse
	{
		$specialOffer->delete();

		return redirect()
			->route('club.special-offers.index')
			->with('message', [
				'type' => 'info',
				'content' => __('special-offer.successfully-destroyed'),
			]);
	}

	public function toggleActive(
		AccessSpecialOfferRequest $request,
		SpecialOffer $specialOffer
	): RedirectResponse {
		$specialOffer->update([
			'active' => !$specialOffer->active,
		]);

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('special-offer.successfully-updated'),
			]);
	}

	public function update(StoreSpecialOfferRequest $request, SpecialOffer $specialOffer): RedirectResponse
	{
		$updateData = $request->only([
			'active',
			'active_by_default',
			'game_id',
			'value',
			'name',
			'description',
			'active_week_days',
			'time_range_type',
			'time_range',
			'duration',
			'slots',
			'when_applies',
			'applies_default',
			'when_not_applies',
		]);

		if (!$updateData['applies_default']) {
			$updateData['when_not_applies'] = [];
		} else {
			$updateData['when_applies'] = [];
		}

		if ($request->hasFile('photo')) {
			$updateData['photo'] = $request->file('photo')?->storePublicly('/', ['disk' => 'setImages']);
		}

		$specialOffer->update($updateData);

		return redirect()
			->route('club.special-offers.index')
			->with('message', [
				'type' => 'info',
				'content' => __('special-offer.successfully-updated'),
			]);
	}

	public function clone(SpecialOffer $specialOffer)
	{
		$specialOfferArray = $specialOffer->toArray();
		unset(
			$specialOffer['id'],
			$specialOffer['creator_id'],
			$specialOffer['created_at'],
			$specialOffer['updated_at']
		);
		$name = $specialOfferArray['name'];
		$specialOfferArray['name'] .= '-' . __('discount-code.copy');
		$specialOfferArray['active'] = false;
		$i = 2;
		while (SpecialOffer::where('name', $specialOfferArray['name'])->exists()) {
			$specialOfferArray['name'] = $name . '-' . __('special-offer.copy') . $i;
			$i++;
		}
		club()
			->specialOffers()
			->create($specialOfferArray);

		return redirect()
			->route('club.special-offers.index')
			->with('message', [
				'type' => 'info',
				'content' => __('special-offer.successfully-cloned'),
			]);
	}
}
