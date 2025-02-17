<?php

namespace App\Http\Controllers\Club;

use App\Custom\Timezone;
use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessDiscountCodeRequest;
use App\Http\Requests\Club\StoreDiscountCodeRequest;
use App\Http\Resources\DiscountCodeResource;
use App\Models\DiscountCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscountCodeController extends Controller
{
	public function __construct()
	{
		$this->middleware('isUserType:manager', ['only' => ['edit', 'update', 'destroy', 'toggleActive']]);
	}

	public function index(Request $request): Response
	{
		$discountCodes = DiscountCodeResource::collection(
			club()
				->discountCodes()
				->with('game', 'creator', 'reservations.reservationSlots')
				->withCount('reservations')
				->sortable('discount_codes', ['code', 'active', 'start_at', 'end_at'])
				->searchable('discount_codes', ['code'])
				->paginate(request()['perPage']['discount_codes'] ?? 10)
		);

		return Inertia::render('Club/DiscountCodes/Index', compact(['discountCodes']));
	}

	public function store(StoreDiscountCodeRequest $request): RedirectResponse
	{
		$createData = $request->only([
			'active',
			'game_id',
			'code',
			'type',
			'value',
			'code_quantity',
			'code_quantity_per_customer',
			'start_at',
			'end_at',
		]);

		if (!$createData['start_at']) {
			$createData['start_at'] = now();
		}

		club()
			->discountCodes()
			->create($createData);

		return redirect()
			->route('club.discount-codes.index')
			->with('message', [
				'type' => 'success',
				'content' => __('discount-code.successfully-stored'),
			]);
	}

	public function create(): Response
	{
		return Inertia::render('Club/DiscountCodes/Create', [
            'shouldShowCodePerUser' => club()->customer_registration_required,
        ]);
	}

	public function edit(AccessDiscountCodeRequest $request, DiscountCode $discountCode): mixed
	{
		return Inertia::render('Club/DiscountCodes/Edit', [
            'shouldShowCodePerUser' => club()->customer_registration_required,
			'discountCode' => new DiscountCodeResource($discountCode->load('game')),
		]);
	}

	public function destroy(AccessDiscountCodeRequest $request, DiscountCode $discountCode): RedirectResponse
	{
		$discountCode->delete();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('discount-code.successfully-destroyed'),
			]);
	}

	public function toggleActive(
		AccessDiscountCodeRequest $request,
		DiscountCode $discountCode
	): RedirectResponse {
		$discountCode->update([
			'active' => !$discountCode->active,
		]);

		return redirect()->back();
	}

	public function update(StoreDiscountCodeRequest $request, DiscountCode $discountCode): RedirectResponse
	{
		$updateData = $request->only([
			'active',
			'game_id',
			'code',
			'type',
			'value',
			'code_quantity',
			'code_quantity_per_customer',
			'start_at',
			'end_at',
		]);

		if (!$updateData['start_at']) {
			$updateData['start_at'] = now();
		}

		$discountCode->update($updateData);

		return redirect()
			->route('club.discount-codes.index')
			->with('message', [
				'type' => 'info',
				'content' => __('discount-code.successfully-updated'),
			]);
	}

	public function clone(DiscountCode $discountCode): RedirectResponse
	{
		$discountCodeArray = $discountCode->toArray();
		unset(
			$discountCode['id'],
			$discountCode['creator_id'],
			$discountCode['created_at'],
			$discountCode['updated_at']
		);
		$code = $discountCodeArray['code'];
		$discountCodeArray['code'] .= '-' . __('discount-code.copy');
		$discountCodeArray['active'] = false;
		$discountCodeArray['start_at'] = Timezone::convertToLocal($discountCodeArray['start_at']);
		$discountCodeArray['end_at'] = Timezone::convertToLocal($discountCodeArray['end_at']);
		$i = 2;
		while (DiscountCode::where('code', $discountCodeArray['code'])->exists()) {
			$discountCodeArray['code'] = $code . '-' . __('discount-code.copy') . $i;
			$i++;
		}
		club()
			->discountCodes()
			->create($discountCodeArray);

		return redirect()
			->route('club.discount-codes.index')
			->with('message', [
				'type' => 'info',
				'content' => __('discount-code.successfully-cloned'),
			]);
	}
}
