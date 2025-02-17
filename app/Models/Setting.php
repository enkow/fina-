<?php

namespace App\Models;

use App\Http\Resources\BulbResource;
use App\Http\Resources\FeatureResource;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use JsonException;

class Setting extends BaseModel
{
	public static array $loggable = ['club_id', 'feature_id', 'key', 'value'];
	protected $fillable = ['feature_id', 'key', 'value', 'created_at'];

	public static function getClubGameSetting(int $clubId, string $settingKey, int $gameId = null)
	{
        $clubSettings = Cache::remember(
            'club:' . $clubId . ':retrieved_settings',
            30,
            function () use ($clubId) {
                return self::retrieve('club', $clubId, true);
            }
        );
		foreach ($clubSettings as $key => $setting) {
			$keyToCompare = $settingKey . ($setting['feature'] ? '_' . $setting['feature']->id : '');
			if ($key === $keyToCompare) {
				if ($gameId === null || $setting['feature']->game_id === $gameId) {
					return $setting;
				}
			}
		}

		return null;
	}

	/**
	 * @throws JsonException
	 */
	public static function retrieve($scope = 'global', $clubId = null, $withAdminEditOnly = false): array
	{
		$club = Club::getClub($clubId);
		$countryId = $club?->country_id ?? 1;
		$availableSettings = self::getAvailableSettings();
		$settings = [];
		if ($scope === 'global') {
			// Inserting default values of global settings.
			foreach (config('available-settings')['global'] as $key => $data) {
				$settings[$key] = [
					'feature' => null,
					'translations' => null,
					'value' => $data['default'],
				];
			}

			// Inserting default settings of features assigned to games.
			$featureTypes = (new Feature())->getChildTypes();
			$query = Translation::where('translatable_type', Feature::class)->where('country_id', $countryId);
			$featuresTranslations = Cache::remember(
				'md5' . getMd5FromQuery($query) . ':query_result',
				300,
				static function () use ($query) {
					return $query->get();
				}
			);
			foreach (Game::getCached() as $game) {
				foreach ($game->features as $feature) {
					$featureTypeClassName = $featureTypes[$feature->type];
					$featureTypeClass = new $featureTypeClassName();
					foreach ($featureTypeClass->settings['global'] as $key => $featureTypeSetting) {
						$settings[$key . '_' . $feature->id] = [
							'value' => $featureTypeSetting['default'],
							'translations' => Translation::retrieve(
								countryId: $countryId,
								featureId: $feature->id,
								preloadedTranslations: $featuresTranslations
							),
							'feature' => new FeatureResource($feature),
						];
					}
				}
			}

			// Inserting established values of global settings.
			foreach (Feature::getCached()->whereNull('club_id') as $setting) {
				$settings[$setting->key . ($setting->feature ? '_' . $setting->feature->id : '')] = [
					'feature' => $setting->feature?->game ?? null,
					'translations' => Translation::retrieve(
						countryId: $countryId,
						featureId: $setting->feature?->id
					),
					'value' => $setting->value,
				];
			}

			return $settings;
		}

		if ($scope === 'club') {
			// Inserting default settings of features assigned to the club's games.
            $cachedFeatures = Feature::getCached();
            $countries = Country::getCached();
            $country = $countries->find($countryId);
			$featureTypes = (new Feature())->getChildTypes();
			$gamesFeatures = Club::getCachedGamesForSettings($club);
            $countriesUsedbyClub = $club->countriesUsedByClub();

			$preloadedTranslations = Cache::remember(
				'country:' . $countryId . ':features_translations',
				config('cache.model_cache_time'),
				function () use ($countryId, $cachedFeatures) {
					return Translation::where('translatable_type', Feature::class)
						->where('country_id', $countryId)
						->whereIn('translatable_id', $cachedFeatures->pluck('id'))
						->select('country_id', 'key', 'value', 'translatable_id')
						->get();
				}
			);

			foreach ($gamesFeatures ?? [] as $game) {
				foreach ($game->features as $feature) {
					$featureTypeClassName = $featureTypes[$feature->type];
					$featureTypeClass = new $featureTypeClassName();
					foreach ($featureTypeClass->settings['club'] ?? [] as $key => $featureTypeSetting) {
						$settings[$key . '_' . $feature->id] = [
							'value' => $featureTypeSetting['default'],
							'translations' => Translation::retrieveFeatureTranslationsByCountry(
								feature: $feature,
								country: $country,
								preloadedTranslations: $preloadedTranslations,
                                club: $club,
                                countries: $countriesUsedbyClub,
							),
							'feature' => new FeatureResource($feature),
						];

						if ($game->hasFeature('slot_has_bulb')) {
							$settings[$key . '_' . $feature->id]['bulbs'] = null;
						}
					}
				}
			}


			// Inserting default values of global settings.
			// In this step, the club settings will also be saved, whose value will be loaded from the global settings if the club value has not been established.
			$globalSettingsToLoad = [];
			foreach (config('available-settings')['club'] as $key => $data) {
				if (
					$withAdminEditOnly === false &&
					isset($data['adminOnlyEdit']) &&
					(!((bool) auth()->user()) ||
						!auth()
							->user()
							->isType('admin'))
				) {
					continue;
				}
				if (!isset($settings[$key]) && !isset($featureTypes[$key])) {
					$settings[$key] = [
						'feature' => null,
						'translations' => null,
						'value' => $data['default'],
					];
				}

				if (($data['loadGlobalIfEmpty'] ?? false) && $data['default'] === null) {
					$settings[$key] = [
						'feature' => null,
						'translations' => null,
						'value' => $availableSettings['global'][$key]['default'],
					];
					$globalSettingsToLoad[] = $key;
				}
			}
			// Inserting default global settings.
			foreach (self::getGlobalSettings()->whereIn('key', $globalSettingsToLoad)->where('club_id', $clubId) as $setting) {
				$settings[$setting->key] = [
					'feature' => null,
					'translations' => Translation::retrieveFeatureTranslationsByCountry(
						feature: $cachedFeatures->find($setting->feature_id),
                        country: $country,
                        club: $club,
                        countries: $countriesUsedbyClub,
					),
					'value' => $setting->value,
				];
			}
            // Inserting established values of club settings.
			$settingsCollection = Cache::remember(
				'club:' . $clubId . ':settings_with_bulb_adapter',
				config('cache.model_cache_time'),
				function () use ($club) {
					return $club?->getSettings()->load('bulbAdapter') ?? collect([]);
				}
			);

			$settingsCollectionsFeatureIds = $settingsCollection
				->whereNotNull('feature_id')
                ->where('club_id', $clubId)
				->pluck('feature_id')
				->toArray();

			sort($settingsCollectionsFeatureIds);
			$preloadedTranslations = Cache::remember(
				'country:' .
					$countryId .
					':settings_collections_feature_ids:' .
					json_encode($settingsCollectionsFeatureIds, JSON_THROW_ON_ERROR | true) .
					':features_translations',
				config('cache.model_cache_time'),
				function () use ($settingsCollectionsFeatureIds) {
					return Translation::where('translatable_type', Feature::class)
						->whereIn('translatable_id', $settingsCollectionsFeatureIds)
						->get();
				}
			);
			foreach ($settingsCollection as $setting) {
				$settings[$setting->key . ($setting->feature_id ? '_' . $setting->feature_id : '')] = [
					'feature' => $setting->feature ? new FeatureResource($setting->feature) : null,
					'translations' => Translation::retrieve(
						countryId: $countryId,
						featureId: $setting->feature_id,
						preloadedTranslations: $preloadedTranslations
					),
					'value' => $setting->value,
				];
				if ($setting->feature?->game?->hasFeature('slot_has_bulb')) {
					$settings[$setting->key . ($setting->feature_id ? '_' . $setting->feature_id : '')][
						'bulbs'
					] = $setting->bulbAdapter ? new BulbResource($setting->bulbAdapter) : null;
				}
			}

			return $settings;
		}

		return [];
	}

