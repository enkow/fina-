<?php

namespace App\Models;

use App\Custom\Timezone;
use App\Enums\DiscountCodeType;
use App\Enums\ReservationSlotCancelationType;
use App\Enums\ReservationSlotStatus;
use App\Events\CalendarDataChanged;
use App\Filters\ReservationSlot\CancelationTypeFilter;
use App\Filters\ReservationSlot\GameFilter;
use App\Filters\ReservationSlot\PaymentStatusFilter;
use App\Filters\ReservationSlot\PaymentTypeFilter;
use App\Filters\ReservationSlot\StartAtRangeFilter;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\DiscountCodeResource;
use App\Http\Resources\FeatureResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\ReservationTypeResource;
use App\Http\Resources\SlotResource;
use App\Http\Resources\SpecialOfferResource;
use App\Models\Features\SlotHasBulb;
use App\Searchers\ReservationSlot\CustomerEmailSearcher;
use App\Searchers\ReservationSlot\CustomerNameSearcher;
use App\Searchers\ReservationSlot\CustomerPhoneSearcher;
use App\Searchers\ReservationSlot\NumberSearcher;
use App\Searchers\ReservationSlot\ReservationClubNoteSearcher;
use App\Sorters\ReservationSlot\FinalPriceSorter;
use App\Sorters\ReservationSlot\PersonCountSorter;
use App\Sorters\ReservationSlot\SlotNameSorter;
use App\Sorters\ReservationSlot\CreatedDatetimeSorter;
use App\Sorters\ReservationSlot\CustomerNameSorter;
use App\Sorters\ReservationSlot\NumberSorter;
use App\Sorters\ReservationSlot\SlotsCountSorter;
use App\Sorters\ReservationSlot\StartDatetimeSorter;
use App\Sorters\ReservationSlot\StatusSorter;
use App\Sorters\ReservationSlot\TimeRangeSorter;
use App\Traits\Filterable;
use App\Traits\Searchable;
use App\Traits\Sortable;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use JsonException;

class ReservationSlot extends BaseModel
{
	use Filterable, Searchable, Sortable, SoftDeletes;

	public static array $loggable = [
		'reservation_id',
		'slot_id',
		'occupied_status',
		'club_reservation',
		'status',
		'discount_code_id',
		'special_offer_id',
		'reservation_type_id',
		'settlement_id',
		'reservation_id',
		'slot_id',
		'start_at',
		'end_at',
		'settlement_id',
		'cancelation_type',
		'canceler_id',
		'cancelation_reason',
		'canceled_at',
		'price',
		'final_price',
		'presence',
		'occupied_status',
		'creator_id',
	];
	public static array $availableFilters = [
		GameFilter::class,
		StartAtRangeFilter::class,
		CancelationTypeFilter::class,
		PaymentTypeFilter::class,
		PaymentStatusFilter::class,
	];
	public static array $availableSearchers = [
		NumberSearcher::class,
		CustomerNameSearcher::class,
		CustomerPhoneSearcher::class,
		CustomerEmailSearcher::class,
        ReservationClubNoteSearcher::class
	];
	//Classes that can be added in the Filterable trait method
	public static array $availableSorters = [
		CreatedDatetimeSorter::class,
		NumberSorter::class,
		CustomerNameSorter::class,
		TimeRangeSorter::class,
		StartDatetimeSorter::class,
		SlotsCountSorter::class,
		SlotNameSorter::class,
		PersonCountSorter::class,
		FinalPriceSorter::class,
		StatusSorter::class,
	];

	//Classes that can be added in the Searchable trait method
	protected $fillable = [
		'reservation_id',
		'slot_id',
		'occupied_status',
		'club_reservation',
		'status',
		'start_at',
		'end_at',
		'discount_code_id',
		'discount_code_amount',
		'special_offer_id',
		'special_offer_price',
		'reservation_type_id',
		'settlement_id',
		'timer_enabled',
		'price',
		'final_price',
		'cancelation_type',
		'cancelation_reason',
		'canceler_id',
		'canceled_at',
		'creator_id',
		'refund_id',
		'presence',
		'app_commission_partial',
		'club_commission_partial',
	];

	//Classes that can be added in the Sortable trait method
	protected $casts = [
		'status' => ReservationSlotStatus::class,
		'cancelation_type' => ReservationSlotCancelationType::class,
		'canceled_at' => 'datetime',
		'club_reservation' => 'boolean',
		'presence' => 'boolean',
		'timer_enabled' => 'boolean',
		'start_at' => 'datetime',
		'end_at' => 'datetime',
	];

	public static function getFieldLocaleNames(int $gameId = null): array
	{
		return [
			'club_reservation' => __('reservation.club-reservation'),
			'discount_code_id' => __('discount-code.singular'),
			'special_offer_id' => __('special-offer.singular'),
			'reservation_type_id' => __('reservation-type.singular'),
			'settlement_id' => __('settlement.singular'),
			'status' => ucfirst(__('main.status')),
			'cancelation_type' => ucfirst(__('reservation.canceled-by')),
			'canceled_at' => ucfirst(__('main.canceled-female')),
			'cancelation_reason' => __('widget.cancelation-reason'),
			'canceler_id' => __('reservation.canceler'),
			'slot_id' => ucfirst(Translation::gamesTranslationsArray()[$gameId ?? 1]['slot-singular-short']),
			'presence' => __('customer.presence'),
			'occupied_status' => __('reservation.reduce-the-online-number-of-available-slots'),
			'start_at' => ucfirst(__('main.start-date')),
			'end_at' => ucfirst(__('main.end-date')),
		];
	}

	/*
	 * Many models in the project have a polymorphic relationship with the models:
	 * Reservation and ReservationSlot. ReservationSlot always belongs to Reservation.
	 * Thanks to the following method, we can obtain a reservation model regardless of the start model
	 * received from the polymorphic relation we have.
	 */

	public function reservation(): BelongsTo
	{
		return $this->belongsTo(Reservation::class);
	}

	public function scopeWithReservation()
	{
		return $this->join('reservations', 'reservation_slots.reservation_id', '=', 'reservations.id');
	}

	public function slot(): BelongsTo
	{
		return $this->belongsTo(Slot::class);
	}

	public function reservationType(): BelongsTo
	{
		return $this->belongsTo(ReservationType::class);
	}

	public function discountCode(): BelongsTo
	{
		return $this->belongsTo(DiscountCode::class);
	}

	public function specialOffer(): BelongsTo
	{
		return $this->belongsTo(SpecialOffer::class);
	}

	public function features(): MorphToMany
	{
		return $this->morphToMany(Feature::class, 'describable', 'feature_payload')->withPivot('data');
	}

	public function changes(): MorphMany
	{
		return $this->morphMany(DataChange::class, 'changable');
	}

