<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTranslationRequest;
use App\Http\Requests\CopyTranslationsRequest;
use App\Http\Resources\FeatureResource;
use App\Http\Resources\GameResource;
use App\Models\Country;
use App\Models\Feature;
use App\Models\Game;
use App\Models\Translation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TranslationController extends Controller
{
	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 */
	public function index(): Response|RedirectResponse
	{
		$entityName = match (true) {
			request()?->get('game_id') > 0 => Game::find(request()?->get('game_id'))->name,
			request()?->get('feature_id') > 0 => Feature::find(request()?->get('feature_id'))->code,
			default => null,
		};

		$translations = Translation::retrieve(
			countryId: auth()->user()->country_id,
			gameId: request()->get('game_id', -1),
			featureId: request()->get('feature_id', -1)
		);
		if ($entityName && count($translations) === 0) {
			return redirect()
				->back()
				->with('message', [
					'type' => 'error',
					'content' => 'Nie ma tłumaczeń w tej sekcji',
				]);
		}
		unset($translations['week-day'], $translations['week-day-short']);

		$countriesToCopy = Country::where('active', true)
			->where('id', '!=', auth()->user()->country_id)
			->get();

		return Inertia::render('Admin/Translations/Index', [
			'translations' => $translations,
			'baseLocaleTranslations' => Translation::retrieve(
				countryId: Country::where('code', 'PL')->first()->id ?? auth()->user()->country_id,

				gameId: request()->get('game_id', null),
				featureId: request()->get('feature_id', null)
			),
			'features' => FeatureResource::collection(Feature::with('game')->get()),
			'games' => GameResource::collection(Game::all()),
			'entityName' => $entityName,
		]);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function update(): RedirectResponse
	{
		$translatableModel = match (true) {
			request()?->get('game_id') > 0 => Game::find(request()?->get('game_id')),
			request()?->get('feature_id') > 0 => Feature::find(request()?->get('feature_id')),
			default => null,
		};

		if ($translatableModel) {
			$translatableModel->translations()->updateOrCreate(
				[
					'key' => request()?->get('key'),
					'country_id' => auth()->user()->country_id,
				],
				[
					'value' => request()?->get('value') ?? '',
				]
			);
		} else {
			Translation::updateOrCreate(
				[
					'translatable_type' => null,
					'translatable_id' => null,
					'key' => request()->get('key'),
					'country_id' => auth()->user()->country_id,
				],
				[
					'value' => request()->get('value'),
				]
			);
		}
		Cache::flush();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano tłumaczenie',
			]);
	}

	public function copy(CopyTranslationsRequest $request): RedirectResponse
	{
		$copyFromCountry = Country::getCountry($request->all()['from']);
		auth()
			->user()
			->country->translations()
			->delete();
		foreach ($copyFromCountry->translations as $translation) {
			Translation::create([
				'translatable_id' => $translation->translatable_id,
				'translatable_type' => $translation->translatable_type,
				'key' => $translation->key,
				'value' => $translation->value,
				'country_id' => auth()->user()->country_id,
			]);
		}
		Cache::flush();
		return redirect()->back();
	}
}
