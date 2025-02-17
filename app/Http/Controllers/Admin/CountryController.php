<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCountryRequest;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Custom\Fakturownia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class CountryController extends Controller
{
	public function index(): Response
	{
		$countries = CountryResource::collection(
			Country::withCount('clubs')
				->orderByDesc('active')
				->orderBy('code')
				->paginate(50)
		);

		return Inertia::render('Admin/Countries/Index', compact(['countries']));
	}

	public function edit(Country $country): Response
	{
		return Inertia::render('Admin/Countries/Edit', [
			'country' => new CountryResource($country),
			'availableLocales' => config('app.locales'),
			'availableTimezones' => config('timezones'),
		]);
	}

	public function toggleActive(Country $country): RedirectResponse
	{
		$activeCountries = Country::where('active', true)->get();
		if (
			!$country->active === false &&
			count($activeCountries) === 1 &&
			$activeCountries[0]->id === $country->id
		) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => 'Nie możesz wyłączyć wszystkich dostępnych krajów',
				]);
		}

		$countryData = [
			'active' => !$country->active,
		];

		$country->update($countryData);
		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => $countryData,
			]);
	}

	public function update(UpdateCountryRequest $request, Country $country): RedirectResponse
	{
		$countryData = $request->only(['active', 'locale', 'timezone', 'currency', 'dialing_code']);

		$country->update($countryData);
		Cache::flush();

		return redirect()
			->route('admin.countries.index')
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano kraj',
			]);
	}

	public function changeAdminCountryId(Request $request): RedirectResponse
	{
		$request->validate([
			'country' => 'exists:countries,id',
		]);

		auth()
			->user()
			?->update([
				'country_id' => $request->country,
			]);
		Cache::forget('user:' . auth()->user()->id . ':data');

		return redirect()->back();
	}
}
