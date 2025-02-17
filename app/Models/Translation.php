<?php

namespace App\Models;

use App\Http\Resources\FeatureResource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;

class Translation extends BaseModel
{
    public static array $defaultTranslations = [
        'help-statistics-content' => 'W tej sekcji możesz przeanalizować obroty w swoim klubie !',

        'help-discount-codes-link' => '/club/help-sections',
        'help-discount-codes-content' =>
            'W tej sekcji możesz zdefiniować kody rabatowe. Mogą z nich korzystać zarówno klienci rezerwujący online jak i na miejscu.',

        'help-special-offers-link' => '/club/help-sections',
        'help-special-offers-content' =>
            'W tej sekcji możesz zdefiniować szczegóły swojej promocji. Twoi klienci na pewno chętnie będą z niech korzystać!',

        'help-opening-hours-link' => '/club/help-sections',
        'help-opening-hours-content' =>
            'W tej sekcji możesz ustawić na konkretne dni lub  zakres dni niestandardowe godziny (np. całkowite zamknięte lub inne godziny niz zazwyczaj)',

        'help-opening-hours-exception-link' => '/club/help-sections',
        'help-opening-hours-exception-content' =>
            'W tej sekcji możesz ustawić na konkretne dni lub zakres dni niestandardowe godziny (np. całkowite zamknięte lub inne godziny niz zazwyczaj)',

        'help-employees-link' => '/club/help-sections',
        'help-employees-content' =>
            'W tej sekcji możesz nadać dostęp dla nowego pracownika. Na jego adres mailowy przyjdzie powiadomienie z prośbą o utworzenie hasła',

        'help-calendar-settings-link' => '/club/help-sections',
        'help-calendar-settings-content' => 'W tej sekcji możesz edytować ustawienia związane z kalendarzem',

        'help-pricelists-link' => '/club/help-sections',
        'help-pricelists-content' => 'W tej sekcji widzisz wszystkie utworzone przez Ciebie cenniki',

        'help-reservation-settings-link' => '/club/help-sections',
        'help-reservation-settings-content' =>
            'W tej sekcji możesz edytować ustawienia związane z rezerwacjami online',

        'help-agreements-link' => '/club/agreements',
        'help-agreements-content' =>
            'W tej sekcji możesz utworzyć zgody, które będą akceptowali Twoi klienci.',

        'help-sets-link' => '/club/help-sections',
        'help-sets-content' =>
            'W tej sekcji możesz zdefiniować Zestawy. Bedą one możliwe do kupienia przez klientów online. To super opcja na zwiększenie obrotów w Twoim klubie!',

        'help-settlements-link' => '/club/settlements',
        'help-settlements-content' =>
            'W tej sekcji możesz przeanalizować rozliczenia z aplikacją Bookgame.io.',

        'help-online-payments-content' =>
            'Zarejestruj się w Stripe a będziesz mógł przyjmować płatności online!',

        'help-weekly-statistics-content' =>
            'W tej sekcji możesz edytować ustawienia związane z statystykami tygodniowymi',
    ];
    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'game_id',
        'feature_id',
        'country_id',
        'key',
        'value',
    ];

    /**
     * Establishes a translation array obtained by the conditions from game arguments
     * based on the order (from least important to most important):
     * game code, general game translation, game name set for club-game relation.
     *
     * @param  Club|null  $club
     * @param  int|null   $countryId
     *
     * @return string[]
     */
    public static function retrieveGameNames(
        Club $club = null,
        int $countryId = null,
        $preloadedTranslations = null
    ): array {
        return Cache::remember(
            'club:' . ($club->id ?? 0) . ':country:' . $countryId . ':game_names',
            config('cache.model_cache_time'),
            function () use ($club, $countryId, $preloadedTranslations) {
                $countryId = (int) ($countryId ?? ($club?->country_id ?? 171));
                $gameNames = [];
                $gamesTranslations =
                    $preloadedTranslations?->where('country_id', $countryId) ??
                    Translation::where('translatable_type', Game::class)
                        ->where('country_id', $countryId)
                        ->get();
                foreach (Game::getCached() as $game) {
                    $gameNames[$game->id] = self::retrieve(
                        countryId: $countryId,
                        gameId: $game->id,
                        featureId: null,
                        preloadedTranslations: $gamesTranslations->where('translatable_id', $game->id)
                    )['game-name'];
                    if ($gameNames[$game->id] === 'game-name') {
                        $gameNames[$game->id] = $game->name;
                    }
                    $clubGame = $club?->games->where('id', $game->id)->first();
                    if ($clubGame) {
                        $customClubGameNames = json_decode(
                            $clubGame->pivot->custom_names,
                            true,
                            512,
                            JSON_THROW_ON_ERROR
                        );
                        if (isset($customClubGameNames[$countryId])) {
                            $gameNames[$clubGame->id] = $customClubGameNames[$countryId];
                        }
                    }
                }

                return $gameNames;
            }
        );
    }


    /**
     * Establishes a translation array obtained by the conditions from game arguments
     * based on the order (from least important to most important):
     * game code, general game translation, game name set for club-game relation.
     *
     * @param  Club|null  $club
     * @param  int|null   $countryId
     *
     * @return string[]
     */
    public static function retrieveSpecificGameNames(
        Club $club = null,
        int $countryId = null,
        $preloadedTranslations = null,
        $games = null
    ): array {
        return Cache::remember(
            'club:' . ($club->id ?? 0) . ':country:' . $countryId . ':game_names',
            config('cache.model_cache_time'),
            function () use ($club, $countryId, $preloadedTranslations, $games) {
                $countryId = (int) ($countryId ?? ($club?->country_id ?? 171));
                $gameNames = [];
                $gamesTranslations =
                    $preloadedTranslations?->where('country_id', $countryId) ??
                    Translation::where('translatable_type', Game::class)
                        ->where('country_id', $countryId)
                        ->get();
                foreach ($games as $game) {
                    $gameNames[$game->id] = self::retrieve(
                        countryId: $countryId,
                        gameId: $game->id,
                        featureId: null,
                        preloadedTranslations: $gamesTranslations->where('translatable_id', $game->id)
                    )['game-name'];
                    if ($gameNames[$game->id] === 'game-name') {
                        $gameNames[$game->id] = $game->name;
                    }
                    $clubGame = $club?->games->where('id', $game->id)->first();
                    if ($clubGame) {
                        $customClubGameNames = json_decode(
                            $clubGame->pivot->custom_names,
                            true,
                            512,
                            JSON_THROW_ON_ERROR
                        );
                        if (isset($customClubGameNames[$countryId])) {
                            $gameNames[$clubGame->id] = $customClubGameNames[$countryId];
                        }
                    }
                }

                return $gameNames;
            }
        );
    }




    public static function retrieveFeatureTranslations(
        Feature|FeatureResource $feature,
        Country $country,
        mixed $preloadedTranslations = null,
    ) {
        // load all locale translations if widget pages are being served
        $multilocale = request()->routeIs('widget.*');
        $cacheKeyParts = [];
        $cacheKeyParts[] = 'feature:' . $feature->id;
        if ($multilocale) {
            $cacheKeyParts[] = 'country:all';
        } else {
            $cacheKeyParts[] = 'country:' . $country->id;
        }
        if ($preloadedTranslations) {
            $cacheKeyParts[] = 'preloaded_translations:true';
        } else {
            $cacheKeyParts[] = 'preloaded_translations:false';
        }
        $cacheKeyParts[] = 'translations_array';

        return Cache::remember(
            implode(':', $cacheKeyParts),
            config('cache.model_cache_time'),
            function () use ($feature, $country, $preloadedTranslations, $multilocale) {
                $featureTypes = (new Feature())->getChildTypes();
                $customTranslationsArray = $defaultTranslations = [];
                $featureDefaultTranslations = (new ($featureTypes[$feature->type])())->defaultTranslations;
                $baseCollection = $preloadedTranslations ?? $feature->translations;
                $featureDynamicTranslations = $feature->getDynamicTranslations();
                foreach (
                    $multilocale
                        ? Country::getCached()
                        ->where('active', true)
                        ->pluck('id')
                        ->toArray()
                        : [$country->id]
                    as $currentCountryId
                ) {
                    $locale = Country::getCountry($currentCountryId)->locale;
                    $databaseTranslations = $baseCollection
                        ->where('country_id', $currentCountryId)
                        ->where('translatable_id', $feature->id)
                        ->pluck('value', 'key')
                        ->toArray();
                    if ($multilocale) {
                        $customTranslationsArray[$locale] = $databaseTranslations;
                        $defaultTranslations[$locale] = merge_deeply(
                            $featureDefaultTranslations,
                            $featureDynamicTranslations
                        );
                    } else {
                        $customTranslationsArray = $databaseTranslations;
                        $defaultTranslations = merge_deeply(
                            $featureDefaultTranslations,
                            $featureDynamicTranslations
                        );
                    }
                }

                return merge_deeply($defaultTranslations, $customTranslationsArray);
            }
        );
    }

    public static function retrieveFeatureTranslationsByCountry(
        Feature|FeatureResource $feature,
        $country = null,
        mixed $preloadedTranslations = null,
        $club = null,
        $countries = null,
    ) {
        $cacheKeyParts = 'feature' . $feature->id . 'country' . $country->id . 'country_translations_array';
        return Cache::remember(
            $cacheKeyParts,
            config('cache.model_cache_time'),
            function () use ($feature, $country, $preloadedTranslations, $countries) {
                $featureTypes = (new Feature())->getChildTypes();
                $customTranslationsArray = $defaultTranslations = [];
                $featureDefaultTranslations = (new ($featureTypes[$feature->type])())->defaultTranslations;
                $baseCollection = $preloadedTranslations ?? $feature->translations;
                $featureDynamicTranslations = $feature->getDynamicTranslations();
                foreach (
                    Country::getCached()
                        ->where('active', true)
                        ->whereIn('locale', $countries)
                        ->pluck('id', 'locale')
                        ->toArray()
                    as $currentCountryId => $locale
                ) {
                    $databaseTranslations = $baseCollection
                        ->where('country_id', $currentCountryId)
                        ->where('translatable_id', $feature->id)
                        ->pluck('value', 'key')
                        ->toArray();
                    if (count($countries) > 1) {
                        $customTranslationsArray[$locale] = $databaseTranslations;
                        $defaultTranslations[$locale] = merge_deeply(
                            $featureDefaultTranslations,
                            $featureDynamicTranslations
                        );
                    } else {
                        $customTranslationsArray = $databaseTranslations;
                        $defaultTranslations = merge_deeply(
                            $featureDefaultTranslations,
                            $featureDynamicTranslations
                        );
                    }
                }

                return merge_deeply($defaultTranslations, $customTranslationsArray);
            }
        );
    }


    public static function retrieveGameTranslations(
        int $gameId,
        int $countryId,
        mixed $preloadedTranslations = null
    ) {
        return Cache::remember(
            'game:' . $gameId . ':country:' . $countryId . ':translations_array',
            config('cache.model_cache_time'),
            function () use ($gameId, $countryId, $preloadedTranslations) {
                $defaultTranslations = Game::$defaultTranslations;

                return array_merge(
                    $defaultTranslations,
                    (
                        $preloadedTranslations
                            ?->where('country_id', $countryId)
                            ->where('translatable_id', $gameId)
                            ->where('translatable_type', Game::class) ??
                        Game::getCached()
                            ->find($gameId)
                            ->translations()
                            ->where('country_id', $countryId)
                    )
                        ->pluck('value', 'key')
                        ->toArray()
                );
            }
        );
    }


    public static function retrieveClubFeatureTranslations($countryId = null, $feature = null) : array
    {
        return Cache::remember(
            'country:' . $countryId . ':feature:' . $feature->id . ':translations_array',
            config('cache.model_cache_time'),
            function () use ($countryId, $feature) {
                $translations = $feature->translations()
                    ->where('country_id', $countryId)
                    ->pluck('value', 'key')
                    ->toArray();
                return $translations;
            }
        );
    }


    public static function retrieve(
        $countryId = null,
        $gameId = null,
        $featureId = null,
        $preloadedTranslations = null
    ): array {
        if ($gameId === -1 && $featureId === -1) {
            return [];
        }

        $cacheKeyParts = [
            'country',
            $countryId ?? '-',
            'game',
            $gameId ?? '-',
            'feature',
            $featureId ?? '-',
            'preloadedTranslations',
            $preloadedTranslations ? 'true' : 'false',
        ];

        $cacheKey = implode('_', $cacheKeyParts);

        return Cache::remember($cacheKey, config('cache.model_cache_time'), function () use (
            $gameId,
            $featureId,
            $countryId,
            $preloadedTranslations
        ) {
            $gameId = in_array($gameId, [0, '0', '-1', -1], true) ? null : $gameId;
            $featureId = in_array($featureId, [0, '0', '-1', -1], true) ? null : $featureId;

            $features = Feature::getCached();
            $feature = $features->where('id', $featureId)->first();
            $featureTypes = (new Feature())->getChildTypes();
            $defaultTranslations = match (true) {
                $gameId === null && $featureId === null => self::$defaultTranslations,
                $gameId === null && $featureId !== null => array_merge(
                    (new ($featureTypes[$feature->type])())->defaultTranslations,
                    $feature->getDynamicTranslations()
                ),
                $gameId !== null && $featureId === null => Game::$defaultTranslations,
                default => [],
            };

            if ($preloadedTranslations) {
                $translations = $preloadedTranslations;
            } else {
                $translations = match (true) {
                    $gameId === null && $featureId === null => self::whereNull('translatable_type'),
                    $gameId === null && $featureId !== null => $feature->translations(),
                    $gameId !== null && $featureId === null => Game::getCached()
                        ->find($gameId)
                        ->translations(),
                    default => [],
                };
            }
            $translations = array_merge(
                $defaultTranslations,
                $translations !== []
                    ? $translations
                    ->where('country_id', $countryId)
                    ->pluck('value', 'key')
                    ->toArray()
                    : []
            );

            // Merge with base dashboard translations if receiving global translations
            if ($gameId === null && $featureId === null) {
                $country = Country::getCountry($countryId);
                $translations = array_merge($translations, [
                    'week-day' => array_values(Lang::get('main.week-day', [], $country->locale)),
                    'week-day-short' => array_values(Lang::get('main.week-day-short', [], $country->locale)),
                ]);
            }

            return $translations;
        });
    }

    public static function gamesTranslationsArray($countryId = null, $preloadedTranslations = null, $gamesArray = null): array
    {
        if($gamesArray !== null){
            $games = $gamesArray;
        } else {
            $games = Game::getCached();
        }

        $countryId =
            $countryId ??
            (club()?->country_id ?? (auth()->user()?->country_id ?? request()->route('club')->country_id));
        return Cache::remember(
            'country:' . $countryId . ':games_translations_array',
            config('cache.model_cache_time'),
            function () use ($games, $countryId, $preloadedTranslations) {
                $result = [];
                $gamesTranslations =
                    $preloadedTranslations ?? Translation::where('translatable_type', Game::class)->get();
                foreach ($games as $game) {
                    $result[$game->id ?? $game] = self::retrieveGameTranslations(
                        $game->id ?? $game,
                        $countryId,
                        $gamesTranslations
                    );
                }
                return $result;
            }
        );
    }
    public static function clubGamesTranslationsArray($club, $countryId = null): array
    {
        $games = $club->games;
        $countryId =
            $countryId ??
            (club()?->country_id ?? (auth()->user()?->country_id ?? request()->route('club')->country_id));
        return Cache::remember(
            'country:' . $countryId . 'club:' . $club->id . ':club_games_translations_array',
            config('cache.model_cache_time'),
            function () use ($games, $countryId) {
                $result = [];
                foreach ($games as $game) {
                    $result[$game->id ?? $game] = self::retrieveGameTranslations(
                        $game->id ?? $game,
                        $countryId,
                    );
                }
                return $result;
            }
        );
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
