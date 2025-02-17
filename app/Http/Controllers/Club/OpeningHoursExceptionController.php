<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessOpeningHoursExceptionRequest;
use App\Http\Requests\Club\CreateOpeningHoursExceptionRequest;
use App\Http\Requests\Club\DestroyOpeningHoursExceptionRequest;
use App\Http\Requests\Club\StoreOpeningHoursExceptionRequest;
use App\Http\Requests\Club\UpdateOpeningHoursExceptionRequest;
use App\Http\Resources\OpeningHoursExceptionResource;
use App\Models\OpeningHoursException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OpeningHoursExceptionController extends Controller
{
	public function store(StoreOpeningHoursExceptionRequest $request): RedirectResponse
	{
		club()
			->openingHoursExceptions()
			->create(
				$request->only([
					'start_at',
					'end_at',
					'club_start',
					'club_end',
					'club_closed',
					'reservation_start',
					'reservation_end',
					'reservation_closed',
				])
			);

		return redirect()
			->route('club.opening-hours.show')
			->with('message', [
				'type' => 'success',
				'content' => __('opening-hours-exception.successfully-stored'),
			]);
	}

	public function create(CreateOpeningHoursExceptionRequest $request): Response
	{
		return Inertia::render('Club/OpeningHoursExceptions/Create');
	}

	public function edit(
		AccessOpeningHoursExceptionRequest $request,
		OpeningHoursException $openingHoursException
	): Response {
		return Inertia::render('Club/OpeningHoursExceptions/Edit', [
			'openingHoursException' => new OpeningHoursExceptionResource($openingHoursException),
		]);
	}

	public function update(
		UpdateOpeningHoursExceptionRequest $request,
		OpeningHoursException $openingHoursException
	): RedirectResponse {
		$openingHoursException->update(
			$request->only([
				'start_at',
				'end_at',
				'club_start',
				'club_end',
				'club_closed',
				'reservation_start',
				'reservation_end',
				'reservation_closed',
			])
		);

		return redirect()
			->route('club.opening-hours.show')
			->with('message', [
				'type' => 'info',
				'content' => __('opening-hours-exception.successfully-updated'),
			]);
	}

	public function destroy(
		DestroyOpeningHoursExceptionRequest $request,
		OpeningHoursException $openingHoursException
	): RedirectResponse {
		$openingHoursException->delete();

		return redirect()
			->route('club.opening-hours.show')
			->with('message', [
				'type' => 'info',
				'content' => __('opening-hours-exception.successfully-destroyed'),
			]);
	}
}