	public static function getAvailableSettings(): array
	{
		$settings = config('available-settings');
		$childTypes = (new Feature())->getChildTypes();

		return array_reduce(
			$childTypes,
			static function ($carry, $class) {
				$featureSettings = (new $class())->settings;

				return array_replace_recursive($carry, $featureSettings);
			},
			$settings
		);
	}

	public static function getGlobalSettings()
	{
		return Cache::remember('global_settings', config('cache.model_cache_time'), function () {
			return self::whereNull('club_id')->get();
		});
	}

	/**
	 * @return Attribute
	 */
	public function value(): Attribute
	{
		return Attribute::make(
			get: fn($value) => self::castValue($this->key, $value),
			set: fn($value) => match (self::getSettingTypeByKey($this->key)) {
				'json' => json_encode($value, JSON_THROW_ON_ERROR),
				default => $value,
			}
		);
	}

	/**
	 * @throws JsonException
	 */
	public static function castValue(string $key, mixed $value): mixed
	{
		$settingType = self::getSettingTypeByKey($key);

		return match ($settingType) {
			'json' => json_decode($value, true, 512, JSON_THROW_ON_ERROR),
			'integer' => $value !== null ? (int) $value : null,
			'boolean' => (bool) $value,
			default => $value,
		};
	}

	public static function getSettingTypeByKey(string $key): string
	{
		return self::getAvailableSettings()[self::findScopeBySettingName($key)][$key]['type'] ?? 'integer';
	}

	public static function findScopeBySettingName($settingName): string
	{
		foreach (array_keys(self::getAvailableSettings()) as $scope) {
			foreach (self::getAvailableSettings()[$scope] as $key => $val) {
				if ($key === $settingName) {
					return $scope;
				}
			}
		}

		return 'club';
	}

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function feature(): BelongsTo
	{
		return $this->belongsTo(Feature::class);
	}

	public function changes(): MorphMany
	{
		return $this->morphMany(DataChange::class, 'changable');
	}

	public function bulbAdapter()
	{
		return $this->hasOne(BulbAdapter::class);
	}
}
