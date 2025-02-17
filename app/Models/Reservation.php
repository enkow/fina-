<?php

namespace App\Models;

use App\Custom\Timezone;
use App\Enums\BulbStatus;
use App\Enums\BulbReason;
use App\Enums\DiscountCodeType;
use App\Enums\OnlinePayments;
use App\Enums\ReminderType;
use App\Enums\ReservationReturnStatus;
use App\Enums\ReservationSlotStatus;
use App\Enums\ReservationSource;
use App\Filters\Reservation\CancelationTypeFilter;
use App\Filters\Reservation\GameFilter;
use App\Filters\Reservation\PaymentStatusFilter;
use App\Filters\Reservation\PaymentTypeFilter;
use App\Filters\Reservation\StartAtRangeFilter;
use App\Http\Requests\Club\StoreReservationRequest;
use App\Http\Requests\Club\UpdateReservationRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\DiscountCodeResource;
use App\Http\Resources\FeatureResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\SlotResource;
use App\Http\Resources\SpecialOfferResource;
use App\Interfaces\Payable;
use App\Models\Features\BookSingularSlotByCapacity;
use App\Notifications\Customer\ReservationStoredNotification as CustomerReservationStoredNotification;
use App\Notifications\Manager\ReservationStoredNotification;
use App\Observers\ReservationObserver;
use App\Searchers\Reservation\CustomerEmailSearcher;
use App\Searchers\Reservation\CustomerNameSearcher;
use App\Searchers\Reservation\CustomerPhoneSearcher;
use App\Searchers\Reservation\NumberSearcher;
use App\Searchers\Reservation\ReservationClubNoteSearcher;
use App\Sorters\Reservation\CreatedDatetimeSorter;
use App\Sorters\Reservation\CustomerNameSorter;
use App\Sorters\Reservation\FinalPriceSorter;
use App\Sorters\Reservation\NumberSorter;
use App\Sorters\Reservation\PersonCountSorter;
use App\Sorters\Reservation\SlotNameSorter;
use App\Sorters\Reservation\SlotsCountSorter;
use App\Sorters\Reservation\StartDatetimeSorter;
use App\Sorters\Reservation\StatusSorter;
use App\Sorters\Reservation\TimeRangeSorter;
use App\Traits\Filterable;
use App\Traits\Searchable;
use App\Traits\Sortable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Reservation extends BaseModel implements Payable
{
    use HasFactory, SoftDeletes, HasRelationships, Sortable, Filterable, Searchable, Sortable;

    /**
     * @var array|string[] Fields whose changes will be saved in data_changes table
     */
    public static array $loggable = [
        'game_id',
        'customer_id',
        'customer_note',
        'club_note',
        'return_status',
        'return_id',
        'price',
        'paid_at',
        'payment_method_id',
        'currency',
        'rate_service',
        'rate_staff',
        'rate_atmosphere',
        'rate_content',
    ];

    /**
     * @var array|string[] Classes that can be added in the Filterable trait method
     */
    public static array $availableFilters = [
        GameFilter::class,
        StartAtRangeFilter::class,
        CancelationTypeFilter::class,
        PaymentTypeFilter::class,
        PaymentStatusFilter::class,
    ];

    /**
     * @var array|string[] Classes that can be added in the Sortable trait method
     */
    public static array $availableSorters = [
        CreatedDatetimeSorter::class,
        NumberSorter::class,
        CustomerNameSorter::class,
        TimeRangeSorter::class,
        StartDatetimeSorter::class,
        SlotsCountSorter::class,
        SlotNameSorter::class,
        FinalPriceSorter::class,
        PersonCountSorter::class,
        StatusSorter::class,
    ];
    /**
     * @var array|string[] Classes that can be added in the Searchable trait method
     */
    public static array $availableSearchers = [
        NumberSearcher::class,
        CustomerNameSearcher::class,
        CustomerPhoneSearcher::class,
        CustomerEmailSearcher::class,
        ReservationClubNoteSearcher::class
    ];
    public static array $exportFieldExclusions = [
        'reservation_number_id',
        'reservation_id',
        'reservation_slot_id',
        'parent_slot_id',
        'cancelation_type',
        'cancelation_status',
        'end_datetime',
        'customer_note',
        'club_reservation',
        'show_customer_note_on_calendar',
        'customer_email',
        'club_note',
        'show_club_note_on_calendar',
        'status',
        'payment_method_online',
        'club_commission',
        'invoice_id',
        'invoice_conditions_matched',
        'slot_id',
        'custom_display_name',
        'payment_method_id',
        'display_name',
        'created_at',
        'calendar_color',
        'calendar_name',
        'occupied_status',
        'timer_status',
    ];

    public static array $exportFieldInclusions = [
    ];

    protected $fillable = [
        'source',
        'game_id',
        'unregistered_customer_data',
        'customer_id',
        'customer_note',
        'show_customer_note_on_calendar',
        'club_note',
        'show_club_note_on_calendar',
        'price',
        'payment_method_id',
        'paid_at',
        'currency',
        'club_commission',
        'app_commission',
        'provider_commission',
        'invoice_id',
        'invoice_conditions_matched',
        'rate_service',
        'rate_staff',
        'rate_atmosphere',
        'rate_content',
        'ip',
        'created_at',
    ];

    protected $casts = [
        'source' => ReservationSource::class,
        'paid_at' => 'timestamp',
        'discount_code_snapshot' => 'array',
        'special_offer_snapshot' => 'array',
        'unregistered_customer_data' => 'array',
        'canceled' => 'boolean',
        'show_customer_note_on_calendar' => 'boolean',
        'invoice_conditions_matched' => 'boolean',
        'show_club_note_on_calendar' => 'boolean',
        'return_status' => ReservationReturnStatus::class,
    ];

    public static function getFieldLocaleNames(): array
    {
        return [
            'rate_service' => __('rate.singular') . ' - ' . __('rate.service'),
            'rate_atmosphere' => __('rate.singular') . ' - ' . __('rate.atmosphere'),
            'rate_staff' => __('rate.singular') . ' - ' . __('rate.staff'),
            'rate_final' => __('rate.singular') . ' - ' . __('rate.final'),
            'rate_content' => __('rate.singular') . ' - ' . ucfirst(__('main.content')),
            'customer_note' => __('reservation.customer-note'),
            'club_note' => __('reservation.club-note'),
            'customer_id' => __('customer.singular'),
            'payment_method_id' => ucfirst(__('main.payment-method')),
            'price' => ucfirst(__('main.price')),
            'final_price' => ucfirst(__('main.final-amount')),
        ];
    }

    /**
     * Returns a reservation or table reservation model
     * depending on whether the game has the "person_as_slot" feature.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function getReservations(
        int $clubId = null,
        int $customerId = null,
        int $invoiceId = null,
        bool $paginated = false,
        int $itemsPerPage = 30,
        int $resultsLimit = null,
        bool $active = null,
        bool $canceled = null,
        array $tablePreference = [],
        string $tableName = '',
        string $paginationTableName = null,
        bool $extendedData = false,
        bool $onlyOnlinePayment = false,
        bool $allColumns = false
    ) {
        $game =
                request()?->get('filters')[$tableName]['game'] ?? null
            ? Game::getCached()
            ->where('id', request()?->get('filters')[$tableName]['game'] ?? null)
            ->first()
            : request()->route('game');
        $pricelistIds = Club::getClub($clubId)?->pricelists()->where('game_id',
            $game->id)->pluck('id')->toArray() ?? [];
        if ($game->hasFeature('person_as_slot')) {
            $clubPricelistsIds = Club::getClub($clubId)?->pricelists()->pluck('id')->toArray();
            $slotIds = Slot::whereIn('pricelist_id', $pricelistIds)->pluck('id')->toArray();
            //If the game has a <person as slot> feature, the ReservationSlot models are persons - the main search entity in this case is reservation
            $output = self::with([
                'customer' => fn($q) => $q->withTrashed(),
                'reservationNumber',
                'firstReservationSlot.sets' => fn($q) => $q->select('sets.id', 'sets.name', 'sets.price'),
                'firstReservationSlot.slot' => fn($q) => $q->select('slots.id', 'slots.name', 'slot_id'),
            ])
                ->when($clubId !== null, function ($query) use ($clubId, $clubPricelistsIds, $slotIds) {
                    $query->whereHas('customer', function ($query) use ($clubId) {
                        $query->where('club_id', $clubId);
                    });
                    $query->orWhereHas('firstReservationSlot',
                        function ($query) use ($clubId, $clubPricelistsIds, $slotIds) {
                            $query->whereIn('slot_id', $slotIds);
                        });
                })
                ->when($invoiceId !== null, function ($query) use ($invoiceId) {
                    $query->where('invoice_id', $invoiceId);
                })
                ->when($customerId !== null, function ($query) use ($customerId) {
                    $query->where('customer_id', $customerId);
                })
                ->when($active === true, function ($query) {
                    $query->having('reservation_slots_count', '>', 0);
                })
                ->withCount([
                    'reservationSlots' => function ($query) {
                        $query->active();
                    },
                ])
                ->when($onlyOnlinePayment, function ($query) {
                    $query->whereHas('paymentMethod', function ($query) {
                        $query->where('online', true);
                    });
                })
                ->when(
                    auth()
                        ->user()
                        ?->isType('admin'),
                    function ($query) {
                        $query->whereHas('customer', function ($query) {
                            $query->whereHas('club', function ($query) {
                                $query->where('country_id', auth()->user()->country_id);
                            });
                        });
                    }
                )
                ->when($canceled !== null, function ($query) use ($canceled) {
                    $query->whereHas('firstReservationSlot', function ($query) use ($canceled) {
                        $query->when($canceled, function ($query) {
                            $query->whereNotNull('cancelation_type');
                        });
                        $query->when(!$canceled, function ($query) {
                            $query->whereNull('cancelation_type');
                        });
                    });
                })
                ->filterable($tableName, self::$availableFilters)
                ->searchable($tableName, self::$availableSearchers)
                ->sortable($tableName, self::$availableSorters);
        } else {
            //If the game does not have the <person as slot> feature, the ReservationSlot models are separate reservations, and the reservation model is a reservation group aggregator
            $slotIds = Slot::whereIn('pricelist_id', $pricelistIds)->pluck('id')->toArray();
            $output = ReservationSlot::with([
                'reservationNumber',
                'reservation',
                'sets',
                'features' => fn($q) => $q->whereIn('type', ['price_per_person', 'reservation_slot_has_display_name']),
                'timerEntries',
            ])
                ->when($onlyOnlinePayment, function ($query) {
                    $query->whereHas('reservation', function ($query) {
                        $query->whereHas('paymentMethod', function ($query) {
                            $query->where('online', true);
                        });
                    });
                })
                ->when($customerId !== null, function ($query) use ($customerId) {
                    $customerReservationIds = Reservation::where('customer_id', $customerId)->pluck('id')->toArray();
                    $query->whereIn('reservation_id', $customerReservationIds);
                })
                ->when($invoiceId !== null, function ($query) use ($invoiceId) {
                    $query->whereHas('reservation', function ($query) use ($invoiceId) {
                        $query->where('invoice_id', $invoiceId);
                        $query->where('invoice_conditions_matched', true);
                    });
                })
                ->when(
                    auth()
                        ->user()
                        ?->isType('admin'),
                    function ($query) {
                        $query->whereHas('slot', function ($query) {
                            $query->whereHas('pricelist', function ($query) {
                                $query->whereHas('club', function ($query) {
                                    $query->where('country_id', auth()->user()->country_id);
                                });
                            });
                        });
                    }
                )
                ->when($clubId !== null, function ($query) use ($slotIds) {
                    $query->whereIn('slot_id', $slotIds);
                })
                ->when($canceled !== null, function ($query) use ($canceled) {
                    $query->when($canceled, function ($query) {
                        $query->whereNotNull('cancelation_type');
                    });
                    $query->when(!$canceled, function ($query) {
                        $query->whereNull('cancelation_type');
                    });
                })
                ->when($active === true, function ($query) {
                    $query->active();
                })
                ->filterable($tableName, array_values(array_diff(ReservationSlot::$availableFilters,
                    [\App\Filters\ReservationSlot\GameFilter::class])))
                ->searchable($tableName, ReservationSlot::$availableSearchers)
                ->sortable($tableName, ReservationSlot::$availableSorters);
        }

        if ($paginated === true) {
            $output = $output->paginate($itemsPerPage, ['*'], $paginationTableName);
            $modifyMethodName = 'through';
        } else {
            $output = $output->take($resultsLimit ?? 10000)->get();
            $modifyMethodName = 'map';
        }

        $preloadedCollections = null;
        $preloadedCustomersIds =
            count($output) && $output[0] instanceof ReservationSlot
                ? array_unique($output->pluck('reservation.customer_id')->toArray())
                : $output->pluck('customer_id')->toArray();
        if ($extendedData === true && count($output)) {
            $preloadedCollections['customers'] = Customer::whereIn('id', $preloadedCustomersIds)
                ->withCount('reservations')
                ->withSum('reservationSlots', 'price')
                ->get();
            $preloadedCollections['discountCodes'] = Cache::remember(
                'club_' . clubId() . '_game_' . $game->id . '_discount_codes',
                30,
                function () use ($game) {
                    return club()
                        ->discountCodes()
                        ->when($game, function ($query) use ($game) {
                            $query->where('game_id', $game->id);
                        })
                        ->get();
                }
            );
            $preloadedCollections['specialOffers'] = Cache::remember(
                'club_' . clubId() . '_game_' . $game->id . '_special_offers',
                30,
                function () use ($game) {
                    return club()
                        ->specialOffers()
                        ->when($game, function ($query) use ($game) {
                            $query->where('game_id', $game->id);
                        })
                        ->get();
                }
            );
        }
        if (club() && !$game->hasFeature('person_as_slot')) {
            $preloadedCollections['slots'] = club()->getSlots()[$game->id] ?? [];
        }
        if (club() && ($game->hasFeature('person_as_slot') || $game->hasFeature('slot_has_parent'))) {
            $preloadedCollections['parent_slots'] = Cache::remember(
                'club_' . clubId() . '_game_' . $game->id . '_slots',
                30,
                function () use ($game, $pricelistIds) {
                    return club()
                        ?->slots()
                        ->when($game, function ($query) use ($game, $pricelistIds) {
                            $query->whereIn('pricelist_id', $pricelistIds);
                        })
                        ->whereNull('slot_id')
                        ->get();
                }
            );
        }

        $preloadedCollections['game'] = $game;
        $preloadedCollections['features'] = Feature::getCached();
        $preloadedCollections['club'] = $clubId ? Club::getClub($clubId) : club();
        $preloadedCollections['game_feature_statuses'] = [
            'slot_has_parent' => $game->hasFeature('slot_has_parent'),
            'has_timers' => $game->hasFeature('has_timers'),
        ];

        return $output->$modifyMethodName(function ($outputModel) use (
            $extendedData,
            $tablePreference,
            $preloadedCollections,
            $output,
            $allColumns
        ) {
            if (!$allColumns) {
                return TablePreference::getDataArrayFromModel(
                    $outputModel->prepareForOutput(
                        extendedData: $extendedData,
                        preloadedCollections: $preloadedCollections,
                        output: $output
                    ),
                    $tablePreference,
                    array_merge(
                        ['status_color', 'reservation_type_color', 'start_datetime', 'end_datetime_raw'],
                        $extendedData === true ? ['extended'] : []
                    )
                );
            } else {
                return (array)$outputModel->prepareForOutput(
                    extendedData: $extendedData,
                    preloadedCollections: $preloadedCollections,
                    output: $output
                );
            }
        });
    }

    public static function getReservation(null|int $reservationId): Reservation|null
    {
        return Cache::remember('reservation:' . $reservationId, 3, static function () use ($reservationId) {
            return $reservationId ? Reservation::find($reservationId)->load('reservationSlots') : null;
        });
    }

    public static function getAppCommission(
        Club $club,
        int $gameId,
        Carbon|string|null $startAt = null,
        Carbon|string|null $endAt = null
    ) {
        if ($startAt === null || $endAt === null) {
            return 0;
        }

        $club_game = $club
            ->games()
            ->where('game_id', $gameId)
            ->first();

        $reservations = Reservation::where('game_id', $gameId)
            ->whereHas('reservationSlots', function ($query) use ($club, $club_game, $startAt, $endAt) {
                $query
                    ->whereHas('slot', function ($query) use ($club) {
                        $query->whereHas('pricelist', function ($query) use ($club) {
                            $query->where('club_id', $club->id);
                        });
                    })
                    ->whereIn(
                        'status',
                        json_decode(
                            $club_game->pivot->include_on_invoice_status ?? '[]',
                            true,
                            512,
                            JSON_THROW_ON_ERROR
                        )
                    )
                    ->whereBetween('start_at', [
                        Timezone::convertFromLocal($startAt),
                        Timezone::convertFromLocal($endAt)->addHours(23)->addMinutes(59)->addSeconds(59)
                    ]);
            })
            ->where('source', ReservationSource::Widget)
            ->whereNull('invoice_id');

        return $reservations->sum('app_commission');
    }

    public static function getDisplayName(Customer $customer = null, array $customerData = null): string
    {
        $firstName = $customer?->first_name ?? ($customerData['first_name'] ?? '');
        $lastName = $customer?->last_name ?? ($customerData['last_name'] ?? '');
        if (strlen($firstName) + strlen($lastName) + 1 > 20) {
            return mb_substr($firstName, 0, 1) . '. ' . $lastName;
        }
        return $firstName . ' ' . $lastName;
    }

    // data presented below except extended key is used in reservations showing tables
    // data in extended key is used in reservation modals

    public function prepareForOutput(
        $extendedData = false,
        $preloadedCollections = [],
        $output = null,
        $withoutFields = []
    ): mixed {
        $reservation = (object)[];
        $reservationSlots = $preloadedCollections['reservationSlots'] ?? $this->reservationSlots->load('features');
        $firstReservationSlot = $reservationSlots->first();
        $firstReservationSlot->status = $firstReservationSlot->cancelation_type
            ? ReservationSlotStatus::Expired
            : $firstReservationSlot->status;

        $customer =
            isset($preloadedCollections['customers']) &&
            !empty(
            ($customerTmp = $preloadedCollections['customers']
                ->where('id', $this->customer_id)
                ->first())
            )
                ? $customerTmp
                : Customer::getCustomer($this->customer_id);

        $unregisteredCustomerData = $this->unregistered_customer_data;

        $reservation->customer_name = $customer
            ? $customer->first_name . ' ' . $customer->last_name
            : ($unregisteredCustomerData['first_name'] ?? '') .
            ' ' .
            ($unregisteredCustomerData['last_name'] ?? '');
        $reservation->customer_name = trim($reservation->customer_name);
        $reservation->club_commission_partial = $this->club_commission ?? 0;

        $reservation->customer_phone = $customer->phone ?? ($unregisteredCustomerData['phone'] ?? '');
        $reservation->customer_phone = trim($reservation->customer_phone);

        $json = $this->features->where('type', 'reservation_slot_has_display_name')->first()?->pivot?->data;
        $reservation->display_name = self::getDisplayName($customer, $unregisteredCustomerData);
        $reservation->custom_display_name = $json
            ? json_decode($json, true, 512, JSON_THROW_ON_ERROR)['display_name']
            : null;

        $reservation->club_commission = $this->club_commission ?? 0;

        $reservation->customer_email = $customer->email ?? ($unregisteredCustomerData['email'] ?? '');
        $reservation->customer_email = trim($reservation->customer_email);

        $reservation->created_at = $this->created_at->format('Y-m-d H:i:s');
        $reservation->game_name = Translation::retrieveGameNames(club())[$this->game_id];
        $reservation->reservation_id = $this->id;
        $reservation->reservation_number_id = $this->reservationNumber?->id ?? $firstReservationSlot->reservationNumber->id;
        $reservation->number = $this->getNumber();

        $reservation->parent_slot_name = '';
        $reservation->parent_slot_id = null;
        if (
            Slot::getSlot($firstReservationSlot->slot_id)->slot_id &&
            !in_array('parent_slot_name', $withoutFields, true)
        ) {
            if (isset($preloadedCollections['parent_slots'])) {
                $reservation->parent_slot_name = $preloadedCollections['parent_slots']
                    ?->where('id', $firstReservationSlot->slot->slot_id)
                    ->first()->name;
                $reservation->parent_slot_id = $preloadedCollections['parent_slots']
                    ?->where('id', $firstReservationSlot->slot->slot_id)
                    ->first()->id;
            } else {
                $reservation->parent_slot_name = $firstReservationSlot->slot->parentSlot?->name;
                $reservation->parent_slot_id = $firstReservationSlot->slot->parentSlot?->id;
            }
        }

        $reservation->start_date = $firstReservationSlot->start_at->format('Y-m-d');

        $reservation->reservation_time_range = $this->reservationTimeRange(
            $firstReservationSlot->start_at,
            $firstReservationSlot->end_at
        );

        $paymentMethod = PaymentMethod::getPaymentMethod($this->payment_method_id);

        $reservation->calendar_color = match (true) {
            $firstReservationSlot->club_reservation => '#9AA1B3',
            $firstReservationSlot->status === ReservationSlotStatus::Confirmed => '#1BC5BD',
            $firstReservationSlot->status === ReservationSlotStatus::Pending && $paymentMethod->online
            => '#FFAA07',
            $firstReservationSlot->status === ReservationSlotStatus::Pending && !$paymentMethod->online
            => '#3699FF',
            default => $firstReservationSlot->status_color,
        };
        $reservation->reservation_type_color = in_array('reservation_type_color', $withoutFields, true)
            ? []
            : ($firstReservationSlot->reservation_type_id
                ? $firstReservationSlot->reservationType->color
                : "transparent");
        $reservation->sets = in_array('sets', $withoutFields, true)
            ? []
            : Set::reduce($firstReservationSlot->sets);

        $reservation->final_price = $this->price + $this->sets()->sum('reservation_slot_set.price');
        $reservation->club_reservation = in_array('club_reservation', $withoutFields, true)
            ? []
            : $firstReservationSlot?->club_reservation;
        $reservation->slots_count = $this->reservation_slots_count ?? $this->reservationSlots()->count();
        $reservation->start_datetime = Timezone::convertToLocal($firstReservationSlot->start_at)->format(
            'Y-m-d H:i:s'
        );
        $reservation->end_datetime = Timezone::convertToLocal($firstReservationSlot->end_at)->format(
            'Y-m-d H:i:s'
        );
        $reservation->end_datetime_raw = Timezone::convertToLocal(
            now()
                ->parse($firstReservationSlot->getRawOriginal('end_at'), 'UTC')
                ->seconds(0)
        )->format('Y-m-d H:i:s');
        $reservation->status_color = in_array('status_color', $withoutFields, true)
            ? []
            : $firstReservationSlot?->status_color;
        $reservation->status = $firstReservationSlot->status;
        $reservation->status_locale = in_array('status_locale', $withoutFields, true)
            ? []
            : $firstReservationSlot?->statusLocale($this->payment_method_id);
        $reservation->source = $this->source;
        $reservation->occupied_status = $firstReservationSlot->occupied_status;
        $reservation->created_datetime = $this->created_at->format('Y-m-d H:i:s');
        $reservation->payment_method_id = $this->payment_method_id;
        $reservation->payment_method_online = $paymentMethod->online;
        $reservation->reservation_slot_id = null;

        $reservation->cancelation_status = (bool)$firstReservationSlot->cancelation_type;
        if ($extendedData === true) {
            $pricelist = Pricelist::getPricelist($firstReservationSlot->slot->pricelist_id);
            $clubTimerStatusSetting = Setting::getClubGameSetting($pricelist->club_id ?? 1, 'timers_status');

            $discountCode = null;
            if ($firstReservationSlot->discount_code_id) {
                $discountCode =
                    isset($preloadedCollections['discountCodes']) &&
                    !empty(
                    ($discountCodeTmp = $preloadedCollections['discountCodes']
                        ->where('id', $firstReservationSlot->discount_code_id)
                        ->first())
                    )
                        ? $discountCodeTmp
                        : $firstReservationSlot->discountCode;
            }
            $specialOffer = null;
            if ($firstReservationSlot->special_offer_id) {
                $specialOffer =
                    isset($preloadedCollections['specialOffers']) &&
                    !empty(
                    ($specialOfferTmp = $preloadedCollections['specialOffers']
                        ->where('id', $firstReservationSlot->special_offer_id)
                        ->first())
                    )
                        ? $specialOfferTmp
                        : $firstReservationSlot->specialOffer;
            }

            $reservationSlotPriceSum = $this->reservationSlots()->sum('price');
            $game = Game::getCached()->find($this->game_id);

            $totalCustomerReservationDurationInMinutes = $customer ? (DB::table('reservation_slots')
                    ->join('reservations', 'reservation_slots.reservation_id', '=', 'reservations.id')
                    ->where('reservations.customer_id', $customer->id)
                    ->select(DB::raw('SUM(TIMESTAMPDIFF(MINUTE, start_at, end_at)) as total_duration'))
                    ->first()
                    ->total_duration / 60) : 0;
            unset($customer->reservationSlots);
            unset($game->icon);

            $customerReservationSlotsSums = $customer?->reservationSlots()
                ->whereIn('status', [ReservationSlotStatus::Confirmed, ReservationSlotStatus::Pending])
                ->whereNull('canceled_at')
                ->selectRaw('sum(final_price) as sum_price, sum(club_commission_partial) as sum_commission')
                ->first() ?? (object)[
                'sum_price' => 0,
                'sum_commission' => 0
            ];
            $customerSetsPriceSum = $customer?->boughtSets()->sum('reservation_slot_set.price');

            $reservation->extended = [
                'calendar_name' => $firstReservationSlot?->calendar_name,
                'cancelation_type' => $firstReservationSlot?->cancelation_type,
                'customer_presence' => Cache::remember(
                    'customer:' . $customer?->id . ':presence',
                    60,
                    function () use ($customer) {
                        return $customer?->presence ?? 0;
                    }
                ),
                'customer_reservations_count' =>
                    $customer?->reservations_count ?? $customer?->reservations()->count(),
                'customer_reservations_hours' => $totalCustomerReservationDurationInMinutes,
                'customer_reservations_turnover' =>
                    $customerReservationSlotsSums->sum_price + $customerReservationSlotsSums->sum_commission + $customerSetsPriceSum,
                'creator_email' => $firstReservationSlot->creator->email ?? '',
                'discountCode' => $discountCode ? new DiscountCodeResource($discountCode) : null,
                'duration' => now()
                    ->parse(
                        $this->reservation_slots_max_end_at ??
                        $this->reservationSlots()->max('end_at')
                    )
                    ->diffInMinutes(
                        now()->parse(
                            $this->reservation_slots_min_start_at ??
                            $this->reservationSlots()->min('start_at')
                        )
                    ),
                'game' => $this->game_id ? new GameResource(Game::getCached()->find($this->game_id)) : null,
                'features' => FeatureResource::collection($this->features, ['translations']),
                'final_price' => $firstReservationSlot->club_reservation
                    ? 0
                    : $this->price + $this->sets()->sum('reservation_slot_set.price'),
                'online_status' => $firstReservationSlot->club_reservation ? false : $paymentMethod->online,
                'presence' => $firstReservationSlot->club_reservation
                    ? true
                    : $firstReservationSlot?->presence,
                'price' => $this->price,
                'relatedReservations' => [],
                'reservation_slots_count' => $this
                    ->reservationSlots()
                    ->active()
                    ->count(),
                'reservation_price' => $reservationSlotPriceSum,
                'reservation_final_price' => $this->final_price,
                'slot' => new SlotResource($firstReservationSlot->slot),
                'occupied_status' => $firstReservationSlot->occupied_status,
                'specialOffer' => $specialOffer ? new SpecialOfferResource($specialOffer) : null,
                'status' => $firstReservationSlot?->status,
                'timer_enabled' =>
                    $game->hasFeature('has_timers') && $clubTimerStatusSetting['value'] === true
                        ? false
                        : null,
                'reservable_type' => $this->reservable_type,
            ];
        }

        return $reservation;
    }

    private static function prepareSlotVariables($data)
    {
        if (count($data['slot_ids'] ?? []) > (int)$data['slots_count']) {
            $data['slots_count'] = count($data['slot_ids']);
        }
        if (count($data['slot_ids'] ?? []) === 1) {
            $data['slot_id'] = $data['slot_ids'][0];
        }
        return $data;
    }

    protected function locale(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->customer?->locale ??
                ($this->unregistered_customer_data['locale'] ??
                    ($this->firstReservationSlot->slot->pricelist->club->widget_default_locale ?? 'en'))
        );
    }

    public function getNumberWithPreloadedReservation($firstReservationSlotPreloaded): string
    {
        $firstReservationSlot = $firstReservationSlotPreloaded ?? $this->firstReservationSlot;

        return str_pad($firstReservationSlot->numberModel()->id, 5, '0', STR_PAD_LEFT);
    }

    public function getNumber(ReservationSlot|null $firstReservationSlotPreloaded = null)
    {
        $firstReservationSlot = $firstReservationSlotPreloaded ?? $this->firstReservationSlot;
        return str_pad(
            $this->reservationNumber->id ?? $firstReservationSlot->numberModel()->id,
            5,
            '0',
            STR_PAD_LEFT
        );
    }

    protected function number(): Attribute
    {

        return Attribute::make(
            get: fn() => $this->getNumber()
        );
    }

    public function reservationTimeRange($startAt = null, $endAt = null): Attribute
    {
        $reservationMinStartAt =
            $this->reservation_slots_min_start_at ?? ($startAt ?? $this->reservationSlots->min('start_at'));
        $reservationMaxEndAt =
            $this->reservation_slots_max_end_at ?? ($endAt ?? $this->reservationSlots->max('end_at'));

        return Attribute::make(
            get: fn() => Carbon::parse($reservationMinStartAt)->format('H:i') .
                ' - ' .
                Carbon::parse($reservationMaxEndAt)->format('H:i')
        );
    }

    public function reservationSlots(): HasMany
    {
        return $this->hasMany(ReservationSlot::class);
    }

    public function firstReservationSlot(): HasOne
    {
        return $this->hasOne(ReservationSlot::class)->orderBy('start_at');
    }

    public static function prepareOutputForExport(mixed $reservation)
    {
        $gameId = $reservation['game_id'] ?? Slot::find($reservation['slot_id'])->pricelist->game_id;
        $game = Game::getCached()->find($gameId);
        if (isset($reservation['sets'])) {
            $reservation['sets'] = implode(
                ',',
                array_map(static function ($item) {
                    return $item['count'] . 'x ' . $item['name'];
                }, $reservation['sets'])
            );
        }

        if (isset($reservation['status'])) {
            $reservation['status'] = $reservation['status']->value;
        }

        if (isset($reservation['club_reservation'])) {
            $reservation['club_reservation'] = boolToLocale($reservation['club_reservation']);
        }

        if (isset($reservation['source'])) {
            $reservation['source'] = ucfirst(
                __('reservation.status.' . (!$reservation['source'] ? 'online' : 'offline'))
            );
        }

        if (isset($reservation['start_datetime'])) {
            $reservation['reservation_time_range'] = Carbon::parse($reservation['start_datetime'])->format('H:i');
            if (!$game->hasFeature('fixed_reservation_duration') && Setting::getClubGameSetting(club()->id,
                    'fixed_reservation_duration_value', $gameId)?->value === null) {
                $reservation['reservation_time_range'] .= " - " . Carbon::parse($reservation['end_datetime'])->format('H:i');
            }
        }

        if (isset($reservation['start_datetime'])) {
            $reservation['start_date'] = $reservation['start_datetime'] = Carbon::parse($reservation['start_datetime'])->format('d.m.Y');
        }

        if (isset($reservation['end_datetime'])) {
            $reservation['end_time'] = Carbon::parse($reservation['end_datetime'])->format('H:i');
        }

        if (isset($reservation['final_price'])) {
            $reservation['final_price'] =
                number_format($reservation['final_price'] / 100, 2, ',', ' ') .
                ' ' .
                club()?->country->currency ??
                auth()->user()->country->currency;
        }

        return $reservation;
    }

    public static function tableData(string $tableName = null, int $gameId = null, mixed $user = null): array
    {
        $result['name'] = $tableName ?? 'reservations';

        $user = is_int($user) ? User::find($user) : $user;
        $user = $user ?? (auth()->user() ?? User::getUser(1));

        $result['preference'] = $user?->getTablePreferences()[$tableName ?? trim('reservations_' . ((string)($gameId ?? 1)),
            '_')];
        $result['headings'] = self::tableHeadingTranslations(
            Game::getCached()
                ->where('id', $gameId)
                ->first()
        );

        return $result;
    }

    public static function tableHeadingTranslations(Game $game = null): array
    {
        $gameTranslations = Translation::retrieve(club()->country_id ?? 170, $game?->id) ?? [];
        $featureTranslations = [
            'slot_has_parent' => Translation::retrieve(
                countryId: club()->country_id ?? 170,
                featureId: $game?->getFeaturesByType('slot_has_parent')->first()?->id
            ),
            'price_per_person' => Translation::retrieve(
                countryId: club()->country_id ?? 170,
                featureId: $game?->getFeaturesByType('price_per_person')->first()?->id ??
                Feature::getCached()
                    ->where('type', 'price_per_person')
                    ->first()->id
            ),
            'book_singular_slot_by_capacity' => Translation::retrieve(
                countryId: club()->country_id ?? 170,
                featureId: $game?->getFeaturesByType('book_singular_slot_by_capacity')->first()?->id ??
                Feature::getCached()
                    ->where('type', 'book_singular_slot_by_capacity')
                    ->first()->id
            ),
        ];

        return [
            'id' => ucfirst(__('main.id')),
            'game_name' => ucfirst(__('main.kind')),
            'game_id' => ucfirst(__('main.type')),
            'parent_slot_name' => $featureTranslations['slot_has_parent']['parent-slot'] ?? '',
            'slot_name' => $gameTranslations['slot-singular-short'] ?? '',
            'slot_capacity' => $featureTranslations['book_singular_slot_by_capacity']['capacity-short'],
            'number' => ucfirst(__('main.number')),
            'created_at',
            'created_datetime' => ucfirst(__('main.created_at')),
            'start_datetime' => ucfirst(__('main.date')),
            'end_datetime' => ucfirst(__('main.end-date')),
            'start_date' => ucfirst(__('main.date')),
            'start_time' => ucfirst(__('main.from')),
            'end_time' => ucfirst(__('main.to')),
            'club_reservation' => ucfirst(__('reservation.club-reservation')),
            'reservation_time_range' => ucfirst(__('main.hour')),
            'payment_method_online',
            'source' => ucfirst(__('main.type')),
            'slots_count' => $gameTranslations['slots-quantity'] ?? '',
            'duration' => ucfirst(__('main.time')),
            'price',
            'final_price' => ucfirst(__('main.price')),
            'person_count' => $featureTranslations['price_per_person']['person-count'],
            'customer_email' => ucfirst(__('main.email')),
            'customer_name' => ucfirst(__('customer.singular')),
            'customer_phone' => ucfirst(__('main.phone')),
            'sets' => __('set.plural'),
            'rate_atmosphere' => __('rate.atmosphere'),
            'rate_service' => __('rate.service'),
            'rate_final' => __('rate.final'),
            'rate_staff' => __('rate.staff'),
            'rate_content' => ucfirst(__('rate.content')),
            'club_note' => __('reservation.club-note'),
            'customer_note' => __('reservation.customer-note'),
            'status_locale' => ucfirst(__('main.status')),
            'occupied_status' => ucfirst(__('main.action.singular')),
            'app_commission' => ucfirst(__('reservation.app_commission')),
            'provider_commission' => ucfirst(__('reservation.provider_commission')),
        ];
    }

    public function recalculateReservationSlotPrices(): void
    {
        $firstIteration = true;
        $amountsToDiscount = [];
        $amountToDiscount = 0;

        $standardReservationSlotPrices = null;
        $reservationSlotIdsWithStandardPrice = [];
        $game = Game::find($this->game_id);
        $gameHasFeatureArray = [
            'person_as_slot' => $game->hasFeature('person_as_slot'),
            'price_per_person' => $game->hasFeature('price_per_person'),
        ];
        unset($game);
        $reservationSlots = $this->reservationSlots()->get();
        $game = Game::find($this->game_id);
        $club = Club::getClub($reservationSlots->first()->slot->pricelist->club_id);
        $reservationOrder = 1;
        foreach ($reservationSlots as $reservationSlot) {
            $discountCode = DiscountCode::find($reservationSlot->discount_code_id);
            if (
                $discountCode &&
                DiscountCodeType::Amount &&
                !isset($amountsToDiscount[$reservationSlot->discount_code_id])
            ) {
                $amountsToDiscount[$reservationSlot->discount_code_id] = $discountCode->value;
            }
            $amountToDiscount = $amountsToDiscount[$reservationSlot->discount_code_id] ?? 0;

            $slot = $reservationSlot->slot;

            $timerEnabled = $reservationSlot->getTimerEnabled([
                'game' => $game,
                'start_at' => $reservationSlot->getOriginal('start_at')
            ]);
            $calculatePriceData = [
                'loadTimers' => $timerEnabled,
                'game' => Game::find($this->game_id),
                'game_id' => $game->id ?? null,
                'slot' => $slot,
                'allFeatures' => Feature::getCached(),
                'club_id' => $club->id,
                'start_at_converted' => $reservationSlot->getOriginal('start_at'),
                'end_at_converted' => $reservationSlot->getOriginal('end_at'),
                'special_offer_id' => $reservationSlot->special_offer_id
            ];

            // if reservation is made in game with person_as_slot feature, each reservation slot has the same price
            // if reservation is made in game with price_per_person feature each slot can have a different number of people assigned to it - price will be different
            // if amount to discount > 0, we have to distribute amount between reservation slots - price will be different

            if (
                !$gameHasFeatureArray['person_as_slot'] ||
                $gameHasFeatureArray['price_per_person'] ||
                $amountToDiscount
            ) {
                $prices = $reservationSlot->calculatePrice(
                    $customer->id ?? null,
                        request()->all()['custom_price'] ?? false
                        ? array_merge(['custom_price' => true, 'price' => $reservationSlot->price], $calculatePriceData)
                        : $calculatePriceData,
                    $firstIteration,
                    $amountToDiscount,
                    $reservationOrder++
                );
                $discountAmountUsed = $amountToDiscount - $prices['remainingAmountToDiscount'];
                $amountsToDiscount[$reservationSlot->discount_code_id] = $prices['remainingAmountToDiscount'];
                ReservationSlot::where('id', $reservationSlot->id)->update([
                    'price' => $prices['basePrice'],
                    'final_price' => $prices['finalPrice'],
                    'discount_code_amount' => $discountAmountUsed,
                    'discount_code_id' => $reservationSlot->discount_code_id,
                    'special_offer_amount' => $prices['special_offer_amount'],
                ]);
            } else {
                //only first reservationSlot has sets and amount discount code attached to id,
                //others have standard price
                if (!$standardReservationSlotPrices) {
                    $standardReservationSlotPrices = $reservationSlot->calculatePrice(
                        $customer->id ?? null,
                            request()->all()['custom_price'] ?? false ? array_merge($calculatePriceData,
                            ['price' => $reservationSlot->price]) : $calculatePriceData,
                        $firstIteration
                    );
                }
                $reservationSlotIdsWithStandardPrice[] = $reservationSlot->id;
            }
            $firstIteration = false;
        }
        if ($reservationSlotIdsWithStandardPrice) {
            ReservationSlot::whereIn('id', $reservationSlotIdsWithStandardPrice)->update([
                'price' => $standardReservationSlotPrices['basePrice'],
                'final_price' => $standardReservationSlotPrices['finalPrice'],
            ]);
        }
    }

    public static function store(
        array $data,
        bool $includeCommission = false,
        string $returnVariable = 'slotsData',
        int $reservationSource = 1,
        bool $withValidation = true,
        bool $widget = false
    ): JsonResponse|Reservation {

        $club = request()->route('club') ?? club();
        $game = Game::getCached()->find($data['game_id']);
        $club = Club::getClub($data['club_id']);
        $data['duration'] = $data['duration'] ?? 60;

        //Repeating data
        $personAsSlot = $game->hasFeature('person_as_slot');

        $gameFeatures = $game->features;
        foreach ($gameFeatures as $feature) {
            if (!$feature->executablePublicly) {
                continue;
            }
            $data = $feature?->prepareDataForAction($data) ?? $data;
        }
        $now = now();
        $startAt = $now->parse($data['start_at']);
        $endAt = $startAt
            ->clone()
            ->addMinutes($data['duration']);
        $data['end_at'] = $endAt->format('Y-m-d H:i:s');
        $data['creation'] = true;

        //we should clear null values from data array because its laters used in foreach so we dont want to have null values
        $filteredDataFeatures = array_filter($data['features']);


        //Old code
        //$data = BookSingularSlotByCapacity::prepareSlotVariables($data);


        // New code:
        // We use personAsSlot variable to determine if we need to prepare slot variables as it was in previosu function,
        // but now we have information if this game has person_as_slot feature or not and we can use it to determine if we need to prepare slot variables
        if (!$personAsSlot) {
            $data = self::prepareSlotVariables($data);
        }

        // Old code had no features parameter in this function so i added it beacuse we already have it in this function
        // no point of getting it again spamming cache retrieve:
        if ($withValidation) {
            self::processReservationInsertDataValidation($data, null, $gameFeatures);
        }


        //Getting customers - if customer is in data and his id is present no need to get him from data below
        if(isset($data['customer']['id']) && $data['customer']['id'] !== null) {
            $customer = Customer::find($data['customer']['id']);
        } else {
            $customer = self::getCustomerFromData($data, $club);
            if ($customer === null && request()->routeIs('widget.*')) {
                $customer = Customer::find(session()->get('customer_id', 0));
            }
        }

        if ($reservationSource === 1) {
            $customer?->syncTags($data['customer']['tags'] ?? []);
        }

        //In administrator panel we can choose payment method, in widget we cant,
        //so if we already have payment_method_id in request we shoudlnt use this block below because its waste of time
        if($data['status'] !== null && $data['status'] !== 0) {
            if(isset(json_decode($data['status'],true)['payment_method_id'])){
                $paymentMethodId = json_decode($data['status'],true)['payment_method_id'];
                if($paymentMethodId !== null) {
                    $data['payment_method_id'] = $paymentMethodId;
                }
            } else {
                $data['payment_method_id'] = PaymentMethod::where('online', false)->first()->id;
            }
        } else {
            if ($data['payment_type'] === 'offline') {
                $result = json_decode($data['status'], true, 512, JSON_THROW_ON_ERROR);
                $paymentMethodId = null;
                if (json_last_error() === JSON_ERROR_NONE && !request()->routeIs('widget.*')) {
                    $data['status'] = $result['status'];
                    $paymentMethod = $result['payment_method_id']
                        ? PaymentMethod::getPaymentMethod($result['payment_method_id'])
                        : null;
                    if ($paymentMethod && $paymentMethod->online === false) {
                        $data['payment_method_id'] = $paymentMethod?->id;
                    }
                }
                if (empty($paymentMethodId)) {
                    $data['payment_method_id'] = Cache::rememberForever(
                        'first_offline_payment_method',
                        function () {
                            return PaymentMethod::where('online', false)->first();
                        }
                    )->id;
                }
            } else {
                $data['payment_method_id'] = $club->getOnlinePaymentMethod()->id;
            }
        }

        $reservationCreateArray = [
            'source' => $reservationSource,
            'unregistered_customer_data' => $customer === null ? $data['customer'] : null,
            'game_id' => $game->id,
            'club_note' => $data['club_note'],
            'customer_note' => $data['customer_note'],
            'show_customer_note_on_calendar' =>
                $data['show_customer_note_on_calendar'] ??
                ($data['customer_note'] ?? false) && $data['customer_note'] !== '',
            'show_club_note_on_calendar' => $data['show_club_note_on_calendar'] ?? false,
            'customer_id' => $customer?->id ?? null,
            'payment_method_id' => $data['payment_method_id'],
            'currency' => $club->country->currency,
            'customer_ip' => $data['customer_ip'] ?? null,
        ];

        // Disable reservation event dispatcher because there are no ReservationSlots assigned to it
        $reservation = self::withoutEvents(function () use ($reservationCreateArray) {
            return Reservation::create($reservationCreateArray);
        });

        if ($personAsSlot) {
            // in this case we have to insert only one reservation slot. UpdateReservationData method in PersonAsSlot class will do its job
            $slotsIds = match (true) {
                !$data['occupied_status'] => [
                    Slot::whereHas('pricelist', function ($query) use ($data) {
                        $query->where('club_id', $data['club_id']);
                        $query->where('game_id', $data['game_id']);
                    })
                        ->where('slot_id', $data['parent_slot_id'])
                        ->first()->id,
                ],
                default => self::optimizedGetVacantSlotsIds(
                    $personAsSlot,
                    $gameFeatures,
                    $club,
                    $game,
                    now()->parse($data['start_at']),
                    $data['duration'],
                    null,
                    null,
                    (int)$data['slots_count'],
                    $filteredDataFeatures
                ),
            };
        } else {
            $slotsIds = match (true) {
                count(array_filter($data['slot_ids'])) => $data['slot_ids'],
                default => self::optimizedGetVacantSlotsIds(
                    $personAsSlot,
                    $gameFeatures,
                    $club,
                    $game,
                    now()->parse($data['start_at']),
                    $data['duration'],
                    $data['slot_ids'] ?? [0 => null],
                    $data['slot_id'] ?? null,
                    (int)($data['slots_count'] ?? count($data['slot_ids'])),
                    $filteredDataFeatures,
                    true
                ),
            };
        }

        $firstIteration = true;

        // database performance optimization in reservation with one slot
        $slots =
            count($slotsIds) === 1
                ? collect([Slot::getSlot($slotsIds[0])])
                : Slot::whereIn('id', $slotsIds)->get();


        $reservationSlotsInsertData = [];
        $utcStartAt = Timezone::convertFromLocal($data['start_at']);
        $utcEndAt = Timezone::convertFromLocal($data['end_at']);

        foreach ($slotsIds as $slotId) {
            $reservationSlotsInsertData[$slotId] = array_merge(
                [
                    'reservation_id' => $reservation->id,
                    'slot_id' => $slotId,
                    'start_at' => $utcStartAt,
                    'end_at' => $utcEndAt,
                    'presence' => true,
                    'creator_id' => $reservationSource === 1 ? auth()->user()?->id : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                ReservationSlot::fillWithData($data)
            );
        }
        ReservationSlot::insert($reservationSlotsInsertData);
        $reservationSlots = $reservation
            ->reservationSlots()
            ->with('sets')
            ->get();

        $reservationNumbersToInsert = [];
        $reservationSlotIdsWithStandardPrice = [];
        $standardReservationSlotPrices = null;
        $additionalReservationSlots = [];

        $discountCode = null;
        // request from widget has discount code in text format
        if ($data['discount_code'] ?? 0) {
            $discountCode = DiscountCode::where('code', $data['discount_code'])->first();
        }
        if ($data['discount_code_id'] ?? 0) {
            $discountCode = DiscountCode::find($data['discount_code_id']);
        }

        $amountToDiscount =
            $discountCode && $discountCode->type === DiscountCodeType::Amount ? $discountCode->value : 0;

        $reservationOrder = 1;
        foreach ($slotsIds as $slotId) {
            $reservationSlot = $reservationSlots->where('slot_id', $slotId)->first();

            // we always attach all sets to the first reservation
            if ($firstIteration === true) {
                $reservationSlot->attachSets($data['sets'] ?? [], $club);
            }
            $calculatePriceData = array_merge($data, [
                'game' => $game,
                'game_id' => $game->id,
                'slot' => $slots->where('id', $slotId)->first(),
                'slot_id' => $slotId,
                'discount_code_id' => $discountCode->id ?? null,
                'allFeatures' => Feature::all(),
                'club_id' => $club->id,
                'start_at_converted' => $startAt,
                'end_at_converted' => $endAt,
            ]);

            if (
                $firstIteration ||
                $game->hasFeature('price_per_person') ||
                $amountToDiscount ||
                $data['custom_price']
            ) {
                $prices = $reservationSlot->calculatePrice(
                    $customer->id ?? null,
                    $calculatePriceData,
                    $firstIteration,
                    $amountToDiscount,
                    $reservationOrder++
                );
                $discountAmountUsed = $amountToDiscount - $prices['remainingAmountToDiscount'];
                $amountToDiscount = $prices['remainingAmountToDiscount'];
                ReservationSlot::withoutEvents(function () use (
                    $reservationSlot,
                    $prices,
                    $discountAmountUsed,
                    $discountCode
                ) {
                    $reservationSlot->update([
                        'price' => $prices['basePrice'],
                        'final_price' => $prices['finalPrice'],
                        'discount_code_amount' => $discountAmountUsed,
                        'special_offer_amount' => $prices['special_offer_amount'],
                        'discount_code_id' => $discountCode->id ?? null,
                    ]);
                });
            } else {
                //only first reservationSlot has sets and amount discount code attached to id,
                //others have standard price
                if (!$standardReservationSlotPrices) {
                    $standardReservationSlotPrices = $reservationSlot->calculatePrice(
                        $customer->id ?? null,
                        $calculatePriceData,
                        $firstIteration
                    );
                }
                $reservationSlotIdsWithStandardPrice[] = $reservationSlot->id;
            }


            //@TODO - think about this
            // Make additional reservation if enabled.
            // An additional reservation is a technical entity that allows you to book
            // time to prepare a slot for the next client
            $additionalClubReservationStatus = Setting::getClubGameSetting(
                $club->id,
                'additional_club_reservation_status'
            );

            if ($additionalClubReservationStatus['value'] === true) {
                $additionalReservationSlots[] = $reservationSlot->makeAdditionalReservation();
            }

            if (!$personAsSlot) {
                $reservationNumbersToInsert[] = [
                    'numerable_type' => ReservationSlot::class,
                    'numerable_id' => $reservationSlot->id,
                ];
            }
            $firstIteration = false;
        }
        if ($reservationSlotIdsWithStandardPrice) {
            ReservationSlot::whereIn('id', $reservationSlotIdsWithStandardPrice)->update([
                'price' => $standardReservationSlotPrices['basePrice'],
                'final_price' => $standardReservationSlotPrices['finalPrice'],
            ]);
        }

        // ReservationNumber is assigned to Reservation only if its game has person_as_slot feature
        if (
            count($reservationNumbersToInsert) === 0 &&
            $personAsSlot
        ) {
            $reservation->reservationNumber()->create();
        } else {
            ReservationNumber::insert($reservationNumbersToInsert);
        }

        $canProcessFeatures = !(new Feature())->preventReservationProcessing;
        foreach ($gameFeatures as $feature) {
            if ($feature->preventReservationProcessing) {
                $canProcessFeatures = false;
            }
        }

        if ($canProcessFeatures) {
            foreach ($gameFeatures as $feature) {
                if (!$feature->executablePublicly) {
                    continue;
                }
                $reservationOrder = 1;
                foreach ($reservationSlots as $reservationSlot) {
                    $feature->updateReservationData(
                        $reservationSlot->numberModel(),
                        array_merge($data ?? ['slots_count' => $data['slots_count']], [
                            'reservation' => $reservation,
                            'reservationSlots' => $reservationSlots,
                            'reservationSlot' => $reservationSlot,
                        ]),
                        true,
                        $reservationOrder++
                    );
                }
            }
        }

        $reservationSlotEntities = [];

        if (!$personAsSlot) {
            $reservationNumberToNotify = $reservationSlots->first()->reservationNumber;
            $reservationSlots = $reservation->reservationSlots()->with('slot')->get();
            foreach ($reservationSlots as $reservationSlot) {
                $reservationSlotEntities[] = $reservationSlot->prepareForOutput(
                    preloadedCollections: [
                        'reservation' => $reservation,
                        'game' => $game,
                        'slots' => collect([$reservationSlot->slot]),
                        'firstReservationSlot' => $reservationSlot,
                        'club' => $club
                    ]
                );
            }
            foreach ($additionalReservationSlots as $additionalReservationSlot) {
                $reservationSlotEntities[] = (array)$additionalReservationSlot->prepareForOutput(
                    preloadedCollections: [
                        'slots' => $slots,
                    ]
                );
            }
        } else {
            $game
                ->features()
                ->where('type', 'person_as_slot')
                ->first()
                ->updateReservationData($reservation->reservationNumber, $data, true);
        }

        $managerEmails = $club
            ->getManagerEmails()
            ->where('game_id', $game->id)
            ->pluck('email')
            ->toArray();
        foreach ($managerEmails as $email) {
            $notifiable = (new AnonymousNotifiable())->route('mail', $email);
            $notifiable->notify(new ReservationStoredNotification($reservationNumberToNotify));
        }

        if ($widget === false) {
            $reservation->setCustomerNotifications($data['notification']);
        }

        $reservationUpdateData = [];

        $reservationUpdateData['price'] = $reservation->getPrice(ReservationSlot::where('reservation_id',
            $reservation->id)->get());

        $reservationUpdateData['app_commission'] =
            $data['payment_type'] === 'online'
                ? $reservation->getPaymentCommission(
                $reservationUpdateData['price'],
                $reservationSlots->first()
            )
                : 0;
        $reservationUpdateData['club_commission'] =
            $data['payment_type'] === 'offline'
                ? 0
                : $club->getCommission($reservationUpdateData['price']);
        $reservationUpdateData['price'] += $reservationUpdateData['club_commission'];

        self::withoutEvents(function () use ($reservation, $reservationUpdateData) {
            $reservation->update($reservationUpdateData);
        });

        (new ReservationObserver())->created($reservation, $reservationSlots);
        (new ReservationObserver())->updated($reservation, $reservationSlots);

        if (isset($data['timer_init']) && $data['timer_init']) {
            $reservation->reservationSlots->each(function ($reservationSlot) {
                $reservationSlot->startTimer();
            });
        }



        return match (true) {
            $returnVariable === 'reservation' => $reservation->load('reservationSlots'),
            $personAsSlot => response()->json(
                []
            ), // if so, reservation entities data are unneccessary
            default => response()->json(['reservationSlotEntities' => $reservationSlotEntities]),
        };

    }

    public function setCustomerNotifications(array $data): void
    {
        $baseNotificationStatusesArray = [
            'sms' => false,
            'mail' => true,
        ]; // default
        foreach ($baseNotificationStatusesArray as $method => $value) {
            if (!isset($data[$method])) {
                $data[$method] = $value;
            }
        }
        $insertArray = [];
        foreach ($data as $method => $value) {
            if ($value === false) {
                $insertArray[] = [
                    'remindable_type' => Reservation::class,
                    'remindable_id' => $this->id,
                    'method' => $method,
                    'type' => ReminderType::NewReservation,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('reminders')->insert($insertArray);
    }

    public static function getCustomerFromData(array $data, $club = null): Customer|null
    {

        // If club is not passed as a parameter, we will try to get it from the request
        if($club == null){
            $club = request()->route('club') ?? club();
        }

        $customerData = $data['customer'];
        if (request()->routeIs('widget.*')) {
            $customer = Customer::where('club_id', $club->id);
            if ($customerData['email'] && strlen($customerData['email'])) {
                $customer->where('email', $customerData['email']);
            } else {
                $customer->where('phone', $customerData['phone']);
            }
            return $customer->first();
        }
        if (!$data['anonymous_reservation']) {
            $locale = request()->routeIs('widget.*') ? session()->get('customLocale',
                $club->widget_default_locale) : $club->country->locale;

            if ($customerData['phone'] && strlen($customerData['phone'])) {
                $customer = Customer::where('club_id', $club->id)->where('phone', $customerData['phone'])->first();
            } else {
                $customer = Customer::where('club_id', $club->id)->where('email', $customerData['email'])->first();
            }

            if (empty($customer)) {
                $customer = $club->customers()->create([
                    'phone' => $customerData['phone'],
                    'first_name' => $customerData['first_name'],
                    'last_name' => $customerData['last_name'],
                    'locale' => $locale ?? 'en',
                    'email' => $customerData['email'],
                ]);
            } else {
                $customer->update([
                    'phone' => $customerData['phone'],
                    'first_name' => $customerData['first_name'],
                    'last_name' => $customerData['last_name'],
                    'email' => $customerData['email'],
                ]);
            }
            return $customer;
        }
        return null;
    }

    public static function processReservationInsertDataValidation(
        Request|array $data,
        ReservationNumber $reservationNumber = null,
        $features = null
    ): void {
        $data = is_array($data) ? $data : $data->all();
        $data['price'] = isset($data['price']) ? $data['price'] : 0;
        $data['price'] =
            is_int((int)$data['price']) && (string)(int)$data['price'] === $data['price']
                ? (int)$data['price'] * 100
                : round((float)str_replace(',', '.', $data['price']) * 100);

        request()->merge($data);
        if ($reservationNumber) {
            $validationArray = (new UpdateReservationRequest())->rules($reservationNumber);
            $game = $reservationNumber->numerable->game;
        } else {
            $validationArray = (new StoreReservationRequest())->rules();
            $game = request()->route('game') ?? Game::getCached()->first();
        }

        $gameTranslations = Translation::gamesTranslationsArray()[$game->id];
        $validationNiceNames = [
            'slot_ids.*' => strtolower($gameTranslations['slot-singular-short']),
        ];

        if ($features == null){
            $features = Game::getCached()
                ->where('id', $game->id)
                ->first()->features;
        }

        // Is it doable to move this get cached games query and get features taken before here?
        foreach (
            $features
            as $feature
        ) {
            $validationArray += $feature->getReservationDataValidationRules($data);
            $data['features'][$feature->id] = $feature->prepareReservationDataForValidation(
                $data['features'][$feature->id] ?? []
            );
            $validationNiceNames += $feature->getReservationDataValidationNiceNames();
        }

        Validator::make($data, $validationArray, [], $validationNiceNames)->validate();
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function features(): MorphToMany
    {
        return $this->morphToMany(Feature::class, 'describable', 'feature_payload')->withPivot('data');
    }

    public static function getVacantSlotsIds(
        $game = null,
        $startAt = null,
        $duration = null,
        $slotIds = null,
        $slotId = null,
        $slotsCount = 1,
        $features = [],
        $preserveOrder = false,
        $excludeReservationSlotIds = [],
        $_club = null
    ): array {
        $gameFeatures = Game::getCached()->find($game)->features;
        $club =
            Club::getClub(request()->all()['club_id'] ?? 0) ??
            (request()->route('club') ?? (club() ?? $_club));
        $startAt = is_string($startAt) ? now()->parse($startAt) : $startAt;
        $endAt = $startAt->clone()->addMinutes($duration);

        if ($slotId) {
            $slot = Slot::getSlot($slotId);
            $pricelists = [Pricelist::getPricelist($slot->pricelist_id)->id];
        } else {
            $pricelists = $club
                ->pricelists()
                ->with('pricelistItems', 'pricelistExceptions')
                ->where('game_id', $game->id)
                ->get();
            $pricelistsPrices = $pricelists
                ->mapWithKeys(
                    fn(Pricelist $pricelist) => [
                        $pricelist->id => $pricelist->calculatePrice($startAt->clone(), $endAt->clone()),
                    ]
                )
                ->toArray();
            asort($pricelistsPrices);
            $pricelists = array_keys($pricelistsPrices);
        }

        // Get slotIds to fill. If there was a many slot ids field pass it to $slotIds variable
        $slotIds = array_filter($slotIds ?? []);
        $slotIds = empty($slotIds) ? null : $slotIds;

        // if not pass single slot id field value if present
        $slotIds = $slotIds ?? ($slotId ? [$slotId] : []);

        $slotsCount = is_string($slotsCount) ? (int)$slotsCount : $slotsCount;
        $pricelistIds = Pricelist::where('club_id', $club->id)->where('game_id', $game->id)->pluck('id')->toArray();
        $allSlotsIds = Slot::where('active', true)
            ->whereIn('pricelist_id', $pricelists)
            ->vacant($club->id, $game->id, $startAt->clone(), $endAt->clone(), $excludeReservationSlotIds);
        foreach ($features ?? [] as $featureId => $value) {
            $gameFeature = $gameFeatures->find($featureId);
            if (
                !empty($gameFeature) > 0 &&
                isset(request()->all()['features'][$featureId])
            ) {
                $allSlotsIds =
                    $gameFeature?->slotQueryScope($allSlotsIds, request()->all()) ??
                    $allSlotsIds;
            }
        }
        $allSlotsIds = $allSlotsIds->select('pricelist_id', 'id')->get();

        $gameHasFeatures = [
            'person_as_slot' => $game->hasFeature('person_as_slot'),
            'book_singular_slot_by_capacity' => $game->hasFeature('book_singular_slot_by_capacity'),
        ];

        while ($slotsCount > 0 && $slotsCount > count($slotIds ?? [])) {
            if (count($pricelists) === 0) {
                return $slotIds;
            }
            $newSlotIds = $allSlotsIds
                ->where('pricelist_id', array_shift($pricelists))
                ->whereNotIn('id', $slotIds ?? []);

            if ($gameHasFeatures['person_as_slot'] || $gameHasFeatures['book_singular_slot_by_capacity']) {
                $newSlotIds = $newSlotIds->take($slotsCount - count($slotIds ?? []));
            }

            $newSlotIds = collect($newSlotIds)
                ->pluck('id')
                ->toArray();

            foreach ($newSlotIds as $newSlotId) {
                $slotIds[] = $newSlotId;
            }
        }

        // If $preserveOrder = true then if the user selects the original slot 3 and two slots to be reserved, then return slots 3 and 4, not 3 and 1
        if (
            $preserveOrder &&
            !$gameHasFeatures['person_as_slot'] &&
            !$gameHasFeatures['book_singular_slot_by_capacity']
        ) {
            $pivot = $slotIds[0];
            $smaller = [];
            $bigger = [];

            for ($i = 1; $i < count($slotIds); $i++) {
                if ($slotIds[$i] < $pivot) {
                    $smaller[] = $slotIds[$i];
                } else {
                    $bigger[] = $slotIds[$i];
                }
            }

            sort($smaller);
            sort($bigger);
            $smaller = array_reverse($smaller);

            $slotIds = array_merge([$pivot], $bigger, $smaller);
        }

        $slotIds = array_map('intval', $slotIds);
        $slotIds = array_unique($slotIds);
        $result = array_slice($slotIds, 0, $slotsCount ?? 1) ?? [];
        sort($result);
        return $result;
    }


    public static function optimizedGetVacantSlotsIds(
        $hasPersonAsSlotFeature,
        $gameFeatures,
        $club,
        $game = null,
        $startAt = null,
        $duration = null,
        $slotIds = null,
        $slotId = null,
        $slotsCount = 1,
        $features = [],
        $preserveOrder = false,
        $excludeReservationSlotIds = [],
        $_club = null
    ): array {
        $startAt = is_string($startAt) ? now()->parse($startAt) : $startAt;
        $endAt = $startAt->clone()->addMinutes($duration);

        if ($slotId) {
            $slot = Slot::getSlot($slotId);
            $pricelists = [Pricelist::getPricelist($slot->pricelist_id)->id];
        } else {
            $pricelists = $club
                ->pricelists()
                ->with('pricelistItems', 'pricelistExceptions')
                ->where('game_id', $game->id)
                ->get();
            $pricelistsPrices = $pricelists
                ->mapWithKeys(
                    fn(Pricelist $pricelist) => [
                        $pricelist->id => $pricelist->calculatePrice($startAt->clone(), $endAt->clone()),
                    ]
                )
                ->toArray();
            asort($pricelistsPrices);
            $pricelists = array_keys($pricelistsPrices);
        }

        // Get slotIds to fill. If there was a many slot ids field pass it to $slotIds variable
        $slotIds = array_filter($slotIds ?? []);
        $slotIds = empty($slotIds) ? null : $slotIds;

        // if not pass single slot id field value if present
        $slotIds = $slotIds ?? ($slotId ? [$slotId] : []);
        $slotsCount = is_string($slotsCount) ? (int)$slotsCount : $slotsCount;

        //Not used anywhere pointless db call
        //$pricelistIds = Pricelist::where('club_id', $club->id)->where('game_id', $game->id)->pluck('id')->toArray();

        //### I dont understand this block of code for me it doesnt makes sense and doesnt do anything - allSlotsIds has same value before foreach
        //### and after foreach
        $allSlotsIds = Slot::where('active', true)
            ->whereIn('pricelist_id', $pricelists)
            ->vacant($club->id, $game->id, $startAt->clone(), $endAt->clone(), $excludeReservationSlotIds);
        foreach ($features ?? [] as $featureId => $value) {
            $gameFeature = $gameFeatures->find($featureId);
            if (
                !empty($gameFeature) > 0 &&
                isset(request()->all()['features'][$featureId])
            ) {
                $allSlotsIds =
                    $gameFeature?->slotQueryScope($allSlotsIds, request()->all()) ??
                    $allSlotsIds;
            }
        }
        $allSlotsIds = $allSlotsIds->select('pricelist_id', 'id')->get();
        //###


        $gameHasFeatures = [
            'person_as_slot' => $hasPersonAsSlotFeature,
            'book_singular_slot_by_capacity' => $game->hasFeature('book_singular_slot_by_capacity'),
        ];

        while ($slotsCount > 0 && $slotsCount > count($slotIds ?? [])) {
            if (count($pricelists) === 0) {
                return $slotIds;
            }
            $newSlotIds = $allSlotsIds
                ->where('pricelist_id', array_shift($pricelists))
                ->whereNotIn('id', $slotIds ?? []);

            if ($gameHasFeatures['person_as_slot'] || $gameHasFeatures['book_singular_slot_by_capacity']) {
                $newSlotIds = $newSlotIds->take($slotsCount - count($slotIds ?? []));
            }

            $newSlotIds = collect($newSlotIds)
                ->pluck('id')
                ->toArray();

            foreach ($newSlotIds as $newSlotId) {
                $slotIds[] = $newSlotId;
            }
        }

        // If $preserveOrder = true then if the user selects the original slot 3 and two slots to be reserved, then return slots 3 and 4, not 3 and 1
        if (
            $preserveOrder &&
            !$hasPersonAsSlotFeature &&
            !$gameHasFeatures['book_singular_slot_by_capacity']
        ) {
            $pivot = $slotIds[0];
            $smaller = [];
            $bigger = [];

            for ($i = 1; $i < count($slotIds); $i++) {
                if ($slotIds[$i] < $pivot) {
                    $smaller[] = $slotIds[$i];
                } else {
                    $bigger[] = $slotIds[$i];
                }
            }

            sort($smaller);
            sort($bigger);
            $smaller = array_reverse($smaller);

            $slotIds = array_merge([$pivot], $bigger, $smaller);
        }

        $slotIds = array_map('intval', $slotIds);
        $slotIds = array_unique($slotIds);
        $result = array_slice($slotIds, 0, $slotsCount ?? 1) ?? [];
        sort($result);
        return $result;
    }



    public static function calculatePrice(
        Request $request,
        $resultType = 'json',
        $withValidation = true
    ): array|JsonResponse {
        $result = [
            'basePrice' => 0,
            'finalPrice' => 0,
            'setsPrice' => 0,
            'reservationFee' => 0,
            'priceBeforeDiscount' => 0,
        ];
        $data = $request->all();
        $data['start_at'] = now()->parse($data['start_at']);
        if (isset($data['start_at'], $data['duration'])) {
            $data['end_at'] = $data['start_at']->clone()->addMinutes($data['duration']);
        }
        // set status to paid when calculating price - it doesn't matter here
        $data['status'] = 1;

        $club = $request->route('club') ?? club();
        $commisionPercent = $club->getSettings()->where('key', 'additional_commission_percent')->first();
        $commisionFixed = $club->getSettings()->where('key', 'additional_commission_fixed')->first();
        $reservationFeeValue = [];
        if ($commisionPercent !== null && $commisionPercent->value > 0) {
            $reservationFeeValue['commisionPercent'] = $commisionPercent->value / 100;
        }
        if ($commisionFixed !== null && $commisionFixed->value > 0) {
            $reservationFeeValue['commisionFixed'] = $commisionFixed->value;
        }

        $data['discount_code_id'] = $data['discount_code_id'] ?? null;
        if (isset($data['discount_code']) && $data['discount_code'] !== '') {
            $data['discount_code_id'] = DiscountCode::where('code', $data['discount_code'] ?? '')
                ->where('club_id', $club->id)
                ->where(function ($query) {
                    $query->where('game_id', request()->get('game_id'))->orWhereNull('game_id');
                })
                ->first()?->id;
        }
        $data['special_offer_id'] = $data['special_offer_id'] ?? null;

        $customer = null;
        if (session()->get('customer_id')) {
            $customer = Customer::where('id', session()->get('customer_id'))->first();
        }

        $discountCode = $data['discount_code_id'] ? DiscountCode::find($data['discount_code_id']) : null;
        $amountToDiscount = 0;
        $reservationNumber =
            isset($data['reservation_number_id']) && $data['reservation_number_id']
                ? ReservationNumber::find($data['reservation_number_id'])
                : null;
        if (
            !$discountCode ||
            !$discountCode->isAvailable(
                $customer?->id,
                request()->get('start_at'),
                request()->get('start_at')
            )
        ) {
            $data['discount_code_id'] = null;
            $discountCode = null;
        }
        if (
            $reservationNumber &&
            $reservationNumber->numerable_type === ReservationSlot::class &&
            $data['discount_code_id']
        ) {
            if ($discountCode && $discountCode->type === DiscountCodeType::Amount) {
                $amountToDiscount = $discountCode->value;
                $specialOfferAmountsSumsFromPreviousReservationSlots = (int) ReservationSlot::where(
                    'id',
                    '<',
                    $reservationNumber->numerable->id
                )
                    ->where('reservation_id', $reservationNumber->numerable->reservation_id)
                    ->sum('special_offer_amount');
                $pricesSumFromPreviousReservationSlots = (int) ReservationSlot::where(
                    'id',
                    '<',
                    $reservationNumber->numerable->id
                )
                    ->where('reservation_id', $reservationNumber->numerable->reservation_id)
                    ->sum('price');
                $amountToDiscount = max(
                    0,
                    $amountToDiscount -
                    $pricesSumFromPreviousReservationSlots +
                    $specialOfferAmountsSumsFromPreviousReservationSlots
                );
            }
        } elseif (!$reservationNumber) {
            $amountToDiscount =
                $discountCode && $discountCode->type === DiscountCodeType::Amount ? $discountCode->value : 0;
        }

        $data = BookSingularSlotByCapacity::prepareSlotVariables($data);
        $request->merge($data);
        if ($withValidation) {
            self::processReservationInsertDataValidation(
                $request,
                $reservationNumber ??
                ($request->has('reservation_number_id')
                    ? ReservationNumber::find($request->get('reservation_number_id'))
                    : null)
            );
        }
        $data = $request->all();

        $game = Game::getCached()
            ->where('id', $data['game_id'] ?? 1)
            ->first();
        if ($data['custom_price'] && $game->hasFeature('person_as_slot')) {
            return returnResult(
                [
                    'finalPrice' => intval($data['price']),
                    'basePrice' => intval($data['price']),
                    'finalPrice' => intval($data['price']),
                    'reservationFee' => $reservationFeeValue,
                ],
                $resultType
            );
        }

        $slotsInReservation = [];

        if ($reservationNumber) {
            $slotsInReservationPrepare =
                $reservationNumber->numerable_type === Reservation::class
                    ? $reservationNumber->numerable->reservationSlots()->get()
                    : [$reservationNumber->numerable];

            foreach ($slotsInReservationPrepare as $slotInReservationPrepare) {
                $slotsInReservation[$slotInReservationPrepare->slot_id] = $slotInReservationPrepare;
            }
        }

        if ($game->hasFeature('person_as_slot')) {
            $slot = $club
                ->slots()
                ->whereHas('pricelist', function ($query) use ($game, $club) {
                    $query->where('game_id', $game->id);
                    $query->where('club_id', $club->id);
                })
                ->vacant(
                    clubId(),
                    $request->get('game_id'),
                    $data['start_at']->clone(),
                    $data['end_at']->clone()
                )
                ->first();

            if (isset($slotsInReservation[$slot->id])) {
                $reservationSlot = $slotsInReservation[$slot->id];
            } else {
                $reservationSlot = new ReservationSlot();

                $reservationSlot->fillWithRequest($request);
                $reservationSlot->fill([
                    'slot_id' => $slot->id,
                    'start_at' => $data['start_at']->clone(),
                    'end_at' => $data['start_at']->clone()->addHour(),
                ]);
            }

            $prices = $reservationSlot->calculatePrice(
                $customer->id ?? null,
                array_merge($data, ['slot_id' => $slot->id])
            );
            $result['basePrice'] =
                $prices['basePrice'] *
                $request->all()['features'][
                $game
                    ->features()
                    ->where('type', 'person_as_slot')
                    ->first()->id
                ]['persons_count'];
            $result['finalPrice'] =
                $prices['basePrice'] *
                $request->all()['features'][
                $game
                    ->features()
                    ->where('type', 'person_as_slot')
                    ->first()->id
                ]['persons_count'];

            $result['reservationFee'] = $reservationFeeValue;
            $result['priceBeforeDiscount'] = $result['finalPrice'];
            // reduce price by a fixed discount only once
            if (
                $reservationSlot->discountCode &&
                $reservationSlot->discountCode->type === DiscountCodeType::Amount
            ) {
                $excessDiscountValue =
                    ($request->all()['features'][
                        $game
                            ->features()
                            ->where('type', 'person_as_slot')
                            ->first()->id
                        ]['persons_count'] -
                        1) *
                    $reservationSlot->discountCode->value;
                $result['basePrice'] -= $excessDiscountValue;
                $result['finalPrice'] -= $excessDiscountValue;
            }

            return returnResult($result, $resultType);
        }

        $slotsIds =
            count(array_filter($request->all()['slot_ids'] ?? [0 => null])) ===
            (int) $request->all()['slots_count']
                ? $request->all()['slot_ids']
                : self::getVacantSlotsIds(
                $game,
                $request->all()['start_at'],
                $request->all()['duration'] ?? 60,
                $request->all()['slot_ids'] ?? [0 => null],
                $request->all()['slot_id'] ?? null,
                (int) ($request->all()['slots_count'] ?? count($request->all()['slot_ids'])),
                $request->all()['features'] ?? []
            );

        $isFirstReservationSlotInReservation = true;
        $reservationOrder = 1;
        $slots = Slot::whereIn('id', $slotsIds)
            ->with('features')
            ->get();

        foreach ($slotsIds as $slotId) {
            if (isset($slotsInReservation[$slotId])) {
                $reservationSlot = $slotsInReservation[$slotId];
            } else {
                $reservationSlot = new ReservationSlot();

                $reservationSlot->fillWithRequest($request);
                $reservationSlot->fill([
                    'slot_id' => $slotId,
                    'start_at' => $data['start_at']->clone(),
                    'end_at' => $data['end_at']->clone(),
                ]);
            }

            $prices = $reservationSlot->calculatePrice(
                $customer->id ?? null,
                array_merge($data, ['slot' => $slots->where('id', $slotId)->first()]),
                $isFirstReservationSlotInReservation,
                $amountToDiscount,
                $reservationOrder++
            );

            $result['priceBeforeDiscount'] += $prices['priceBeforeDiscount'];
            $result['basePrice'] += $prices['basePrice'];
            $result['finalPrice'] += $prices['finalPrice'];
            $result['setsPrice'] += $prices['setsPrice'];
            $amountToDiscount = $prices['remainingAmountToDiscount'];
            $isFirstReservationSlotInReservation = false;
        }

        $result['basePrice'] = max(0, $result['basePrice']);
        $result['setsPrice'] = max(0, $result['setsPrice']);
        $result['finalPrice'] = max(0, $result['finalPrice']);
        $result['priceBeforeDiscount'] = $result['priceBeforeDiscount'];
        $result['reservationFee'] = $reservationFeeValue;

        return returnResult($result, $resultType);
    }

    public function reservationNumber(): MorphOne
    {
        return $this->morphOne(ReservationNumber::class, 'numerable');
    }

    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }

    public function getPrice(Collection|null $reservationSlotsPreloaded = null): int
    {
        $price = 0;
        $reservationSlots = $reservationSlotsPreloaded ?? self::getReservation($this->id)->reservationSlots;
        foreach ($reservationSlots as $reservationSlot) {
            $price += $reservationSlot->final_price;
            $price += $reservationSlot->sets()->sum('reservation_slot_set.price');
        }

        return $price;
    }

    public function reservable(): MorphTo
    {
        return $this->morphTo();
    }

    public function changes(): MorphMany
    {
        return $this->morphMany(DataChange::class, 'changable');
    }

    public function returner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returner_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function canceler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'canceled_by', 'id');
    }

    /*
     * Many models in the project have a polymorphic relationship with the models:
     * Reservation and ReservationSlot. ReservationSlot always belongs to Reservation.
     * Thanks to the following method, we can obtain a reservation model regardless of the start model
     * received from the polymorphic relation we have.
     */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function sets(): HasManyDeep
    {
        return $this->hasManyDeep(Set::class, [ReservationSlot::class, 'reservation_slot_set']);
    }

    public function duration(): Attribute
    {
        return Attribute::make(
            get: fn() => Carbon::parse($this->reservationSlots->max('end_at'))->diffInMinutes(
                $this->reservationSlots->min('start_at')
            )
        );
    }

    public function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => number_format((int)$this->price / 100, 2, ',', ' ') . ' ' . $this->currency
        );
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /*
     * Timezone handle
     */

    public function getPaymentCurrency(): string
    {
        return $this->currency;
    }

    public function getPaymentTotal(): int
    {
        return $this->price;
    }

    public function getPaymentCommission(
        int $price = null,
        ReservationSlot|null $preloadedFirstReservationSlot = null
    ): int {
        $club = $this->getClub($preloadedFirstReservationSlot);
        $gamePivot = $club->getGames()->find($this->game_id)->pivot;
        return round(
            (($price ?? $this->price) * ((float)$gamePivot->fee_percent)) / 100 + $gamePivot->fee_fixed
        );
    }

    public function formattedRate(string $rateType): string
    {
        if (in_array($rateType, ['service', 'atmosphere', 'staff'], true)) {
            $rateTypeField = "rate_$rateType";
            $rateValue = $this->getOriginal($rateTypeField);
        } else {
            $rateValue = $this->rate_staff + $this->rate_atmosphere + $this->rate_service;
            $rateValue /= 3;
            $rateValue = round($rateValue, 2);
        }
        return number_format($rateValue, 2, '.', '');
    }

    public function getClub(ReservationSlot|null $preloadedReservationSlot = null): Club|null
    {
        return ($preloadedReservationSlot ?? $this->firstReservationSlot)->getClub();
    }

    protected function reservation(): Attribute
    {
        return Attribute::make(get: fn() => $this);
    }

    protected function paidAt(): Attribute
    {
        return Timezone::getClubLocalizedDatetimeAttribute();
    }

    protected function expiredAt(): Attribute
    {
        return Timezone::getClubLocalizedDatetimeAttribute();
    }

    public function sendStoreNotification()
    {
        $notifiable = Customer::getCustomer($this->customer_id);
        if ($notifiable === null) {
            $slot = Slot::getSlot($this->firstReservationSlot->slot_id);
            $pricelist = Pricelist::getPricelist($slot->pricelist_id);
            $notifiable = (new AnonymousNotifiable())
                ->route('mail', $this->unregistered_customer_data['email'])
                ->route('phone', $this->unregistered_customer_data['phone'])
                ->route('clubId', $pricelist->club_id);
        }

//		$now = now('UTC');
//		$slot = $this->firstReservationSlot;
//		$minutes = $slot->start_at->diffInMinutes($now);
//		$reservationIn30Minutes = $minutes <= 30;

        $notifiable?->notify(
            new CustomerReservationStoredNotification(
                $this->firstReservationSlot->numberModel(),
                $this->source === ReservationSource::Widget ? ['mail'] : [],
//				$reservationIn30Minutes ? ['sms'] : [],
                [],
                ['reservation' => $this]
            )
        );
    }
}