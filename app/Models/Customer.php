<?php

namespace App\Models;

use App\Enums\CustomerFilter;
use App\Enums\CustomerVerificationMethod;
use App\Enums\ReminderMethod;
use App\Enums\ReminderType;
use App\Enums\ReservationSlotStatus;
use App\Filters\Customer\GroupFilter;
use App\Filters\Customer\TypeFilter;
use App\Notifications\Customer\VerificationNotification;
use App\Searchers\CustomerNameSearcher;
use App\Sorters\Customer\LatestReservationSorter;
use App\Sorters\Customer\NameSorter;
use App\Traits\Filterable;
use App\Traits\Searchable;
use App\Traits\Sortable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Customer extends BaseModel
{
	use HasFactory, SoftDeletes, Searchable, Filterable, Sortable, Notifiable, HasRelationships;

	public static array $availableFilters = [GroupFilter::class, TypeFilter::class];
	//Classes that can be added in the Filterable trait method
	public static array $availableSearchers = ['customers.id', CustomerNameSearcher::class, 'phone', 'email'];
	//Classes and fields that can be added in the Searchable trait method
	public static array $availableSorters = ['id', LatestReservationSorter::class, NameSorter::class];
	//Classes and fields that can be added in the Sortable trait method
	protected $fillable = [
		'club_id',
		'first_name',
		'last_name',
		'name',
		'email',
		'phone',
		'password',
		'verified',
		'widget_channel',
		'widget_channel_expiration',
		'locale',
	];

	protected $casts = [
		'verified' => 'boolean',
		'widget_channel_expiration' => 'datetime',
	];

	public function preferredLocale()
	{
		return $this->locale ?? 'en';
	}

	public function reminders(): MorphMany
	{
		return $this->morphMany(Reminder::class, 'remindable');
	}

	public static function getCustomer(int|null $customerId): self|null
	{
			return $customerId ? self::where('id', $customerId)->first() : null;
	}

	public static function tableData(): array
	{
		$result['name'] = auth()
			->user()
			?->isType('admin')
			? 'admin_customers'
			: 'customers';
		$result['preference'] = auth()
			->user()
			->getTablePreferences()[$result['name']];
		$result['headings'] = self::tableHeadingTranslations();

		return $result;
	}

	public static function tableHeadingTranslations($shorter = false): array
	{
		return [
			'id' => !$shorter ? ucfirst(__('main.id')) : 'ID',
			'first_name' => ucfirst(__('main.first-name')),
			'last_name' => ucfirst(__('main.last-name')),
			'club',
			'club_name' => ucfirst(__('main.club')),
			'full_name' => !$shorter
				? ucfirst(__('main.first-name-and-last-name'))
				: ucfirst(__('main.name')),
			'email' => ucfirst(__('main.email')),
			'phone' => ucfirst(__('main.phone')),
			'latest_reservation' => __('customer.last-reservation'),
			'reservations_count' => __('customer.reservation-count'),
			'tags' => __('tag.plural'),
			'agreement_0' => __('agreement.types.0'),
			'agreement_1' => __('agreement.types.1'),
			'agreement_2' => __('agreement.types.2'),
		];
	}

	public function agreements(): BelongsToMany
	{
		return $this->belongsToMany(Agreement::class)->withTimestamps();
	}

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function reservations(): HasMany
	{
		return $this->hasMany(Reservation::class);
	}

	public function reservationSlots(): HasManyThrough
	{
		return $this->hasManyThrough(ReservationSlot::class, Reservation::class);
	}

    public function boughtSets()
    {
        return $this->hasManyDeep(
            Set::class,
            [Reservation::class, 'reservation_slots', 'reservation_slot_set'], // Ścieżka przez modele i tabele pośrednie
            [
                'customer_id', // Klucz obcy w Reservation wskazujący na Customer
                'reservation_id', // Klucz obcy w reservation_slots wskazujący na Reservation
                'reservation_slot_id' // Klucz obcy w reservation_slot_set wskazujący na ReservationSlot
            ],
            [
                'id', // Klucz lokalny w Customer
                'id', // Klucz lokalny w Reservation
                'id' // Klucz lokalny w ReservationSlot
            ]
        )->withPivot('set_id');
    }

	public function latestReservation(): HasOne
	{
		return $this->hasOne(Reservation::class)->latest();
	}

	public function smsCodes(): HasMany
	{
		return $this->hasMany(SmsCode::class);
	}

	public function latestSmsCode(): HasOne
	{
		return $this->hasOne(SmsCode::class)->latest();
	}

	public function getValidCode(string $code)
	{
		return $this->smsCodes()
			->where('code', $code)
			->where('active', true)
			->first();
	}

	public function tags(): BelongsToMany
	{
		return $this->belongsToMany(Tag::class);
	}

	public function isPasswordRecoveryTokenValid(string $token): bool
	{
		return md5($this->recovery_password_token_base) === $token;
	}

	public function fullName(): Attribute
	{
		return Attribute::make(get: fn() => $this->first_name . ' ' . $this->last_name);
	}

	public function displayName(): Attribute
	{
		return Attribute::make(
			get: fn() => $this->first_name . ' ' . $this->last_name . ' (' . $this->email . ')'
		);
	}

	public function presence(): Attribute
	{

			// If the game has the <person_as_slot> feature, we need to count the number of Reservations, not ReservationSlots.
			// ReservationSlot for a game with this feature is one person.
			$gamesWithPersonAsSlotIds = [];
			foreach (Game::getCached() as $game) {
				foreach ($game->features as $feature) {
					if ($feature->type === 'person_as_slot') {
						$gamesWithPersonAsSlotIds[] = $game->id;
						break;
					}
				}
			}
			$reservationSlotPresenceNumbers = ReservationSlot::whereHas('reservation', function ($query) use (
				$gamesWithPersonAsSlotIds
			) {
				$query->whereNotIn('game_id', $gamesWithPersonAsSlotIds);
                $query->where('customer_id', $this->id);
			})
				->groupBy('presence')
				->selectRaw('presence, count(*) as c')
				->get();
			$reservationPresenceNumbers = Reservation::selectRaw(
				'reservation_slots.presence, COUNT(reservations.id) as c'
			)
                ->where('customer_id', $this->id)
				->joinSub(
					function ($query) {
						$query
							->select('reservation_id', DB::raw('MIN(id) as min_id'))
							->from('reservation_slots')
							->groupBy('reservation_id');
					},
					'first_slots',
					function ($join) {
						$join->on('reservations.id', '=', 'first_slots.reservation_id');
					}
				)
				->join('reservation_slots', function ($join) {
					$join->on('first_slots.min_id', '=', 'reservation_slots.id');
				})
				->groupBy('reservation_slots.presence')
				->get();

			$count = $reservationSlotPresenceNumbers->sum('c');
			$count += $reservationPresenceNumbers->sum('c');

			$presenceTrue = $reservationSlotPresenceNumbers->where('presence', true)->first()->c ?? 0;
			$presenceTrue += $reservationPresenceNumbers->where('presence', true)->first()->c ?? 0;
            $presence = round(($presenceTrue / $count) * 100, 2);

		return Attribute::make(get: fn() => $presence);
	}

	public function scopeFilter($query, $filter)
	{
		return match ((int) $filter) {
			CustomerFilter::New->value => $query->filterNew(),
			CustomerFilter::MostLoyal->value => $query->filterMostLoyal(),
			CustomerFilter::Inactive->value => $query->filterInactive(),
			CustomerFilter::FirstReservation->value => $query->filterFirstReservation(),
			default => $query,
		};
	}

	public function scopeFilterNew($query)
	{
		return $query
			->whereDoesntHave('reservations', function ($query) {
				$query->whereHas('reservationSlots', function ($query) {
					$query->whereIn('status', [
						ReservationSlotStatus::Confirmed->value,
						ReservationSlotStatus::Pending->value,
					]);
					$query->whereNull('cancelation_type');
					$query->where('start_at', '<=', now());
				});
			})
			->whereHas('reservations', function ($query) {
				$query->whereHas('reservationSlots', function ($query) {
					$query->whereIn('status', [
						ReservationSlotStatus::Confirmed->value,
						ReservationSlotStatus::Pending->value,
					]);
					$query->whereNull('cancelation_type');
					$query->where('start_at', '>', now());
				});
			});
	}

	public function scopeFilterMostLoyal($query)
	{
		return $query
			->withCount([
				'reservations as most_loyal_reservations_count' => function ($query) {
					$query->whereHas('reservationSlots', function ($query) {
						$query->whereIn('status', [
							ReservationSlotStatus::Confirmed->value,
							ReservationSlotStatus::Pending->value,
						]);
						$query->whereNull('cancelation_type');
						$query->where('start_at', '>=', now()->subDays(90));
						$query->where('start_at', '<=', now());
					});
				},
			])
			->having('most_loyal_reservations_count', '>=', 2);
	}

	public function scopeFilterInactive($query)
	{
		return $query->whereDoesntHave('reservations', function ($query) {
			$query->whereHas('reservationSlots', function ($query) {
				$query->whereIn('status', [
					ReservationSlotStatus::Confirmed->value,
					ReservationSlotStatus::Pending->value,
				]);
				$query->whereNull('cancelation_type');
				$query->where('start_at', '>', now()->subDays(30));
			});
		});
	}

	public function scopeFilterFirstReservation($query)
	{
		return $query
			->whereDoesntHave('reservations', function ($query) {
				$query->whereHas('reservationSlots', function ($query) {
					$query->whereIn('status', [
						ReservationSlotStatus::Confirmed->value,
						ReservationSlotStatus::Pending->value,
					]);
					$query->whereNull('cancelation_type');
					$query->where('start_at', '>=', now());
				});
			})
			->whereDoesntHave('reservations', function ($query) {
				$query->whereHas('reservationSlots', function ($query) {
					$query->whereIn('status', [
						ReservationSlotStatus::Confirmed->value,
						ReservationSlotStatus::Pending->value,
					]);
					$query->whereNull('cancelation_type');
					$query->where('start_at', '<=', now()->subDays(30));
				});
			})
			->withCount([
				'reservations as first_reservation_customers_reservations_count' => function ($query) {
					$query->whereHas('reservationSlots', function ($query) {
						$query->where('start_at', '>=', now()->subDays(30));
						$query->where('start_at', '<=', now());
						$query->whereIn('status', [
							ReservationSlotStatus::Confirmed->value,
							ReservationSlotStatus::Pending->value,
						]);
						$query->whereNull('cancelation_type');
					});
				},
			])
			->having('first_reservation_customers_reservations_count', '=', 1);
	}

	protected function widgetChannel(): Attribute
	{
		return Attribute::make(
			get: function (string|null $value) {
				if (!request()->routeIs('widget.*')) {
					return $value;
				}
				if (
					$this->widget_channel_expiration &&
						Carbon::createFromFormat(
							'Y-m-d H:i:s',
							$this->widget_channel_expiration->format('Y-m-d H:i:s'),
							'UTC'
						)?->gt(now('UTC')) ?? false
				) {
					$this->update([
						'widget_channel_expiration' => now('UTC')->addMinutes(30),
					]);
					$processChannel = $value;
				} else {
					$processChannel = self::generateWidgetChannel();
					$this->update([
						'widget_channel' => $processChannel,
						'widget_channel_expiration' => now('UTC')->addMinutes(30),
					]);
				}

				return $processChannel;
			}
		);
	}

	public static function generateWidgetChannel(): string
	{
		$processChannel = 'widget' . Str::random();
		while (
			session()->has('widget_channel_' . $processChannel) ||
			self::where('widget_channel', $processChannel)->exists()
		) {
			$processChannel = 'widget' . Str::random();
		}
		session()->put('widget_channel_' . $processChannel, true);

		return $processChannel;
	}

	protected function recoveryPasswordTokenBase(): Attribute
	{
		return Attribute::make(get: fn() => $this->id . $this->password . now('UTC')->format('Y-m-d'));
	}

	public static function getWidgetChannel(Customer|null $customer): string
	{
		if ($customer) {
			$channel = $customer->widget_channel;
		} else {
			$channel = 'widget' . Str::random();
			while (
				session()->has('widget_channel_' . $channel) ||
				Customer::where('widget_channel', $channel)->exists()
			) {
				$channel = 'widget' . Str::random();
			}
		}
		session()->put('widget_channel_' . $channel, true);
		return $channel;
	}

	protected function onlineActiveReservationsCount(): Attribute
	{
		$customerId = $this->getRawOriginal('id');
		$getResult = Cache::remember(
			'customer:' . $customerId . ':online_active_reservations_count',
			30,
			function () {
                $onlinePaymentMethods = PaymentMethod::where('online', true)->pluck('id');
				return $this->reservations()
					->whereHas('reservationSlots', function ($query) {
						$query->whereNull('canceled_at');
						$query->where('status', ReservationSlotStatus::Pending);
					})
					->whereIn('payment_method_id', $onlinePaymentMethods)
					->count();
			}
		);
		return Attribute::make(get: fn() => $getResult);
	}

	protected function offlineTodayReservationsCounts(): Attribute
	{
		$customerId = $this->getRawOriginal('id');
		$getResult = Cache::remember(
			'customer:' . $customerId . ':offline_today_reservations_count',
			30,
			function () {
                $offlinePaymentMethodIds = PaymentMethod::where('online', false)->pluck('id');
				return $this->reservations()
					->whereDoesntHave('reservationSlots', function ($query) {
						$query->whereNotNull('canceled_at');
						$query->orWhere('status', ReservationSlotStatus::Confirmed);
					})
					->whereHas('firstReservationSlot', function ($query) {
						$query->where('start_at', '>', now('UTC'));
					})
					->whereIn('payment_method_id',$offlinePaymentMethodIds)
					->groupBy('game_id')
					->selectRaw('game_id, count(*) as c')
					->pluck('c', 'game_id');
			}
		);
		return Attribute::make(get: fn() => $getResult);
	}

	public static function getAnonymousCustomerActiveOfflineReservationsCounts(Club $club): array
	{
		$result = [];
		foreach (
			$club->getGames()->whereIn(
				'id',
				$club
					->games()
					->pluck('game_id')
					->toArray()
			)
			as $game
		) {
			$result[$game->id] = Reservation::where('customer_ip', request()->ip())
				->whereDoesntHave('reservationSlots', function ($query) {
					$query->where('status', ReservationSlotStatus::Confirmed);
				})
				->whereHas('firstReservationSlot', function ($query) {
					$query->where('start_at', '>', now());
				})
				->whereHas('paymentMethod', function ($query) {
					$query->where('online', false);
				})
				->count();
		}
		return $result;
	}

	public static function getAnonymousCustomerActiveOnlineReservationsCount()
	{
		return Reservation::where('customer_ip', request()->ip())
			->whereHas('firstReservationSlot', function ($query) {
				$query->where('status', ReservationSlotStatus::Pending);
			})
			->whereHas('paymentMethod', function ($query) {
				$query->where('online', true);
			})
			->count();
	}

	public function syncTags(array $tags): void
	{
		$this->tags()->detach();
		foreach ($tags as $tag) {
			$this->tags()->attach(Tag::firstOrCreate(['name' => $tag]));
		}
	}

	public function resendVerification()
	{
		if ($this->verified) {
			return false;
		}

		$club = $this->club;

		$methodKey = match ($club->customer_verification_type) {
			CustomerVerificationMethod::MAIL => 'mail',
			CustomerVerificationMethod::SMS => 'sms',
		};

		if ($club->customer_verification_type === CustomerVerificationMethod::SMS) {
			$smsSentToday = $this->reminders()
				->where('type', ReminderType::RegisterCustomer)
				->where('method', ReminderMethod::Sms)
				->where('created_at', '>=', now('UTC')->subHour());

			if ($smsSentToday->count() >= 3) {
				return false;
			}
		}

		$customer = $this;

		RateLimiter::attempt(
			"customer-verification-$methodKey-sent" . $this->id,
			1,
			static function () use ($customer, $methodKey) {
                $customer->reminders()->create([
                    'method' => $methodKey,
                    'type' => ReminderType::RegisterCustomer,
                    'real' => 1,
                ]);
				$customer->notify(new VerificationNotification([$methodKey]));
			},
			3
		);

		return true;
	}
}