	public function refund(): BelongsTo
	{
		return $this->belongsTo(Refund::class);
	}

	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, 'creator_id');
	}

	public function formattedPrice(): Attribute
	{
		return Attribute::make(
			get: fn() => number_format($this->price / 100, 2, ',', ' ') .
				' ' .
				$this->reservation->customer->club->country->currency
		);
	}

	public function duration(): Attribute
	{
		return Attribute::make(
			get: fn() => $this->timer_enabled
				? round(ReservationSlotTimerEntry::sumSecondsByReservationSlotId($this->id) / 60, 0)
				: $this->end_at->diffInMinutes($this->start_at)
		);
	}

	public function reservationTimeRange(): Attribute
	{
		return Attribute::make(
			get: fn() => $this->start_at->format('H:i') . ' - ' . $this->end_at->format('H:i')
		);
	}

	public function scopeActive(Builder $query): void
	{
		$query->where(function ($query) {
			$query->whereIn('status', [ReservationSlotStatus::Confirmed, ReservationSlotStatus::Pending]);
			//            $query->orWhere(function ($query) {
			//                $query->where('status', ReservationSlotStatus::Pending);
			//                $query->where('reservation_slots.created_at', '>', now()->subMinutes(5));
			//            });
			$query->orWhere(function ($query) {
				$query->whereHas('reservation', function ($query) {
					$query->whereHas('paymentMethod', function ($query) {
						$query->where('online', 0);
					});
				});
			});
		});
	}

	public function updatePartialCommissions(
		Reservation|null $reservation = null,
		Collection|null $reservationSlots = null
	): void {
		$reservationSlot = $this;

		ReservationSlot::withoutEvents(function () use ($reservationSlot, $reservation, $reservationSlots) {
			$reservationSlot->update([
				'app_commission_partial' => $reservationSlot->getPartialPaymentCommission(
					$reservation,
					$reservationSlots
				),
				'club_commission_partial' => $reservationSlot->getPartialClubCommission(
					$reservation,
					$reservationSlots
				),
			]);
		});
	}

	public function getPartialPaymentCommission(
		Reservation|null $reservationPreloaded = null,
		Collection|null $reservationSlotsPreloaded = null
	): int {
		$reservation = $reservationPreloaded ?? $this->reservation;
		$reservationSlots = $reservationSlotsPreloaded ?? $reservation->reservationSlots;
		$first = $reservationSlots->first()->id === $this->id;

		$app_commission = $reservation->app_commission;
		$additional_partial_commission = $app_commission % $reservationSlots->count();
		$partial_commission =
			($app_commission - $additional_partial_commission) / $reservationSlots->count() +
			($first ? $additional_partial_commission : 0);

		return $partial_commission;
	}

	public function getPartialClubCommission(
		Reservation|null $reservationPreloaded = null,
		Collection|null $reservationSlotsPreloaded = null
	): int {
		$reservation = $reservationPreloaded ?? $this->reservation;
		$reservationSlots = $reservationSlotsPreloaded ?? $reservation->reservationSlots;
		$first = $reservationSlots->first()->id === $this->id;

		$club_commission = $reservation->club_commission;
		$additional_partial_commission = $club_commission % $reservationSlots->count();
		$partial_commission =
			($club_commission - $additional_partial_commission) / $reservationSlots->count() +
			($first ? $additional_partial_commission : 0);

		return $partial_commission;
	}

	public function scopeCanceled(Builder $query, bool $status)
	{
		if ($status) {
			$query->whereNotNull('cancelation_type');
		} else {
			$query->whereNull('cancelation_type');
		}
	}

	/**
	 * @throws JsonException
	 */
	public function prepareForOutput(
		$extendedData = false,
		$preloadedCollections = [],
		$output = null,
		$withoutFields = []
	): mixed {
		if ($this->cancelation_type) {
			$this->status = ReservationSlotStatus::Expired;
		}

		$reservationSlot = (object) [];
		$reservation = $preloadedCollections['reservation'] ?? $this->reservation;

		$slot = isset($preloadedCollections['slots'])
			? $preloadedCollections['slots']->where('id', $this->slot_id)->first()
			: null;
		if ($slot === null) {
			$slot = Slot::getSlot($this->slot_id);
			if (
				Game::getCached()
					->find($this->reservation->game_id)
					->hasFeature('slot_has_parent')
			) {
				$slot = $slot->load('parentSlot');
			}
		}

		$customer = null;
		if ($reservation->customer_id) {
			$customer =
				isset($preloadedCollections['customers']) &&
				!empty(
					($customerTmp = $preloadedCollections['customers']
						->where('id', $reservation->customer_id)
						->first())
				)
					? $customerTmp
					: Customer::getCustomer($reservation->customer_id);
		}
		$unregisteredCustomerData = $reservation->unregistered_customer_data;

		$reservationSlot->club_reservation = $this->club_reservation;
		$reservationSlot->club_note = $reservation->club_note;
		$reservationSlot->club_commission_partial = $this->club_commission_partial ?? 0;
		$reservationSlot->customer_name = $customer
			? $customer->first_name . ' ' . $customer->last_name
			: ($unregisteredCustomerData['first_name'] ?? '') .
				' ' .
				($unregisteredCustomerData['last_name'] ?? '');
		$reservationSlot->customer_name = trim($reservationSlot->customer_name);
		$reservationSlot->customer_note = $reservation->customer_note;

		$reservationSlot->customer_phone = $customer?->phone ?? ($unregisteredCustomerData['phone'] ?? '');
		$reservationSlot->customer_phone = trim($reservationSlot->customer_phone);

		$reservationSlot->customer_email = $customer?->email ?? ($unregisteredCustomerData['email'] ?? '');
		$reservationSlot->customer_email = trim($reservationSlot->customer_email);

		$reservationSlot->show_customer_note_on_calendar = $reservation->show_customer_note_on_calendar;
		$reservationSlot->show_club_note_on_calendar = $reservation->show_club_note_on_calendar;
		$paymentMethod = PaymentMethod::getPaymentMethod($reservation->payment_method_id);

		$reservationSlot->created_at = $reservation->created_at->format('Y-m-d H:i:s');
		$reservationSlot->calendar_color = match (true) {
			$this->club_reservation => '#9AA1B3',
			$this->status === ReservationSlotStatus::Confirmed => '#1BC5BD',
			$this->status === ReservationSlotStatus::Pending && $paymentMethod->online => '#FFAA07',
			$this->status === ReservationSlotStatus::Pending && !$paymentMethod->online => '#3699FF',
			default => $this->status_color,
		};
		$reservationSlot->number = $this->number;
		$reservationSlot->reservation_number_id =
			$this->reservationNumber?->id ?? ($reservation->reservationNumber?->id ?? 0);


		$reservationSlot->parent_slot_id = null;
		$reservationSlot->parent_slot_name = '';
        $game = ($preloadedCollections['game'] ?? Game::getCached()
            ->find($reservation->game_id));
		if (
            ($preloadedCollections['game_feature_statuses']['slot_has_parent'] ?? $game->hasFeature('slot_has_parent')) &&
			!in_array('parent_slot_name', $withoutFields, true)
		) {
			if (isset($preloadedCollections['parent_slots'])) {
                $parentSlot = $preloadedCollections['parent_slots']
                    ?->where('id', $this->slot->slot_id)
                    ->first();
				$reservationSlot->parent_slot_name = $parentSlot?->name;
				$reservationSlot->parent_slot_id = $parentSlot?->id;
			} else {
				$reservationSlot->parent_slot_name = $this->slot->parentSlot?->name;
				$reservationSlot->parent_slot_id = $this->slot->parentSlot?->id;
			}
		}

		$pricePerPersonSlotData =
			$this->features->where('type', 'price_per_person')->first()->pivot->data ?? json_encode([]);
		$pricePerPersonSlotData = json_decode($pricePerPersonSlotData, true);
		$reservationSlot->person_count = $pricePerPersonSlotData['person_count'] ?? 1;
		$reservationSlot->sets = Set::reduce($this->sets);
        $localStartAt = Timezone::convertToLocal($this->getRawOriginal('start_at'));
        $localStartAtRaw = Timezone::convertToLocal(
            now()
                ->parse($this->getRawOriginal('start_at'), 'UTC')
                ->seconds(0)
        );
        $localEndAtRaw = Timezone::convertToLocal(
            now()
                ->parse($this->getRawOriginal('end_at'), 'UTC')
                ->seconds(0)
        );
        $timerEnabled = $this->getTimerEnabled(['game' => $game, 'start_at' => $localStartAt->clone()]);
        $localEndAt = $timerEnabled === true
            ? now() : $localEndAtRaw;
        $prices = $this->calculatePrice($customer?->id, [
            'loadTimers' => $timerEnabled,
            'game' => $preloadedCollections['game'] ?? null,
            'game_id' => $preloadedCollections['game']?->id ?? null,
            'slot' => $slot,
            'allFeatures' => $preloadedCollections['features'] ?? null,
            'club_id' => ($preloadedCollections['club']?->id ?? $slot->pricelist->club_id),
            'start_at_converted' => $localStartAt,
            'end_at_converted' => $localEndAt,
        ]);
		$reservationSlot->final_price =
			(0
				? $prices['finalPrice']
				: $this->final_price) +
			array_sum(array_column($reservationSlot->sets, 'price')) +
			$this->club_commission_partial;
		$reservationSlot->payment_method_id = $reservation->payment_method_id;
		$reservationSlot->payment_method_online = $paymentMethod->online;
		$reservationSlot->reservation_type_color = $this->reservation_type_id
			? $this->reservationType?->color
			: 'transparent';
		$reservationSlot->slot_name = $slot->name;
		$reservationSlot->slot_id = $slot->id;

		$bookSingularSlotByCapacityData =
			$slot->features->where('type', 'book_singular_slot_by_capacity')->first()->pivot->data ??
			json_encode([], JSON_THROW_ON_ERROR | true);
		$bookSingularSlotByCapacityArray = json_decode(
			$bookSingularSlotByCapacityData,
			true,
			512,
			JSON_THROW_ON_ERROR
		);
		$reservationSlot->slot_capacity = $bookSingularSlotByCapacityArray['capacity'] ?? 1;

		$reservationSlot->status_color = $this->status_color;
		$reservationSlot->status_locale = $this->statusLocale(null, $reservation);
		$reservationSlot->start_datetime = $localStartAt->clone()
			->format('Y-m-d H:i:s');
		$reservationSlot->start_datetime_raw = $localStartAtRaw->clone()
			->format('Y-m-d H:i:s');
		$reservationSlot->end_datetime = $localEndAt->clone()->format('Y-m-d H:i:s');
		$reservationSlot->end_datetime_raw = $localEndAtRaw->clone()->format('Y-m-d H:i:s');
		$reservationSlot->created_datetime = $this->created_at->format('Y-m-d H:i:s');
		$reservationSlot->source = $reservation->source->value;
		$reservationSlot->occupied_status = $this->occupied_status;
		$reservationSlot->reservation_id = $this->reservation_id;
		$reservationSlot->status = $this->status;
		$reservationSlot->reservation_slot_id = $this->id;

		$reservationSlot->timer_status = match (true) {
			!$this->areTimersAvailable(slot: $slot, preloadedReservation: $reservation) => 0, // not available
			count($this->timerEntries) === 0 => 1, // not started
			count($this->timerEntries->whereNull('end_at')) > 0 => 2, // started
			count($this->timerEntries->where('stopped', true)) === 0 => 3, // paused
			default => 4, // stopped
		};

		$json = $this->features->where('type', 'reservation_slot_has_display_name')->first()?->pivot?->data;
		$reservationSlot->display_name = Reservation::getDisplayName($customer, $unregisteredCustomerData);
		$reservationSlot->custom_display_name = $json
			? json_decode($json, true, 512, JSON_THROW_ON_ERROR)['display_name']
			: null;

		$reservationSlot->cancelation_status = (bool) $this->cancelation_type;
		if ($extendedData === true) {
			$clubId =
				clubId() ??
				(Pricelist::getPricelist($this->firstReservationSlot->slot->pricelist_id)->club_id ?? 1);

			$clubTimerStatusSetting = Cache::remember(
				'club:' . $clubId . ':timer_status',
				30,
				function () use ($clubId) {
					return Setting::getClubGameSetting($clubId ?? 1, 'timers_status');
				}
			);
            $localStartAt = Timezone::convertToLocal($this->getRawOriginal('start_at'));
			$discountCode = null;
			if (!$this->club_reservation && $this->discount_code_id) {
				$discountCode =
					isset($preloadedCollections['discountCodes']) &&
					!empty(
						($discountCodeTmp = $preloadedCollections['discountCodes']
							->where('id', $this->discount_code_id)
							->first())
					)
						? $discountCodeTmp
						: $this->discountCode;
			}
			$specialOffer = null;
			if (!$this->club_reservation && $this->special_offer_id) {
				$specialOffer =
					isset($preloadedCollections['specialOffers']) &&
					!empty(
						($specialOfferTmp = $preloadedCollections['specialOffers']
							->where('id', $this->special_offer_id)
							->first())
					)
						? $specialOfferTmp
						: $this->specialOffer;
			}

            $game = new GameResource($reservation->game->load('features'));
            unset($game->icon);

            $slot = $this->slot;

            $totalCustomerReservationDurationInMinutes = $customer ? (DB::table('reservation_slots')
                    ->join('reservations', 'reservation_slots.reservation_id', '=', 'reservations.id')
                    ->where('reservations.customer_id', $customer->id)
                    ->select(DB::raw('SUM(TIMESTAMPDIFF(MINUTE, start_at, end_at)) as total_duration'))
                    ->first()
                    ->total_duration / 60) : 0;
            $reservationReservationSlots = $reservation->reservationSlots()->with('sets')->get();
            unset($customer->reservationSlots);
            $customerReservationSlotsSums = $customer?->reservationSlots()
                ->whereIn('status', [ReservationSlotStatus::Confirmed, ReservationSlotStatus::Pending])
                ->whereNull('canceled_at')
                ->selectRaw('sum(final_price) as sum_price, sum(club_commission_partial) as sum_commission')
                ->first() ?? (object)[
                    'sum_price' => 0,
                    'sum_commission' => 0
            ];
            $customerSetsPriceSum = $customer?->boughtSets()->sum('reservation_slot_set.price');
			$reservationSlot->extended = [
				'calendar_name' => $this->calendar_name,
				'cancelation_type' => $this->cancelation_type,
				'customer' => $customer ? new CustomerResource($customer->load('tags')) : null,
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
				'creator_email' => User::getUser($this->creator_id)->email ?? '',
				'discountCode' => $discountCode ? new DiscountCodeResource($discountCode) : null,
				'duration' => ($timerEnabled === true
					? (now('UTC')->seconds() === 0
						? now('UTC')
						: now('UTC')
							->addMinute()
							->seconds(0))
					: now()
						->parse($this->getRawOriginal('end_at'), 'UTC')
						->seconds(0)
				)->diffInMinutes(
					now()
						->parse($this->getRawOriginal('start_at'), 'UTC')
						->seconds(0)
				),
				'features' => FeatureResource::collection($this->features, ['translations']),
				'final_price' => $timerEnabled
					? $prices['finalPrice']
					: ($this->club_reservation
						? 0
						: $this->final_price),
				'reservation_price' =>
                    $reservation->price - $reservation->sets()->sum('reservation_slot_set.price'),
				'reservation_final_price' => $reservation->price,
				'game' => $game,
				'online_status' => $this->club_reservation
					? false
					: PaymentMethod::getPaymentMethod($reservation->payment_method_id)->online,
				'presence' => $this->club_reservation ? true : $this->presence,
				'price' => $timerEnabled
					? $prices['basePrice']
					: ($this->club_reservation
						? 0
						: $this->price),
				'relatedReservations' =>
					$reservationReservationSlots->load('timerEntries','slot','reservationNumber')
					->where('id', '!=', $this->id)
					->map(function (ReservationSlot $relatedReservationSlot) use (
						$reservationSlot,
						$preloadedCollections,
						$output,
						$reservation,
                        $game
					) {
						$relatedReservationSlot->number =
							$output?->where('id', $relatedReservationSlot->id)->first()?->number ??
							$relatedReservationSlot->number;

						$relatedReservationSlotSlot = $relatedReservationSlot->slot;

						$relatedReservationSlot->status_locale = $relatedReservationSlot->statusLocale(
							null,
							$reservation
						);
                        unset($game->icon);

						$relatedReservationSlot->extended = [
							'created_at' => $relatedReservationSlot->created_at,
							'slot_name' => $relatedReservationSlotSlot->name,
							'status' => $relatedReservationSlot->status,
						];
                        unset($relatedReservationSlot->reservation);
                        unset($relatedReservationSlot->slot);
                        unset($relatedReservationSlot->reservationNumber);

						return $relatedReservationSlot;
					})
					->toArray(),
				'reservationType' => $this->reservation_type_id
					? new ReservationTypeResource(
						$this->reservationType()
							->select('id', 'name', 'color')
							->first()
					)
					: null,
				'slot' => $slot ? new SlotResource($slot) : null,
				'occupied_status' => $this->occupied_status,
				'slotFeatures' => $this->slot->features,
				'specialOffer' => $specialOffer ? new SpecialOfferResource($specialOffer) : null,
				'status' => $this->status,
				'timer_enabled' =>
                    ($preloadedCollections['game_feature_statuses']['has_timers'] ?? $game->hasFeature('has_timers')) && $clubTimerStatusSetting['value'] === true
						? $timerEnabled
						: null,
				'total_paid' => $this->club_reservation ? 0 : $this->reservation->payments()->sum('total'),
				'' => $this->club_reservation
					? 0
					: $reservationReservationSlots
                        ->whereNull('canceled_at')
                        ->reduce(static function ($carry, $slot) {
                            return $carry +
                                $slot['final_price'] +
                                $slot['club_commission_partial'];
                        }, 0) +
						$reservationReservationSlots
                            ->whereNull('canceled_at')
							->reduce(static function ($carry, $slot) {
								return $carry +
									array_reduce(
										$slot
											->sets
											->toArray(),
										static function ($carry, $set) {
											return $carry + $set['pivot']['price'];
										},
										0
									);
							}, 0),
//				'reservable_type' => $this->reservable_type,
			];
		}

		return $reservationSlot;
	}

	/**
	 * @throws JsonException
	 */
	public function calculatePrice(
		int|null $customerId = null,
		array $data = [],
		bool $isFirstReservationSlotInReservation = false,
		int $amountToDiscount = 0,
		int|null $reservationOrder = null
	): array {
		if ($this->club_reservation) {
			return [
				'basePrice' => 0,
				'finalPrice' => 0,
				'special_offer_amount' => 0,
				'remainingAmountToDiscount' => 0,
				'setsPrice' => 0,
				'priceBeforeDiscount' => 0,
			];
		}

        $customPrice = $data['custom_price'] ?? false ? $data['price'] : null;
        $customPrice = $customPrice ? floatval($customPrice) : null;
        $priceToThisSlot = round(($customPrice / ($data['slots_count'] ?? 1)) * ($reservationOrder ?? 1));
        $priceToLastSlot = round(
            ($customPrice / ($data['slots_count'] ?? 1)) * (($reservationOrder ?? 1) - 1)
        );
        $customPrice = $customPrice ? $priceToThisSlot - $priceToLastSlot : null;

        // if reservation is paid by online payment method return its original price - it should not be changed
        if($this->reservation) {

            $paymentMethod = PaymentMethod::getPaymentMethod($this->reservation->payment_method_id);
            if($paymentMethod->online === true && $this->status === ReservationSlotStatus::Confirmed) {
                $customPrice = $this->price;
            }
        }

		$slot = match (true) {
			isset($data['slot']) && is_int($data['slot']) && $data['slot'] > 0 => Slot::getSlot(
				$data['slot_id']
			),
			isset($data['slot']) => $data['slot'],
			default => Slot::getSlot($this->slot_id),
		};

		$data['slot_id'] = $slot->id;
        $pricelist = Pricelist::getPricelist($slot->pricelist_id);
		$data['game_id'] = $data['game_id'] ?? $pricelist->game_id;
		$game = $data['game'] ?? Game::getCached()
			->where('id', $data['game_id'])
			->first();
        $startAt = match(true) {
            isset($data['start_at_converted']) => $data['start_at_converted'],
            isset($data['start_at']) => now('UTC')
                ->parse($data['start_at'])
                ->timezone(date_default_timezone_get()),
            default => $this->start_at->clone(),
        };
        $endAt = match(true) {
            isset($data['end_at_converted']) => $data['end_at_converted'],
            isset($data['duration']) => $startAt->clone()->addMinutes($data['duration']),
            default => $this->end_at->clone(),
        };

		//if the game is numbered tables(book_singular_slot_by_capacity) or collective tables(person_as_slot), the price should always be calculated only for one hour of reservation
		if ($game->hasFeature('person_as_slot') || $game->hasFeature('book_singular_slot_by_capacity')) {
			$endAt = $startAt->clone()->addHour(1);
		}

		$timersReducedArray = [];
		$timerEntries = Cache::remember('reservationSlot:' . $this->id . ':timerEntries', 2, function () {
			return $this->timerEntries;
		});
		foreach ($timerEntries as $timerEntry) {
			$timersReducedArray[] = [
				$timerEntry->start_at,
				$timerEntry->end_at ?? (now('UTC')->seconds() === 0 ? now('UTC') : now('UTC')->seconds(0)),
			];
		}

		$basePrice = $finalPrice = $pricelistPriceSum = $discountedPricelistPriceSum = 0;

        if($game->hasFeature('book_singular_slot_by_capacity')) {
            $result = PricelistException::where('pricelist_id', $slot->pricelist_id)->where('start_at','<=',$startAt->clone()->endOfDay()->format('Y-m-d'))
                ->where('end_at','>=',$startAt->clone()->startOfDay()->format('Y-m-d'))
                ->where('from','<=',$startAt->clone()->seconds(0)->format('H:i:s'))
                ->where('to','>',$startAt->clone()->seconds(0)->format('H:i:s'))
                ->first();
            if(empty($result))
            {
                $result = PricelistItem::where('pricelist_id', $slot->pricelist_id)
                    ->where('from','<=',$startAt->clone()->seconds(0)->format('H:i:s'))
                    ->where('to','>=',$startAt->clone()->seconds(0)->format('H:i:s'))
                    ->where('day', weekDay($startAt))
                    ->first();
            }
            $specialOffer = $this->specialOffer ?? SpecialOffer::getSpecialOffer($data['special_offer_id'] ?? 0);
            $price = $result->price;
            $finalPricePart = $specialOffer ? round($price * (100 - $specialOffer->value) / 100) : $price;
            $pricelistPriceSum += $finalPricePart;
            $discountedPricelistPriceSum += $price;
            $basePrice = $customPrice ?? $basePrice + $price;
            $finalPrice = $customPrice ?? $finalPrice + $finalPricePart;
        }
        else {
            foreach (!($data['loadTimers'] ?? false) ? [[$startAt, $endAt]] : $timersReducedArray as $item) {
                $currentStartAt = $item[0];
                $currentEndAt = $item[1];
                $pricelistPrice = Cache::remember(
                    'pricelist_id' .
                    $slot->pricelist_id .
                    '_start_' .
                    $currentStartAt->clone()->unix() .
                    '_end_' .
                    $currentEndAt->clone()->unix() .
                    '_calculate_price',
                    2,
                    function () use ($slot, $currentStartAt, $currentEndAt, $pricelist) {
                        return $pricelist->calculatePrice(
                            $currentStartAt->clone(),
                            $currentEndAt->clone()
                        );
                    }
                );

                $special_offer_id = $data['special_offer_id'] ?? null;


                $discountedPricelistPrice = Cache::remember(
                    'pricelist_id' .
                    $slot->pricelist_id .
                    '_special_offer_' .
                    $special_offer_id .
                    '_slot_id_' .
                    $slot->id .
                    '_start_' .
                    $currentStartAt->clone()->unix() .
                    '_end_' .
                    $currentEndAt->clone()->unix() .
                    '_calculate_discounted_price',
                    1,
                    function () use ($slot, $currentStartAt, $currentEndAt, $special_offer_id, $pricelist) {
                        $specialOffer = $this->specialOffer ?? SpecialOffer::getSpecialOffer($special_offer_id);
                        $price = 0;
                        if ($special_offer_id && $specialOffer && $specialOffer->time_range_type === 'end') {
                            $specialOfferEndRanges = $specialOffer->time_range['end'] ?? [];

                            $offerPeriods = [];
                            $nonOfferPeriods = [];

                            $reservationPeriod = CarbonPeriod::create(
                                $currentStartAt->clone(),
                                $currentEndAt->clone()
                            )/*->setDateInterval(new \DateInterval('PT15M'))*/;

                            foreach ($reservationPeriod as $day) {
                                if (in_array(weekDay($day), $specialOffer->active_week_days)) {
                                    foreach ($specialOfferEndRanges as $range) {
                                        $specialOfferStartAt = now('UTC')->parse(
                                            $day->format('Y-m-d') . ' ' . $range['from']
                                        );
                                        $specialOfferEndAt = now('UTC')->parse(
                                            $day->format('Y-m-d') . ' ' . $range['to']
                                        );

                                        $reservationStartAt = $currentStartAt;
                                        $reservationEndAt = $currentEndAt;

                                        if ($reservationStartAt->lt($specialOfferStartAt)) {
                                            $nonOfferPeriods[] = [
                                                'from' => $reservationStartAt,
                                                'to' => $specialOfferStartAt->copy(),
                                            ];
                                        }

                                        if ($reservationEndAt->gt($specialOfferEndAt)) {
                                            $nonOfferPeriods[] = [
                                                'from' => $specialOfferEndAt->copy(),
                                                'to' => $reservationEndAt,
                                            ];
                                        }

                                        if (
                                            $reservationStartAt->lt($specialOfferEndAt) &&
                                            $reservationEndAt->gt($specialOfferStartAt)
                                        ) {
                                            $offerPeriods[] = [
                                                'from' => $specialOfferStartAt->max($reservationStartAt),
                                                'to' => $specialOfferEndAt->min($reservationEndAt),
                                            ];
                                        }
                                    }
                                } else {
                                    $nonOfferPeriods[] = [
                                        'from' => $day->copy()->startOfDay(),
                                        'to' => $day->copy()->endOfDay(),
                                    ];
                                }
                            }
                            foreach ($offerPeriods as $key => $offerPeriod) {
                                if ($offerPeriod['from']->lt($currentStartAt)) {
                                    $offerPeriods[$key]['from'] = $currentStartAt;
                                }
                                if ($offerPeriod['to']->gt($currentEndAt)) {
                                    $offerPeriods[$key]['to'] = $currentEndAt;
                                }
                            }
                            foreach ($nonOfferPeriods as $key => $nonOfferPeriod) {
                                if ($nonOfferPeriod['from']->lt($currentStartAt)) {
                                    $nonOfferPeriods[$key]['from'] = $currentStartAt;
                                }
                                if ($nonOfferPeriod['to']->gt($currentEndAt)) {
                                    $nonOfferPeriods[$key]['to'] = $currentEndAt;
                                }
                            }

                            foreach ($offerPeriods as $specialOfferPeriod) {
                                $priceMem = $pricelist->calculatePrice(
                                    $specialOfferPeriod['from']->clone()->timezone('UTC'),
                                    $specialOfferPeriod['to']->clone()->timezone('UTC'),
                                    true
                                );
                                $price += $this->specialOffer->calculatePrice(
                                    $priceMem,
                                    $specialOfferPeriod['from']->clone()->timezone('UTC'),
                                    $specialOfferPeriod['to']->clone()->timezone('UTC')
                                );
                            }
                            foreach ($nonOfferPeriods as $specialOfferPeriod) {
                                $price += $pricelist->calculatePrice(
                                    $specialOfferPeriod['from']->timezone('UTC'),
                                    $specialOfferPeriod['to']->timezone('UTC')
                                );
                            }
                        } elseif (
                            $special_offer_id &&
                            $specialOffer &&
                            $specialOffer->time_range_type === 'start'
                        ) {
                            $price = $pricelist->calculatePrice(
                                $currentStartAt->clone(),
                                $currentEndAt->clone()
                            );
                            $price = $specialOffer->calculatePrice(
                                $price,
                                $currentStartAt->clone(),
                                $currentEndAt->clone()
                            );
                        } else {
                            $price = $pricelist->calculatePrice(
                                $currentStartAt->clone(),
                                $currentEndAt->clone()
                            );
                        }
                        return $price;
                    }
                );

                $pricelistPriceSum += $pricelistPrice;
                $discountedPricelistPriceSum += $discountedPricelistPrice;
                $basePrice = $customPrice ?? $basePrice + $pricelistPrice;
                $finalPrice = $customPrice ?? $finalPrice + $discountedPricelistPrice;
            }

        }

		if (!isset($data['features'])) {
			$data['features'] = [];
			foreach ($this->features as $feature) {
				$data['features'][$feature->id] = json_decode(
					$feature->pivot->data,
					true,
					512,
					JSON_THROW_ON_ERROR
				);
			}
		}
		$features = $data['allFeatures'] ?? Feature::getCached();

		foreach ($slot->features as $slotFeature) {
			if (!isset($data['features'][$slotFeature->id])) {
				$data['features'][$slotFeature->id] = json_decode(
					$slotFeature->pivot->data,
					true,
					512,
					JSON_THROW_ON_ERROR
				);
			}
		}

		$bookSingularSlotByCapacityFeature = $features
			->where('type', 'book_singular_slot_by_capacity')
			->first();
		if (!isset($data['features'][$bookSingularSlotByCapacityFeature->id])) {
			$data['features'][$bookSingularSlotByCapacityFeature->id] = ['data' => true];
		}

		foreach ($data['features'] ?? [] as $featureId => $featureData) {
			if (
				!empty($featureData) &&
				$features->where('id', $featureId)->first()?->game_id === (int) $data['game_id']
			) {
				$features
					->find($featureId)
					?->calculateFeatureReservationPrice(
						$basePrice,
						$finalPrice,
						$data,
						$data['club_id'] ?? $pricelist->club_id,
						$reservationOrder
					);
			}
		}

		$discountCode =
			isset($data['discount_code_id']) && $data['discount_code_id']
				? DiscountCode::find($data['discount_code_id'])
				: null;

		$priceBeforeDiscount = $finalPrice;
		if ($discountCode && $discountCode?->isAvailable($customerId, $startAt, $startAt)) {
			if ($discountCode->type === DiscountCodeType::Percent) {
				$finalPrice = $discountCode->calculatePrice($finalPrice, $startAt->clone(), $endAt);
			}
		}

		$setsPrice = array_reduce(
			$this->sets->toArray() ?? [],
			static function ($carry, $set) {
				return $carry + $set['pivot']['price'];
			},
			0
		);

		$result = [
			'basePrice' => $basePrice,
			'finalPrice' => max($finalPrice - $amountToDiscount, 0),
			'special_offer_amount' => $pricelistPriceSum - $discountedPricelistPriceSum,
			'setsPrice' => $setsPrice,
			'priceBeforeDiscount' => $priceBeforeDiscount
		];
		$result['remainingAmountToDiscount'] = $amountToDiscount - ($finalPrice - $result['finalPrice']);

		return $result;
	}

	protected function reservationSlots(): Attribute
	{
		return Attribute::make(get: fn() => [$this]);
	}

	public function number(): Attribute
	{
		$reservationNumber = Cache::remember(
			'reservationSlot:' . $this->id . ':reservationNumber',
			2,
			function () {
				return $this->reservationNumber?->id ?? ($this->reservation->reservationNumber?->id ?? 0);
			}
		);

		return Attribute::make(get: fn() => str_pad($reservationNumber, 5, '0', STR_PAD_LEFT));
	}

	/**
	 * @throws JsonException
	 */
	// data presented below except extended key is used in reservation table
	// data in extended key is used in reservation modals
	public function reservationTypeColor(): Attribute
	{
		return Attribute::make(
			get: fn() => $this->reservation_type_id
				? ReservationType::getReservationType($this->reservation_type_id)->color
				: null
		);
	}

	public function statusColor(): Attribute
	{
		return Attribute::make(
			get: fn() => match ($this->status) {
				ReservationSlotStatus::Pending => '#FFAA07',
				ReservationSlotStatus::Confirmed => '#1BC5BD',
				ReservationSlotStatus::Expired => '#F64E60',
			}
		);
	}

	public function numberModel()
	{
		return Cache::remember('reservation_slot_id:' . $this->id . ':number_model', 10, function () {
			return $this->reservationNumber ?? ($this->reservation->reservationNumber ?? null);
		});
	}

	public function game(): Attribute
	{
		return Attribute::make(get: fn() => Game::getCached()->find($this->reservation->game_id));
	}

	public function modifyFields(&$data): void
	{
		$keys = ['slot_id', 'start_at', 'end_at', 'discount_code_id', 'special_offer_id', 'club_reservation'];

		foreach ($keys as $key) {
			if (isset($data[$key]) || $data[$key] === null) {
				$this->$key = $data[$key];
			}
		}
	}

	public function fillWithRequest(Request $request): void
	{
		$this->fill(self::fillWithData($request->all()));
	}

	public static function fillWithData(array $data): array
	{
		$game = isset($data['game_id']) ? Game::getCached()->find($data['game_id']) : null;
		$result = [
			'status' => $data['status'],
			'club_reservation' => $data['club_reservation'] ?? false,
			'discount_code_id' => $data['discount_code_id'] ?? null,
			'special_offer_id' => $data['special_offer_id'] ?? null,
			'reservation_type_id' => $data['reservation_type_id'] ?? null,
		];
		if ($game && $game->hasFeature('reservation_slot_has_occupied_status')) {
			$result['occupied_status'] = $data['occupied_status'] ?? true;
		}
		return $result;
	}

	public function makeAdditionalReservation(): ReservationSlot
	{
		$pricelist = $this->slot->pricelist;
		$calendarTimeScale = Setting::getClubGameSetting($pricelist->club_id, 'calendar_time_scale');
		$reservation = self::withoutEvents(function () use ($pricelist) {
			return Reservation::create([
				'game_id' => $pricelist->game_id,
				'payment_method_id' => PaymentMethod::where('online', false)->first()->id,
				'currency' => $pricelist->club->country->currency,
			]);
		});

		$reservationSlot = $reservation->reservationSlots()->create([
			'slot_id' => $this->slot_id,
			'club_reservation' => true,
			'presence' => true,
			'status' => ReservationSlotStatus::Confirmed,
			'start_at' => $endAt,
			'end_at' => $endAt->clone()->addMinutes($calendarTimeScale['value']),
			'price' => 0,
			'final_price' => 0,
		]);
		$reservationSlot->reservationNumber()->create();
		return $reservationSlot;
	}

	public function reservationNumber(): MorphOne
	{
		return $this->morphOne(ReservationNumber::class, 'numerable') ??
			$this->reservation->reservationNumber();
	}

	public function attachSets($sets, $club = null): void
	{
		$setIds = array_column($sets ?? [], 'id');
		$setsCollection = $club
			? $club->getSets()->whereIn('id', $setIds)
			: Set::whereIn('id', $setIds)->get();
		foreach ($sets ?? [] as $set) {
			if ($set['selected']) {
				for ($i = 0; $i < $set['selected']; $i++) {
					$setModel = $setsCollection->where('id', $set['id'])->first();
					$this->sets()->attach($set['id'], [
						'price' => $setModel->price,
					]);
				}
			}
		}
	}

	public function sets(): BelongsToMany
	{
		return $this->belongsToMany(Set::class)->withPivot('price');
	}

	public function statusLocale(
		$paymentMethodId = null,
		Reservation|null $preloadedReservation = null
	): string {
		$paymentMethod = PaymentMethod::getPaymentMethod(
			$paymentMethodId ?? ($preloadedReservation ?? $this->reservation)->payment_method_id
		);
		return match (true) {
			$this->club_reservation => Lang::get('reservation.status.club-reservation'),
			$this->cancelation_type === ReservationSlotCancelationType::Club => __(
				'reservation.status.canceled-by-club'
			),
			$this->cancelation_type === ReservationSlotCancelationType::Customer => __(
				'reservation.status.canceled-by-customer'
			),
			$this->cancelation_type === ReservationSlotCancelationType::System => __(
				'reservation.status.canceled-by-system'
			),
			$this->status === ReservationSlotStatus::Pending &&
				($this->created_at->addMinutes(5)->lt(now()) || $paymentMethod->online === false)
				=> Lang::get('reservation.status.unpaid'),

			$this->status === ReservationSlotStatus::Pending &&
				$paymentMethod->online === true &&
				$this->created_at->addMinutes(5)->gte(now())
				=> Lang::get('reservation.status.during-payment'),

			$this->status === ReservationSlotStatus::Confirmed &&
				!$paymentMethod->online &&
				$paymentMethod->code === 'card'
				=> Lang::get('reservation.status.paid'),

			$this->status === ReservationSlotStatus::Confirmed &&
				!$paymentMethod->online &&
				$paymentMethod->code === 'cash'
				=> Lang::get('reservation.status.paid'),

			$this->status === ReservationSlotStatus::Confirmed &&
				!$paymentMethod->online &&
				$paymentMethod->code === 'cashless'
				=> Lang::get('reservation.status.paid'),

			$this->status === ReservationSlotStatus::Confirmed && $paymentMethod->online => Lang::get(
				'reservation.status.paid'
			),

			default => '',
		};
	}

	protected function firstReservationSlot(): Attribute
	{
		return Attribute::make(get: fn() => $this);
	}

	protected function price(): Attribute
	{
		return Attribute::make(
			get: fn(string $value) => $this->timer_enabled
				? $this->calculatePrice($this->reservation->customer_id, ['loadTimers' => true])['finalPrice']
				: $value
		);
	}

    public function getTimerEnabled($data = [])
    {
        $result = false;
        $game = $data['game'] ?? Game::getCached()
            ->find(
                ($this->relationLoaded('reservation') && $this->reservation && $this->reservation->game_id
                    ? $this->reservation->game_id
                    : (Reservation::getReservation($this->reservation_id)?->game_id ?? 1)) ??
                Pricelist::getPricelist(Slot::getSlot($this->slot_id)->pricelist_id)->game_id
            );
        $startAt = match(true) {
            isset($data['start_at']) => $data['start_at'],
            default => ($this->start_at ?? now()->parse($this->start_datetime))
        };
        if (
            isset($this->slot_id) &&
            ($data['game_feature_statuses']['has_timers'] ?? $game->hasFeature('has_timers')) &&
            $startAt->isToday()
        ) {
            $timerEntries = $this->relationLoaded('timerEntries') ? $this->timerEntries : Cache::remember('reservationSlot:' . $this->id . ':timerEntries', 2, function () {
                return $this->timerEntries->sortByDesc('id');
            });
            if (count($timerEntries) && count($timerEntries->where('stopped', true)) === 0) {
                $result = true;
            }
        }
        return $result;
    }

	protected function timerEnabled($data = []): Attribute
	{
		return Attribute::make(get: fn() => $this->getTimerEnabled());
	}

	/*
	 * Timezone handle
	 *
	 */

	public function timerEntries(): HasMany
	{
		return $this->hasMany(ReservationSlotTimerEntry::class)->orderByDesc('id');
	}

	public function areTimersAvailable(
		Club|null $club = null,
		Slot|null $slot = null,
		Reservation|null $preloadedReservation = null
	): bool {
		$reservation = $preloadedReservation ?? $this->reservation;
		$hasGameTimersFeature = Cache::remember(
			'game:' . $reservation->game_id . ':has_timers_feature',
			5,
			function () use ($reservation) {
				return Game::getCached()
					->find($reservation->game_id)
					->hasFeature('has_timers');
			}
		);
		if (!$hasGameTimersFeature) {
			return false;
		}
		if (request()->routeIs(['widget.*', 'customer.*', 'calendar.*'])) {
			$slot = Slot::getSlot($this->slot_id);
			$pricelist = Pricelist::getPricelist($slot->pricelist_id);
			$clubId = $pricelist->club_id;
		} else {
			$clubId = clubId() ?? Pricelist::getPricelist($this->slot->pricelist_id)->club_id;
		}
		return Cache::remember('club:' . $clubId . ':timers_status', 5, function () use ($clubId) {
			return Setting::getClubGameSetting($clubId, 'timers_status')['value'];
		});
	}

	public function isUserAllowedToManipulateTimers($user): bool
	{
		return $this->getClub()->id === $user->club_id;
	}

	public function startTimer(): bool
	{
		if (!club()->isDatetimeRangeWithinOpeningHours(now(), now()->addMinutes())) {
			return false;
		}
		if (!$this->isUserAllowedToManipulateTimers(auth()?->user())) {
			return false;
		}
		if (!$this->areTimersAvailable()) {
			return false;
		}
		if ($this->start_at->gt(now()->addMinutes(30))) {
			return false;
		}
		if ($this->end_at->lt(now()->subMinutes(30))) {
			return false;
		}
		// can't start timer if reservation slot is cancelled
		if ($this->canceled_at) {
			return false;
		}
		// can start timer only if reservation slot is confirmed
		if (in_array($this->status, [ReservationSlotStatus::Confirmed, ReservationSlotStatus::Expired])) {
			return false;
		}
		// can't start timer if it has already stopped
		$query = $this->timerEntries()->stopped(true);
		$result = Cache::remember(
			'md5' . getMd5FromQuery($query) . ':query_exists_result',
			5,
			static function () use ($query) {
				return $query->exists();
			}
		);
		if ($result) {
			return false;
		}
		// if there is not paused time entry, we are not able to start timer
		$result = $this->timerEntries()
			->paused(false)
			->exists();
		if ($result) {
			return false;
		}
		if (!$this->timerEntries()->exists()) {
			$this->update([
				'start_at' => now()->seconds(0),
			]);
		}

		$timeEntriesWithEndAtNow = fn() => $this->timerEntries()->where(
			'end_at',
			DB::raw(
				"'" .
					Timezone::convertFromLocal(
						now()
							->addMinute()
							->seconds(0)
					) .
					"'"
			)
		);

		if ($timeEntriesWithEndAtNow()->count()) {
			$timeEntriesWithEndAtNow()->update([
				'end_at' => null,
			]);
		} else {
			$this->timerEntries()->create([
				'start_at' => now()->seconds(0),
				'end_at' => null,
			]);
		}

        SlotHasBulb::clearReservationBulbActions($this->reservation);
        if (
            (Setting::getClubGameSetting(
                clubId(),
                'bulb_status',
                $this->reservation->game_id
            )['value'] ??
                false) ===
            true
        ) {
            SlotHasBulb::prepareStartReservationSlotBulbAction(
                $this,
                ['start_at' => now()],
                $this->reservation
            );
        }

		event(new CalendarDataChanged(club()));

		return true;
	}

	public function pauseTimer(): bool
	{
		if (!$this->isUserAllowedToManipulateTimers(auth()?->user())) {
			return false;
		}
		if (!$this->areTimersAvailable()) {
			return false;
		}
		$this->timerEntries()
			->paused(false)
			->update([
				'end_at' =>
					now()->seconds() === 0
						? now('UTC')
						: now('UTC')
							->addMinute()
							->seconds(0),
			]);
		return true;
	}

	/**
	 * @throws JsonException
	 */
	public function stopTimer(bool $stoppedBySystem = false): bool
	{
		if (!$stoppedBySystem && !$this->isUserAllowedToManipulateTimers(auth()?->user())) {
			return false;
		}
		if (!$this->areTimersAvailable()) {
			return false;
		}

		$lastTimeEntry = Cache::remember('reservationSlot:' . $this->id . ':timerEntries', 2, function () {
			return $this->timerEntries()
				->orderByDesc('id')
				->get();
		})->first();
		if (empty($lastTimeEntry)) {
			return false;
		}

		if (
			$this->start_at->format('Y-m-d H:i') ===
			now('UTC')
				->seconds(0)
				->format('Y-m-d H:i')
		) {
			$this->end_at = $this->end_at->addMinute();
		} else {
			$this->end_at =
				now()->seconds() === 0
					? now()
					: now()
						->seconds(0)
						->addMinute();
		}
        $this->status = ReservationSlotStatus::Confirmed;
		$this->save();
		$lastTimeEntry->update([
			'end_at' => $lastTimeEntry->end_at ?? $this->end_at,
			'stopped' => true,
		]);
		$priceArray = $this->calculatePrice($this->reservation->customer_id, ['loadTimers' => true]);
		$this->update([
			'price' => $priceArray['basePrice'],
			'final_price' => $priceArray['finalPrice'],
		]);
		event(new CalendarDataChanged(club()));
		return true;
	}

	public function getClub(): Club|null
	{
		$slot = Slot::getSlot($this->slot_id);
		$pricelist = Pricelist::getPricelist($slot->pricelist_id);
		return Club::getClub($pricelist->club_id);
	}

	protected function startAt(): Attribute
	{
		return Timezone::getClubLocalizedDatetimeAttribute();
	}

    public function getEndAt($data)
    {
        $value = $this->getTimerEnabled($data) ? now('UTC') : now('UTC')->parse($this->getRawOriginal('end_at'));
        if (
            auth()->user() &&
            auth()
                ->user()
                ->isType(['manager', 'employee'])
        ) {
            return Timezone::convertToLocal($value);
        }

        return is_string($value) ? now()->parse($value) : $value;
    }

	protected function endAt($data = []): Attribute
	{
		return Attribute::make(
			get: function (DateTime|string|null $value) use($data) {
				return $this->getEndAt($data);
			},
			set: static function (DateTime|string|null $value) {
				if (
					auth()->user() &&
					auth()
						->user()
						->isType(['manager', 'employee'])
				) {
					return Timezone::convertFromLocal($value);
				}

				return is_string($value) ? now()->parse($value) : $value;
			}
		);
	}

	public function flushCache(): void
	{
		Cache::forget('reservationSlot:' . $this->id . ':timerEntries');
		Cache::forget('reservationSlot:' . $this->id . ':timer_status');
	}
}
