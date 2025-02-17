<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\UpdateOpeningHoursRequest;
use App\Http\Resources\OpeningHoursExceptionResource;
use App\Http\Resources\OpeningHoursResource;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OpeningHoursController extends Controller
{
	public function show(): Response
	{
		$openingHours = OpeningHoursResource::collection(club()->openingHours);

		$openingHoursExceptions = OpeningHoursExceptionResource::collection(
			club()
				->openingHoursExceptions()
				->with('creator')
				->paginate(request()['perPage']['opening_hours_exceptions'] ?? 10)
		);

		return Inertia::render('Club/OpeningHours/Show', compact(['openingHours', 'openingHoursExceptions']));
	}

	public function update(UpdateOpeningHoursRequest $request): RedirectResponse
	{
		club()
			->openingHours()
			->where('day', $request->input('day'))
			->update(
				$request->only([
					'club_start',
					'club_end',
					'club_closed',
					'reservation_start',
					'reservation_end',
					'reservation_closed',
				])
			);
		club()->flushCache();

		return redirect()
			->route('club.opening-hours.show')
			->with('message', [
				'type' => 'info',
				'content' => __('opening-hours.successfully-updated'),
			]);
	}
}
