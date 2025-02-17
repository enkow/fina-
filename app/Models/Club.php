<?php

namespace App\Models;

use App\Custom\Fakturownia;
use App\Enums\AnnouncementType;
use App\Enums\OnlinePayments;
use App\Enums\ReminderMethod;
use App\Enums\ReservationSource;
use App\Enums\CustomerVerificationMethod;
use App\Exceptions\MissingBillingDetailsException;
use App\Exceptions\MissingPaymentMethodException;
use App\Http\Resources\ClubResource;
use App\Http\Resources\CustomerResource;
use App\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use JsonException;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Club extends BaseModel
{
    use HasFactory, SoftDeletes, HasRelationships, Searchable;

    protected $fillable = [
        'country_id',
        'name',
        'slug',
        'description',
        'email',
        'address',
        'postal_code',
        'city',
        'phone_number',
        'vat_number',
        'invoice_emails',
        'first_login_message',
        'first_login_message_showed',
        'panel_enabled',
        'sets_enabled',
        'calendar_enabled',
        'timer_enabled',
        'widget_enabled',
        'widget_countries',
        'aggregator_enabled',
        'online_payments_enabled',
        'offline_payments_enabled',
        'customer_registration_required',
        'stripe_customer_id',
        'billing_name',
        'billing_address',
        'billing_nip',
        'billing_postal_code',
        'billing_city',
        'preview_mode',
        'invoice_lang',
        'invoice_autosend',
        'invoice_advance_payment',
        'invoice_next_month',
        'invoice_next_year',
        'invoice_autopay',
        'invoice_payment_time',
        'invoice_last',
        'vat',
        'fakturownia_id',
        'created_at',
        'sms_notifications_online',
        'sms_notifications_offline',
        'customer_verification_type',
        'subscription_active',
        'sms_price_offline',
        'sms_price_online',
    ];

    protected $casts = [
        'panel_enabled' => 'boolean',
        'sms_notifications_online' => 'boolean',
        'sms_notifications_offline' => 'boolean',
        'invoice_emails' => 'array',
        'widget_countries' => 'array',
        'timer_enabled' => 'boolean',
        'widget_enabled' => 'boolean',
        'calendar_enabled' => 'boolean',
        'aggregator_enabled' => 'boolean',
        'sets_enabled' => 'boolean',
        'customer_registration_required' => 'boolean',
        'offline_payments_enabled' => 'boolean',
        'first_login_message_showed' => 'boolean',
        'online_payments_enabled' => OnlinePayments::class,
        'customer_verification_type' => CustomerVerificationMethod::class,
        'preview_mode' => 'boolean',
        'invoice_autosend' => 'boolean',
        'invoice_advance_payment' => 'boolean',
        'invoice_autopay' => 'boolean',
        'invoice_last' => 'boolean',
        'subscription_active' => 'boolean',
    ];

    public static function getEncryptedWidgetChannel($channel): string
    {
        return Crypt::encrypt($channel . Crypt::encrypt(config('app.name')));
    }

    public static function getDecryptedWidgetChannel($encryptedChannel): string
    {
        $channelEncrypted = Crypt::decrypt($encryptedChannel);
        $channelEncrypted = str_replace(Crypt::encrypt(config('app.name')), '', $channelEncrypted);

        return Crypt::decrypt($channelEncrypted);
    }

    public static function getClub(int|null $clubId): self|null
    {
        return Cache::remember('club:' . $clubId, config('cache.model_cache_time'), function () use (
            $clubId
        ) {
            return $clubId
                ? self::where('id', $clubId)
                    ->with('country')
                    ->first()
                : null;
        });
    }

    public static function getCachedGames($club)
    {
        return Cache::remember('club:' . $club->id . ':games', config('cache.model_cache_time'), function () use ($club) {
            return $club->games()->with('features', 'features.game', 'features.translations')->get();
        });
    }

    public function getCalendarResource(Game $game = null): ClubResource
    {
        $result = Cache::remember(
            'club:' . $this->id . ':calendar_resource',
            config('cache.model_cache_time'),
            function () use ($game) {
                $result = club()?->load([
                    'country',
                    'games',
                    'games.features' => fn($q) => $q->when($game, fn($q) => $q->where('game_id', $game->id)),
                    'discountCodes' => fn($query) => $query
                        ->where('active', true)
                        ->withCount(['reservations']),
                    'specialOffers' => fn($query) => $query->where('active', true),
                    'reservationTypes',
                ]);
                $result->discountCodes = $result->discountCodes->filter(
                    fn($discountCode) => $discountCode->isAvailable()
                );
                return $result;
            }
        );

        return new ClubResource($result);
    }

    public function createEmptyPricelist($game): Pricelist
    {
        $pricelist = $this->pricelists()->create([
            'game_id' => $game->id,
            'name' => 'default',
        ]);
        for ($i = 1; $i <= 7; $i++) {
            $pricelist->pricelistItems()->create([
                'day' => $i,
                'from' => '06:00:00',
                'to' => '24:00:00',
                'price' => '0',
            ]);
        }
        return $pricelist;
    }

    public function getSettings(): Collection
    {
        return Cache::remember(
            'club:' . $this->id . ':settings',
            config('cache.model_cache_time'),
            function () {
                return $this->settings->load('feature', 'feature.game');
            }
        );
    }

    public static function getCachedGamesForSettings($club)
    {
        return Cache::remember(
            'club:' . $club->id . ':games_for_settings',
            config('cache.model_cache_time'),
            function () use ($club) {
                return $club->games->load('features', 'features.translations', 'features.game');
            }
        );
    }

    public function getGames(): Collection
    {
        return Cache::remember('club:' . $this->id . ':games', config('cache.model_cache_time'), function () {
            return $this->games->load('features');
        });
    }

    public function getAnnouncements(): Collection
    {
        return Cache::remember(
            'club:' . $this->id . ':announcements',
            config('cache.model_cache_time'),
            function () {
                return $this->announcements;
            }
        );
    }

    public function getAgreements(): Collection
    {
        return Cache::remember(
            'club:' . $this->id . ':agreements',
            config('cache.model_cache_time'),
            function () {
                return $this->agreements;
            }
        );
    }

    public function flushCache(): void
    {
        Cache::forget('club:' . $this->id . ':settings');
        Cache::forget('club:' . $this->id . ':opening_hours_exceptions');
        Cache::forget('club:' . $this->id . ':opening_hours');
        Cache::forget('club:' . $this->id . ':agreements');
        Cache::forget('club:' . $this->id . ':announcements');
        Cache::forget('club:' . $this->id . ':games');
        Cache::forget('club:' . $this->id . ':calendar_resource');
        Cache::forget('club:' . $this->id . ':widget_resource');
        Cache::forget('club:' . $this->id . ':widget_features_data');
        Cache::forget('club:' . $this->id . ':widget_games_data');
        Cache::forget('club:' . $this->id . ':rendered_settings');
        Cache::forget('club:' . $this->id . ':settings_with_bulb_adapter');
        Cache::forget('club:' . $this->id . ':slots');
        Cache::forget('club:' . $this->id . ':sets');
        Cache::forget('club:' . $this->id);
        foreach (Game::getCached() as $game) {
            Cache::forget('club:' . $this->id . ':game:' . $game->id . ':slots');
        }
        foreach ($this->users as $user) {
            Cache::forget('user:' . $user->id . ':data');
            $user->flushCache();
        }
    }

    protected function defaultWidgetLocale(): Attribute
    {
        return Attribute::make(
            get: fn() => strtoupper(
                $this->widget_countries[0] ?? Country::getCountry($this->country_id)->code
            )
        );
    }

    public function smsCount(): Attribute
    {
        $lastMonth = $this->getBillingPeriod()['month']['start_at'];

        return Attribute::make(
            get: fn() => [
                'all' => [
                    'offline' => $this->remindersReservation()
                        ->where('method', ReminderMethod::Sms)
                        ->where('real', true)
                        ->whereHasMorph('remindable', [Reservation::class], function ($query) {
                            return $query->where('source', ReservationSource::Panel);
                        })
                        ->count(),
                    'online' => $this->remindersReservation()
                        ->where('method', ReminderMethod::Sms)
                        ->where('real', true)
                        ->whereHasMorph('remindable', [Reservation::class], function ($query) {
                            return $query->where('source', ReservationSource::Widget);
                        })
                        ->count(),
                ],
                'month' => [
                    'offline' => $this->remindersReservation()
                        ->where('method', ReminderMethod::Sms)
                        ->whereNull('invoice_item_id')
                        ->where('real', true)
                        ->whereHasMorph('remindable', [Reservation::class], function ($query, $lastMonth) {
                            return $query
                                ->where('source', ReservationSource::Panel)
                                ->where('created_at', '>=', $lastMonth . ' 00:00:00');
                        })
                        ->count(),
                    'online' => $this->remindersReservation()
                        ->where('method', ReminderMethod::Sms)
                        ->whereNull('invoice_item_id')
                        ->where('real', true)
                        ->whereHasMorph('remindable', [Reservation::class], function ($query, $lastMonth) {
                            return $query
                                ->where('source', ReservationSource::Widget)
                                ->where('created_at', '>=', $lastMonth . ' 00:00:00');
                        })
                        ->count(),
                ],
            ]
        );
    }

    public function reminders(): Attribute
    {
        return Attribute::make(get: fn() => $this->remindersReservation->concat($this->remindersCustomers));
    }

    public function remindersReservation()
    {
        return $this->hasManyDeep(
            Reminder::class,
            [Pricelist::class, Slot::class, ReservationSlot::class, Reservation::class],
            ['club_id', 'pricelist_id', 'slot_id', 'id', 'remindable_id'],
            ['id', 'id', 'id', 'reservation_id', 'id']
        );

        // $clubID = $this->id;

        // return Reminder::where('remindable_type', (new Reservation)->getMorphClass())->whereHas('remindable.slot.pricelist.club', function ($query) use ($clubID) {
        // 	$query->where('id', $clubID);
        // });
    }

    public function remindersCustomers(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->customers()
                ->with('reminders')
                ->get()
                ->pluck('reminders')
                ->collapse()
        );
    }

    public function agreements(): HasMany
    {
        return $this->hasMany(Agreement::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function slots(): HasManyThrough
    {
        return $this->hasManyThrough(Slot::class, Pricelist::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function reservationTypes(): HasMany
    {
        return $this->hasMany(ReservationType::class);
    }

    public function discountCodes(): HasMany
    {
        return $this->hasMany(DiscountCode::class);
    }

    public function specialOffers(): HasMany
    {
        return $this->hasMany(SpecialOffer::class);
    }

    public function reservationSlots(): HasManyDeep
    {
        return $this->hasManyDeep(ReservationSlot::class, [Pricelist::class, Slot::class]);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function pricelists(): HasMany
    {
        return $this->hasMany(Pricelist::class);
    }

    public function tags(): HasManyThrough
    {
        return $this->hasManyDeep(Tag::class, [Customer::class, 'customer_tag']);
    }

    public function translations(): HasManyThrough
    {
        return $this->hasManyDeep(Translation::class, ['club_game', Game::class]);
    }

    public function managerEmails(): HasMany
    {
        return $this->hasMany(ManagerEmail::class);
    }

    public function managers(): HasMany
    {
        return $this->users()->where('type', 'manager');
    }

    /**
     * @return array
     * @throws JsonException
     */
    public function getClubGameData(): array
    {
        $customGameNames = [];
        foreach ($this->games as $game) {
            $customGameNames[$game->id]['names'] = json_decode(
                $game->pivot->custom_names,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            $customGameNames[$game->id]['fee_fixed'] = json_decode(
                $game->pivot->fee_fixed,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            $customGameNames[$game->id]['fee_percent'] = json_decode(
                $game->pivot->fee_percent,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        return $customGameNames;
    }

    public function getWidgetProps(Customer $customer = null, array $arrayToMerge = []): array
    {
        $countries = Country::getCached();
        $games = Club::getCachedGames($this);
        $languages = $this->widget_countries;
        if($languages === null) {
            $languagesArray = config('app.locales');
        } else {
            foreach($languages as $language) {
                $country_lang = Country::where('code', $language)->first()->locale;
                if($country_lang !== null) {
                    $languagesArray[] = $country_lang;
                }
            }
        }

        $gamesNames = $generalTranslations = [];
        $preloadedTranslations = Translation::where('translatable_type', Game::class)
            ->whereIn(
                'country_id',
                $countries
                    ->whereIn('locale', $languagesArray)
                    ->where('active', true)
                    ->pluck('id')
                    ->toArray()
            )
            ->get();

        foreach ($languagesArray as $locale) {
            $country = $countries
                ->where('locale', $locale)
                ->where('active', true)
                ->first();
            if (!empty($country)) {
                $countryPreloadedTranslations = $preloadedTranslations->where('country_id', $country->id);
                if (!empty($country)) {
                    $gamesNames[$locale] = Translation::retrieveSpecificGameNames(
                        $this,
                        $country->id,
                        $countryPreloadedTranslations,
                        $games,
                    );
                    $generalTranslations[$locale] = ((bool) auth()->user())
                        ? Translation::retrieve(
                            $country->id,
                            gameId: 0,
                            featureId: 0,
                            preloadedTranslations: $countryPreloadedTranslations
                        )
                        : [];
                }
            }
        }

        return array_merge(
            [
                'club' => $this->getWidgetResource(),
                'customer' => $customer ? new CustomerResource($customer) : null,
                'gamesNames' => $gamesNames,
                'settings' => $this->getRenderedSettings(),
                'generalTranslations' => $generalTranslations,
            ],
            $arrayToMerge
        );
    }

    public function getWidgetResource(): ClubResource
    {
        return Cache::remember(
            'club:' . $this->id . ':widget_resource',
            config('cache.model_cache_time'),
            function () {
                return new ClubResource(
                    $this->load([
                        'country',
                        'games.features',
                        'sets',
                        'paymentMethod',
                        'specialOffers' => function ($query) {
                            return $query->where('special_offers.active', 1);
                        },
                        'announcements' => function ($query) {
                            return $query->whereIn('type', [AnnouncementType::Widget, AnnouncementType::Calendar])->where('start_at', '<=', Carbon::today())->where('end_at', '>=', Carbon::today())->where('start_at', "<", Carbon::tomorrow());
                        },
                    ])
                );
            }
        );
    }

    public function getManagerEmails()
    {
        return Cache::remember(
            'club:' . $this->id . ':manager_emails',
            config('cache.model_cache_time'),
            function () {
                return $this->managerEmails;
            }
        );
    }

    public function getSets()
    {
        return Cache::remember('club:' . $this->id . ':sets', config('cache.model_cache_time'), function () {
            return $this->sets;
        });
    }

    public function getRenderedSettings(): array
    {
        return Setting::retrieve('club', $this->id);
    }

    public function countriesUsedByClub()
    {
        $languages = $this->widget_countries;
        if($languages === null) {
            $languagesArray = config('app.locales');
        } else {
            foreach($languages as $language) {
                $country_lang = Country::where('code', $language)->first();
                if($country_lang !== null) {
                    $languagesArray[] = $country_lang->locale;
                }
            }
        }
        return $languagesArray;
    }

    public function getAvailableWidgetSteps(): array
    {
        $availableSteps = [];
        if (
            count($this->getGames()) > 1 ||
            Setting::getClubGameSetting($this->id, 'widget_message')['value']
        ) {
            $availableSteps[] = 'game';
        }
        $availableSteps[] = 'details';
        $availableSteps[] = 'time';
        if (count($this->getSets()->where('active', true))) {
            $availableSteps[] = 'sets';
        }
        $availableSteps[] = 'summary';

        return $availableSteps;
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'club_product', 'club_id', 'product_id')
            ->withTimestamps()
            ->withPivot('id', 'period', 'cost');
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'club_game', 'club_id', 'game_id')
            ->withTimestamps()
            ->withPivot(
                'custom_names',
                'weight',
                'enabled_on_widget',
                'fee_percent',
                'fee_fixed',
                'include_on_invoice_status',
                'include_on_invoice'
            )
            ->orderByPivot('weight', 'desc');
    }

    public function sets(): HasMany
    {
        return $this->hasMany(Set::class);
    }

    public function getAvailableStartHoursForDate(
        string|Carbon $date,
        int $duration = 0,
        $type = 'club'
    ): array {
        $startKey = $type . '_start';
        $endKey = $type . '_end';

        $date = is_string($date) ? now()->parse($date) : $date;
        $clubOpeningHours = $this->getOpeningHoursForDate($date);
        if ($clubOpeningHours['club_closed'] || $clubOpeningHours['reservation_closed']) {
            return [];
        }
        $timeScale = Setting::getClubGameSetting(
            clubId: $this->id,
            settingKey: 'full_hour_start_reservations_status'
        )['value']
            ? 60
            : 30;

        $clubStartParts = explode(':', $clubOpeningHours[$startKey]);
        $start = $date
            ->clone()
            ->hours($clubStartParts[0])
            ->minutes($clubStartParts[1])
            ->seconds($clubStartParts[2])
            ->milliseconds(0);

        if ($clubOpeningHours[$endKey] === '23:59:00') {
            $clubOpeningHours[$endKey] = '24:00:00';
        }
        $clubEndParts = explode(':', $clubOpeningHours[$endKey]);
        $end = $date
            ->clone()
            ->hours($clubEndParts[0])
            ->minutes($clubEndParts[1])
            ->seconds($clubEndParts[2])
            ->milliseconds(0);

        if ($clubOpeningHours[$startKey] > $clubOpeningHours[$endKey]) {
            $end->addDay();
        }

        $reservationMinAdvanceTime =
            (Setting::getClubGameSetting(clubId: $this->id, settingKey: 'reservation_min_advance_time')[
            'value'
            ][weekDay($date) - 1] ??
                0) *
            60;
        // we need to add one day to end time if it is less than start time (club is open after midnight)
        if ($end->lt($start)) {
            $end->addDay();
        }

        $result = [];
        $end->subMinutes($duration);

        while ($start->lte($end)) {
            if (
                now()
                    ->addMinutes($reservationMinAdvanceTime)
                    ->lte($start)
            ) {
                $result[] = $start->format('Y-m-d H:i:s');
            }
            $start->addMinutes($timeScale);
        }
        return $result;
    }

    public function getOpeningHoursForDate(Carbon|string $date): array
    {
        $date = is_string($date) ? now()->parse($date) : $date;
        $date = $date->format('Y-m-d');

        $openingHours =
            $this->getOpeningHoursExceptions()
                ->where('end_at', '>=', $date)
                ->where(
                    'start_at',
                    '<',
                    now('UTC')
                        ->parse($date)
                        ->addDay()
                )
                ->first() ??
            $this->getOpeningHours()
                ->where('day', weekDay($date))
                ->first();

        return $openingHours?->only([
            'club_start',
            'club_end',
            'club_closed',
            'open_to_last_customer',
            'reservation_start',
            'reservation_end',
            'reservation_closed',
        ]);
    }

    public function getOpeningHoursExceptions(): Collection
    {
        return Cache::remember(
            'club:' . $this->id . ':opening_hours_exceptions',
            config('cache.model_cache_time'),
            function () {
                return $this->openingHoursExceptions ?? [];
            }
        );
    }

    public function getOpeningHours(): Collection
    {
        return Cache::remember(
            'club:' . $this->id . ':opening_hours',
            config('cache.model_cache_time'),
            function () {
                return $this->openingHours ?? [];
            }
        );
    }

    public function openingHoursExceptions(): HasMany
    {
        return $this->hasMany(OpeningHoursException::class);
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(OpeningHours::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function paymentMethod(): HasOne
    {
        return $this->hasOne(PaymentMethod::class)->where('enabled', '=', '1');
    }

    public function getOnlinePaymentMethod(): PaymentMethod|null
    {
        if ($this->online_payments_enabled === OnlinePayments::Internal) {
            return PaymentMethod::whereNull('club_id')
                ->where('type', $this->paymentMethod->type ?? $this->country->payment_method_type)
                ->where('online', true)
                ->first();
        } elseif ($this->online_payments_enabled === OnlinePayments::External) {
            return $this->paymentMethod;
        }

        return null;
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getCommission(int $amount): int
    {
        $additionalCommissionPercent =
            Setting::getClubGameSetting($this->id, 'additional_commission_percent')['value'] / 100;
        $additionalCommissionFixed = Setting::getClubGameSetting($this->id, 'additional_commission_fixed')[
        'value'
        ];

        return round(($amount * $additionalCommissionPercent) / 100) + $additionalCommissionFixed;
    }
    public function isDatetimeRangeWithinOpeningHours($start, $end): bool
    {
        for ($i = -1; $i <= 1; $i++) {
            $currentDate = $start->clone()->addDays($i);
            $openingHours = $this->getOpeningHoursForDate($currentDate->format('Y-m-d H:i:s'));
            // we can't save 24:00:00 in mysql time field, so we store 23:59:00 instead
            if ($openingHours['club_end'] === '23:59:00') {
                $openingHours['club_end'] = '24:00:00';
            }

            $clubStart = now()->parse(
                $start
                    ->clone()
                    ->addDays($i)
                    ->format('Y-m-d') .
                ' ' .
                $openingHours['club_start']
            );
            $clubEnd = now()->parse(
                $start
                    ->clone()
                    ->addDays($i)
                    ->format('Y-m-d') .
                ' ' .
                $openingHours['club_end']
            );
            if ($clubEnd->lt($clubStart)) {
                $clubEnd = $clubEnd->addDay();
            }
            if (!$openingHours['club_closed'] && $clubStart->lte($start) && $clubEnd->gte($end)) {
                return true;
            }
        }
        return false;
    }

    public function getBillingPeriod()
    {
        $result = [
            'month' => [
                'start_at' => null,
                'end_at' => null,
            ],
            'year' => [
                'start_at' => null,
                'end_at' => null,
            ],
            'payment_time' => $this->invoice_advance_payment ? 'start_at' : 'end_at',
        ];

        if ($this->invoice_advance_payment) {
            if ($this->invoice_next_month !== null) {
                $endAt = strtotime($this->invoice_next_month . ' +1 month');
                $endAt = strtotime('-1 day', $endAt);
                $result['month'] = [
                    'start_at' => date('Y-m-d', strtotime($this->invoice_next_month)),
                    'end_at' => date('Y-m-d', $endAt),
                ];
            }

            if ($this->invoice_next_year !== null) {
                $endAt = strtotime($this->invoice_next_year . ' +1 year');
                $endAt = strtotime('-1 day', $endAt);
                $result['year'] = [
                    'start_at' => date('Y-m-d', strtotime($this->invoice_next_year)),
                    'end_at' => date('Y-m-d', $endAt),
                ];
            }
        } else {
            if ($this->invoice_next_month !== null) {
                $result['month'] = [
                    'start_at' => date('Y-m-d', strtotime($this->invoice_next_month . ' -1 month')),
                    'end_at' => date('Y-m-d', strtotime($this->invoice_next_month)),
                ];
            }

            if ($this->invoice_next_year !== null) {
                $result['year'] = [
                    'start_at' => date('Y-m-d', strtotime($this->invoice_next_year . ' -1 year')),
                    'end_at' => date('Y-m-d', strtotime($this->invoice_next_year)),
                ];
            }
        }

        return $result;
    }

    public function getCustomersTablePreference(): array
    {
        $customersTablePreference = ['full_name', 'email', 'phone', 'latest_reservation'];
        foreach (
            $this->agreements()
                ->where('active', true)
                ->pluck('type')
            as $agreementType
        ) {
            $customersTablePreference[] = "agreement_$agreementType->value";
        }
        $customersTablePreference = array_merge($customersTablePreference, ['reservations_count', 'tags']);
        return array_map(
            static fn(string $column) => [
                'key' => $column,
                'enabled' => true,
            ],
            $customersTablePreference
        );
    }

    public function getSlots()
    {
        $clubId = clubId() ?? $this->id;
        return Cache::remember(
            'club:' . $clubId . ':slots',
            config('cache.model_cache_time'),
            function () use ($clubId) {
                $slots = Slot::whereHas('pricelist', function ($query) use ($clubId) {
                    $query->where('club_id', $clubId);
                })
                    ->with('features')
                    ->whereHas('pricelist', function ($query) {
                        $query->whereHas('game', function ($query) {
                            //we do not load game slots with the person_as_slot feature because there are too many of them and they would burden queries
                            $query->whereDoesntHave('features', function ($query) {
                                $query->where('type', 'person_as_slot');
                            });
                        });
                    })
                    ->with([
                        'parentSlot' => function ($query) {
                            $query->select('name');
                        },
                        'pricelist.game',
                    ])
                    ->get();

                return $slots->groupBy(function ($slot) {
                    return $slot->pricelist->game_id;
                });
            }
        );
    }

    public function reloadFakturowniaInvoicesData(): void
    {
        Cache::remember('club:' . $this->id . ':fakturownia_reload', 120, function () {
            foreach (
                club()
                    ->invoices()
                    ->whereNotNull('fakturownia_id')
                    ->orWhere('fakturownia_id', '!=', '')
                    ->latest()
                    ->take(3)
                    ->get()
                as $invoice
            ) {
                $fakturowniaInvoice = (new Fakturownia())->getInvoiceData($invoice->fakturownia_id);
                if ($fakturowniaInvoice->status() === 404) {
                    $invoice->update([
                        'fakturownia_id' => null,
                    ]);
                    continue;
                }

                if(!$invoice->sent_at) {
                    $fakturowniaSentAt =
                        (new Fakturownia())->getInvoiceData($invoice->fakturownia_id)['sent_time'] ?? null;
                    if ($fakturowniaSentAt) {
                        $invoice->update([
                            'sent_at' => $fakturowniaSentAt,
                        ]);
                    }
                }
            }
            return 1;
        });
    }

    protected function fakturowniaId(): Attribute
    {
        return Attribute::make(
            get: function (string|null $value) {
                if ($value) {
                    return $value;
                }
                $fakturowniaId = (new Fakturownia())->createClient(['name' => $this->name])['id'];
                $this->update([
                    'fakturownia_id' => $fakturowniaId,
                ]);
                return $fakturowniaId;
            }
        );
    }
}