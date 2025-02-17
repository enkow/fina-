<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Game extends BaseModel
{
	use SoftDeletes;

	public static array $defaultTranslations = [
		'game-name' => 'game-name',
		'description' => 'Opis gry',
		'slot-singular-short' => 'Stół',
		'slot-plural' => 'Stoły bilardowe',
		'slot-number-value' => 'Stół nr: :value',
		'slot-mascot-type' => '4',
		'slot-help-content' => 'Tłumaczenie contentu help boxa',
		'slot-help-link' => '/club/help-sections',
		'slot-add' => 'Dodaj stół',
		'slot-edit' => 'Edytuj stół',
		'add-new-item' => 'Dodaj nowy (długa nazwa)',
		'add-new-item-short' => 'Dodaj nowy (krótka nazwa)',
		'index-info' => 'Box informacyjny (Lista slotów)',
		'create-info' => 'Box informacyjny (Tworzenie slota)',
		'slots-quantity' => 'Ilość stolików',
		'successfully-stored' => 'Dodano nowy stół',
		'successfully-updated' => 'Zaktualizowano stół',
		'successfully-destroyed' => 'Usunięto stół',
		'slot-name' => 'Numer stołu',
		'game-time' => 'Czas gry',
		'any' => 'Dowolny',
		'choose-slot' => 'Wybierz tor',
		'the-slot-is-occupied-at-the-given-time' => 'Stół jest zajęty w podanych godzinach',
		'the-slot-is-occupied-at-the-given-time-value' => 'Stół :value jest zajęty w podanych godzinach',
		'reservation-confirmed-customer-notification-greeting' => 'Potwierdzono Twoją grę w Bilard!',
		'reservation-for-slot-header' => 'Rezerwacja na stół nr :slot_name',
		'not-enough-vacant-slots' => 'Nie ma wystarczającej ilości wolnych slotów',
		'pricelist-destroy-has-slots-error' => 'Nie można usunąć cennika, ponieważ jest przypisany do stołów',
		'slot-destroy-has-reservations-error' =>
			'Nie można usunąć stołu, ponieważ istnieją przypisane do niego rezerwacje',
		'slot-unactive-has-reservations-error' =>
			'Nie można dezaktywować stołu, ponieważ istnieją przypisane do niego rezerwacje',
		'slots-no-pricelist-error' => 'Aby zarządzać stołami, musisz najpierw utworzyć cennik.',
		'widget-slots-no-longer-available-error' =>
			'Ten stół o tej godzinie jest już zarezerwowany. Dokonaj nowej rezerwacji.',
		'all-slots-selected-alert' => 'Wybrałeś już wszystkie stoły',
		'offline-reservation-slots-limit-exceeded' =>
			'Wyczerpałeś limit rezerwacji "Płatnych w Klubie". ( :reservations rezerwacja max na :slots stoły ). Zapłać online ( limit zostanie odblokowany po zrealizowaniu/anulowaniu rezerwacji).',
		'offline-reservation-hours-limit-exceeded' =>
			'Wyczerpałeś limit rezerwacji "Płatnych w Klubie". ( :reservations rezerwacja max na :hours godziny ). Zapłać online ( limit zostanie odblokowany po zrealizowaniu/anulowaniu rezerwacji).',
		'offline-reservation-slots-and-hours-limit-exceeded' =>
			'Wyczerpałeś limit rezerwacji "Płatnych w Klubie". ( :reservations rezerwacja max na :slots stoły / :hours godziny ). Zapłać online ( limit zostanie odblokowany po zrealizowaniu/anulowaniu rezerwacji).',
	];
	protected $fillable = ['game_id', 'name', 'icon', 'setting_icon_color', 'photo'];

	public static function getCached()
	{
		return Cache::remember('games_with_features', config('cache.model_cache_time'), function () {
			return Game::with('features', 'features.game', 'features.translations')->get();
		});
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function getReservationsTablePreference(): array
	{
		$reservationTimeRangeCondition =
			!$this->hasFeature('fixed_reservation_duration') ||
			!(
				(int) (Setting::getClubGameSetting(clubId() ?? 1, 'fixed_reservation_duration', $this->id)[
					'value'
				] ?? 0) === 24
			) ||
			auth()
				->user()
				?->isType('admin');

		$columns = [
			auth()
				->user()
				?->isType('admin')
				? 'created_datetime'
				: null,
			'reservation_id',
			'reservation_slot_id',
			'reservation_number_id',
			'number',
			$this->hasFeature('slot_has_parent') ? 'parent_slot_id' : null,
			$this->hasFeature('slot_has_parent') ? 'parent_slot_name' : null,
			!$this->hasFeature('person_as_slot') ? 'slot_id' : null,
			!$this->hasFeature('person_as_slot') ? 'slot_name' : null,
			$this->hasFeature('book_singular_slot_by_capacity') ? 'slot_capacity' : null,
			$this->hasFeature('price_per_person') ? 'person_count' : null,
			$this->hasFeature('has_timers') ? 'timer_status' : null,
			'start_datetime',
			'end_datetime',
			$reservationTimeRangeCondition ? 'reservation_time_range' : null,
			$this->hasFeature('person_as_slot') ? 'slots_count' : null,
			'final_price',
			'source',
			'customer_name',
			'customer_phone',
			'customer_email',
			'sets',
			'calendar_name',
			'calendar_color',
			'status',
			'club_reservation',
			'cancelation_type',
			'cancelation_status',
			'customer_note',
			'show_customer_note_on_calendar',
			'created_at',
			'club_note',
			'show_club_note_on_calendar',
			'status_locale',
			'payment_method_id',
			'payment_method_online',
			'display_name',
			'custom_display_name',
			$this->hasFeature('reservation_slot_has_occupied_status') || $this->hasFeature('person_as_slot')
				? 'occupied_status'
				: null,
		];

		$columns = array_filter($columns);

		return array_map(
			static fn(string $column) => [
				'key' => $column,
				'enabled' => true,
			],
			$columns
		);
	}

	public function getFeaturesByType(Feature|string $feature): Collection
	{
		$gameFeatures = Cache::remember(
			'game_' . $this->id . '_features',
			config('cache.model_cache_time'),
			function () {
				return $this->features;
			}
		);

		return $gameFeatures->where(
			'type',
			match (gettype($feature)) {
				'string' => $feature,
				'object' => $feature->type,
			}
		);
	}

	public function hasFeature(Feature|string $feature): bool
	{
		$gameFeatures = Cache::remember(
			'game_' . $this->id . '_features',
			config('cache.model_cache_time'),
			function () {
				return $this->features;
			}
		);

		return !empty(
			$gameFeatures
				->where(
					'type',
					match (gettype($feature)) {
						'string' => $feature,
						'object' => $feature->type,
					}
				)
				->first()
		);
	}

	public function features(): HasMany
	{
		return $this->hasMany(Feature::class);
	}

	public function getSlotsTablePreference(): array
	{
		$gameFeatures = Cache::remember('game_' . $this->id . '_features', 20, function () {
			return $this->features;
		});
		$columns = ['name'];
		if ($this->hasFeature('slot_has_subtype')) {
			$columns = array_merge($columns, ['subtype']);
		} elseif ($this->hasFeature('slot_has_type')) {
			$columns = array_merge($columns, ['type']);
		}
		if (!$this->hasFeature('has_only_one_pricelist')) {
			$columns = array_merge($columns, ['pricelist']);
		}
		if (
			$this->hasFeature('slot_has_lounge') &&
			(Setting::getClubGameSetting(clubId() ?? 1, 'lounges_status', $this->id)['value'] ?? false)
		) {
			$columns = array_merge($columns, ['lounge']);
		}
		if ($this->hasFeature('slot_has_convenience')) {
			$slotHasConvenienceFeatures = $gameFeatures->where('type', 'slot_has_convenience');
			$columns = array_merge(
				$columns,
				array_map(
					static fn($feature) => 'convenience_' . $feature->id,
					$slotHasConvenienceFeatures->all()
				)
			);
		}

		if ($this->hasFeature('parent_slot_has_online_status')) {
			$columns = array_merge($columns, ['online_status']);
		}
		$columns = array_merge($columns, ['active']);

		if (
			$this->hasFeature('slot_has_parent') &&
			$this->hasFeature('person_as_slot') &&
			$gameFeatures->where('type', 'person_as_slot')->first()['data']['parent_has_capacity_by_week_day']
		) {
			$weekDays = range(1, 7);
			$capacityColumns = array_map(static fn($weekDay) => 'capacity_' . $weekDay, $weekDays);
			$columns = array_merge($columns, $capacityColumns);
		}

		return array_map(
			static fn(string $column) => [
				'key' => $column,
				'enabled' => true,
			],
			$columns
		);
	}

	public function discountCodes(): HasMany
	{
		return $this->hasMany(DiscountCode::class);
	}

	public function specialOffers(): HasMany
	{
		return $this->hasMany(SpecialOffer::class);
	}

	public function translations(): MorphMany
	{
		return $this->morphMany(Translation::class, 'translatable');
	}

	public function reservations(): HasMany
	{
		return $this->hasMany(Reservation::class);
	}

	public function clubs(): BelongsToMany
	{
		return $this->belongsToMany(Club::class, 'club_game', 'game_id', 'club_id')
			->withTimestamps()
			->withPivot('custom_names', 'weight', 'enabled_on_widget', 'fee_percent', 'fee_fixed')
			->orderByPivot('weight', 'desc');
	}

	public function slots(): HasManyThrough
	{
		return $this->hasManyThrough(Slot::class, Pricelist::class);
	}

	public function pricelists(): HasMany
	{
		return $this->hasMany(Pricelist::class);
	}

	public static function getWidgetGamesData(Club $club): array
	{
		return Cache::remember(
			'club:' . $club->id . ':widget_games_data',
			config('cache.model_cache_time'),
			function () use ($club) {
				$result = [];
				$slotsCount = Slot::where('active', true)
					->whereHas('pricelist', function ($query) use ($club) {
						$query->where('club_id', $club->id);
					})
					->selectRaw('pricelists.game_id, COUNT(*) as count')
					->join('pricelists', 'slots.pricelist_id', '=', 'pricelists.id')
					->groupBy('pricelists.game_id')
					->pluck('count', 'game_id')
					->toArray();
				foreach (
					self::getCached()->whereIn(
						'id',
						$club
							->games()
							->pluck('game_id')
							->toArray()
					)
					as $game
				) {
					$result[$game->id]['slots_count'] = $slotsCount[$game->id] ?? 0;

					if ($game->hasFeature('slot_has_type') || $game->hasFeature('slot_has_subtype')) {
						$result[$game->id]['gamesSlots'] = Slot::where('active', true)
							->whereHas('pricelist', function ($query) use ($game, $club) {
								$query->where('game_id', $game->id);
								$query->where('club_id', $club->id);
							})
							->with('features')
							->get();
					}
				}
				return $result;
			}
		);
	}

	public function invoiceItems(): MorphMany
	{
		return $this->morphMany(InvoiceItem::class, 'items');
	}
}
