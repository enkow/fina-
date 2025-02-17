<?php

namespace App\Http\Middleware;

use App\Http\Resources\GameResource;
use App\Models\Club;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Game;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Setting;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use JsonException;

class HandleInertiaRequests extends Middleware
{
	/**
	 * The root template that's loaded on the first page visit.
	 *
	 * @see https://inertiajs.com/server-side-setup#root-template
	 * @var string
	 */
	protected $rootView = 'app';

	/**
	 * Determines the current asset version.
	 *
	 * @see https://inertiajs.com/asset-versioning
	 *
	 * @param  Request  $request
	 *
	 * @return string|null
	 */
	public function version(Request $request): ?string
	{
		return parent::version($request);
	}

	/**
	 * @throws JsonException
	 */
	public function dashboardProps(Request $request)
	{
		if (!auth()->user()) {
			return [];
		}
		//		InvoiceItem::where('model_type', 'Game')->update(['model_type' => 'App\Models\Game']);
		//		InvoiceItem::where('model_type', 'Product')->update(['model_type' => 'App\Models\Product']);

		$club = club();
		$lastInvoice = club()?->invoices()->orderBy('id', 'desc')->first();

        $game = $request->route()->parameters['game'] ?? null;

        if($game === null) {
            $gameTranslations = Translation::gamesTranslationsArray();
        } else {
            if($club === null){
                if(isset($request->route()->parameters['club']) &&$request->route()->parameters['club'] !== null) {
                    $club = $request->route()->parameters['club'];
                    $gameTranslations = Translation::clubGamesTranslationsArray($club, $club->country_id);
                } else {
                    $gameTranslations = Translation::gamesTranslationsArray();
                }
            } else {
                $gameTranslations = Translation::clubGamesTranslationsArray($club, $club->country_id);
            }

        }
        

		$result = [
			'user' => auth()
				->user()
				->getData(),
			'lastInvoicePaid' => $lastInvoice?->paid_at !== null,
			'helpEnabled' => auth()
				->user()
				->isType(['manager', 'operator'])
				? Cache::remember('country_' . $club->country_id . '_help_enabled', 600, function () use (
					$club
				) {
					return Country::getCountry($club->country_id)
						->helpSections()
						->exists();
				})
				: true,
			'clubSettings' => club()?->getRenderedSettings() ?? [],
			'tablePreferences' => auth()
				->user()
				->getTablePreferences(),
			'flash' => [
				'type' => fn() => $request->session()->get('message')['type'] ?? null,
				'message' => fn() => $request->session()->get('message')['content'] ?? null,
				'timeout' => fn() => $request->session()?->get('message')['timeout'] ?? null,
			],
			'generalTranslations' => ((bool) auth()->user())
				? Translation::retrieve(
					$club->country_id ?? auth()->user()->country_id,
					gameId: $game,
					featureId: 0
				)
				: [],
			'gameTranslations' => $gameTranslations,
			'gameNames' => ((bool) auth()->user()) ? Translation::retrieveGameNames(club()) : null,
		];
		if (
			auth()
				->user()
				?->isType('admin')
		) {
			$result += [
				'activeCountries' =>
					(bool) auth()->user() &&
					auth()
						->user()
						?->isType('admin')
						? Country::where('active', 1)
							->pluck('code', 'id')
							->toArray()
						: null,
			];
		}

		return $result;
	}

	public function widgetProps(Request $request): array
	{

        if($request->route()->parameters()['club'] !== null){
            $club = $request->route()->parameters()['club'];
            $languages = $club->widget_countries;
            if($languages === null) {
                $languages = config('app.locales');
            }
            $languagesArray=[];
            $lanuagesIds = [];

            foreach($languages as $language) {
                $country_lang = Country::where('code', $language)->first();
                if($country_lang !== null) {
                    $languagesArray[] = $country_lang;
                }
            }


            $games = Club::getCachedGames($club);
            $gamesArray = [];
            foreach($games as $game){
                $gamesArray[] = $game->id;
            }
            $games = GameResource::collection($games);
        } else {
            $lanuagesIds = [];
            $languagesArray = config('app.locales');
            $games = GameResource::collection(Game::getCached());
        }

        $result = [];
		$gamesTranslations = Cache::remember(
			'country:' . $request->route('country') . ':games_translations',
			config('cache.model_cache_time'),
            function () use ($languagesArray, $gamesArray) {
				$result = [];

                foreach ($languagesArray as $locale) {

                    $result[$locale->locale] = $locale
                        ? Translation::gamesTranslationsArray($locale->id, null, $gamesArray)
                        : [];
                }

				return $result;
			}
		);


		return [
			'customer' => session()->has('customer_id')
				? Customer::getCustomer(session()?->get('customer_id'))
				: null,
			'games' => $games,
			'gamesTranslations' => $gamesTranslations,
			'locales' => $languagesArray,
            'globalSettings' => Setting::retrieve(),
			'currentStep' => (int) request()?->route('step'),
			'dayShortNames' => __('main.week-day-short'),
		];
	}

	public function defaultProps(Request $request): array
	{
		return [];
	}

	/**
	 * Defines the props that are shared by default.
	 *
	 * @see https://inertiajs.com/shared-data
	 *
	 * @param  Request  $request
	 *
	 * @return array
	 */
	public function share(Request $request): array
	{
        $sharedType = 'default';

        if($request->routeIs('profile.*', 'club.*', 'admin.*') && $request->method('get')) {
            $sharedType = 'dashboard';
        }

        if($request->routeIs('widget.*', 'calendar.*') && $request->method('get')) {
            $sharedType = 'widget';
        }


		return array_merge(parent::share($request), $this->{$sharedType . 'Props'}($request));
	}
}
