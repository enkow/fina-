<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessReservationTypeRequest;
use App\Http\Requests\Club\StoreReservationTypeRequest;
use App\Http\Requests\Club\UpdateReservationTypeRequest;
use App\Models\ReservationType;
use Illuminate\Http\RedirectResponse;

class ReservationTypeController extends Controller
{
	public function store(StoreReservationTypeRequest $request): RedirectResponse
	{
		club()
			->reservationTypes()
			->create($request->only(['name', 'color']));

		return redirect()
			->back()
			->with('message', [
				'type' => 'success',
				'content' => __('reservation-type.successfully-stored'),
			]);
	}

	public function update(
		UpdateReservationTypeRequest $request,
		ReservationType $reservationType
	): RedirectResponse {
		$data = $request->all();
		if (isset($data['color'])) {
			$reservationType->color = $data['color'];
		}
		if (isset($data['name'])) {
			$reservationType->name = $data['name'];
		}
		$reservationType->save();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('reservation-type.successfully-updated'),
			]);
	}

	public function destroy(
		AccessReservationTypeRequest $request,
		ReservationType $reservationType
	): RedirectResponse {
		$reservationType->delete();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('reservation-type.successfully-destroyed'),
			]);
	}
}
