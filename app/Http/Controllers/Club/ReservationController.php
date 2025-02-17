<?php

namespace App\Http\Controllers\Club;

use App\Custom\Timezone;
use App\Enums\AnnouncementType;
use App\Enums\BulbReason;
use App\Enums\BulbStatus;
use App\Enums\ReservationSlotCancelationType;
use App\Enums\ReservationSlotStatus;
use App\Events\CalendarDataChanged;
use App\Exports\ExportManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterReservationsRequest;
use App\Http\Requests\Club\AccessReservationRequest;
use App\Http\Requests\Club\CancelReservationRequest;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\SlotResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\UserResource;
use App\Models\Club;
use App\Models\Customer;
use App\Models\DataChange;
use App\Models\Feature;
use App\Models\Features\BookSingularSlotByCapacity;
use App\Models\Features\PersonAsSlot;
use App\Models\Features\SlotHasBulb;
use App\Models\Game;
use App\Models\PaymentMethod;
use App\Models\Pricelist;
use App\Models\Reservation;
use App\Models\ReservationNumber;
use App\Models\ReservationSlot;
use App\Models\Setting;
use App\Models\Slot;
use App\Models\TablePreference;
use App\Models\User;
use App\Notifications\Customer\ReservationStoredNotification;
use App\Notifications\Customer\ReservationUpdateNotification;
use App\Traits\Paginable;
use Closure;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReservationController extends Controller
{
	//use Paginable;

	private array $reservationsTableData;

	public function __construct()
	{
		// make sure that we have the correct data in the query
		$this->middleware(function (Request $request, Closure $next) {
			if (!request()?->has('filters') || !isset(request()?->get('filters')['reservations']['game'])) {
				return redirect()->route('club.reservations.index', [
					'filters' => [
						'reservations' => [
							'startRange' => [
								'from' => now()
									->startOfMonth()
									->format('Y-m-d'),
								'to' => now()
									->endOfMonth()
									->format('Y-m-d'),
							],
							'game' => club()
								->games()
								->first()->id,
						],
					],
				]);
			}
			$this->reservationsTableData = Reservation::tableData(
				gameId: request()?->get('filters')['reservations']['game']
			);

			return $next($request);
		})->only('index', 'export');

		$this->middleware(function (Request $request, Closure $next) {
			$filters = request()?->get('filters', []);
			$gameFilter = data_get($filters, 'reservations.game');

			$startRangeFrom = data_get($filters, 'reservations.startRange.from');
			$startRangeTo = data_get($filters, 'reservations.startRange.to');
			if (
				empty($filters) ||
				!$gameFilter ||
				!$startRangeFrom ||
				!$startRangeTo ||
				$startRangeFrom !== $startRangeTo ||
				(Game::getCached()
					->where('id', $request->get('filters')['reservations']['game'])
					->first()->id ??
					0) !==
					$request->route('game')->id
			) {
				$date = $request->get('date', null);
				if ($date === null) {
					$date = now();
					$previousDayOpeningHours = club()->getOpeningHoursForDate(now()->subDay());
					if ($previousDayOpeningHours['club_end'] < $previousDayOpeningHours['club_start']) {
						if (now()->format('H:i:s') < $previousDayOpeningHours['club_end']) {
							$date = now()->subDay();
						}
					}
					$date = $date->format('Y-m-d');
				}
				return redirect()->route('club.games.reservations.calendar', [
					'game' => $request->route('game')->name,
					'filters' => [
						'reservations' => [
							'game' => $request->route('game')->id,
							'startRange' => [
								'from' => $date,
								'to' => $date,
							],
						],
						'calendar_reservations' => [
							'game' => $request->route('game')->id,
							'startRange' => [
								'from' => $date,
								'to' => $date,
							],
						],
					],
				]);
			}
			$this->reservationsTableData = Reservation::tableData(gameId: $request->route('game')->id);

			return $next($request);
		})->only('calendar');

		$this->middleware(function (Request $request, Closure $next) {
			$this->reservationsTableData = Reservation::tableData(gameId: $request->route('game')->id);

			return $next($request);
		})->only('search');
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function index(): RedirectResponse|Response
	{
		$tableName = $this->reservationsTableData['name'];
		$reservations = Reservation::getReservations(
			clubId: clubId(),
			paginated: true,
			itemsPerPage: request()->all()['perPage'][$tableName] ?? 10,
			tablePreference: $this->reservationsTableData['preference'],
			tableName: $tableName,
			paginationTableName: $tableName,
		);

		return Inertia::render('Club/Reservations/Index', [
			'availableTags' => TagResource::collection(
				club()
					->tags()
					->get()
			),
			'club' => club()->getCalendarResource(),
			'game' => new GameResource(
				Game::getCached()
					->where('id', request()->get('filters')['reservations']['game'])
					->first()
			),
			'reservations' => $reservations,
			'reservationTableHeadings' => $this->reservationsTableData['headings'],
		]);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function export(
		FilterReservationsRequest $request,
		bool $canceled = null
	): \Illuminate\Http\Response|BinaryFileResponse {
		$reservations = Reservation::getReservations(
			clubId: clubId(),
			paginated: false,
			canceled: $canceled,
			tablePreference: $this->reservationsTableData['preference'],
			tableName: $this->reservationsTableData['name']
		)->map(function ($reservation) {
			if ( isset( $reservation['visible_number'] ) ) $reservation['number'] = $reservation['visible_number'];
			return Reservation::prepareOutputForExport($reservation);
		});

		return ExportManager::export(
			TablePreference::getEnabledColumns(
				$this->reservationsTableData['preference'],
				Reservation::$exportFieldExclusions,
				Reservation::$exportFieldInclusions
			),
			$this->reservationsTableData['headings'],
			$reservations,
			$request->get('extension')
		);
	}

	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 * @throws Exception
	 */
	public function calendar(Request $request, Game $game): Response | int
	{
		$filters = request()?->get('filters', []);
		$date = data_get($filters, 'reservations.startRange.from');

		$allReservations = Reservation::getReservations(
			clubId: clubId(),
			tablePreference: $this->reservationsTableData['preference'],
			tableName: 'reservations',
			paginationTableName: 'reservations',
            allColumns: true,
		)->toArray();

		$calendarReservations = array_values(
			array_filter($allReservations, function ($item) {
				return $item['cancelation_status'] === false;
			})
		);

		$notCanceledReservations = array_filter($allReservations, function ($item) {
			return $item['cancelation_status'] === false;
		});

		$canceledReservationsArray = array_values(
			array_filter($allReservations, function ($item) {
				return $item['cancelation_status'] === true;
			})
		);

		// prepare table for cancelled reservations from collection containing all reservations
		$pageName = 'canceledReservations';
		$currentPage = request()->input($pageName) ?? 1;
		$canceledReservationsPerPage = $request->all()['perPage'][$pageName] ?? 10;
		$canceledReservations = new LengthAwarePaginator(
			(new Collection($canceledReservationsArray))->forPage($currentPage, $canceledReservationsPerPage),
			count($canceledReservationsArray),
			$canceledReservationsPerPage,
			$currentPage,
			[
				'path' => LengthAwarePaginator::resolveCurrentPath(),
				'pageName' => $pageName,
			]
		);

		// prepare table for not cancelled reservations from collection containing all reservations
		$pageName = 'reservations';
		$currentPage = request()->input($pageName) ?? 1;
		$reservationsPerPage = $request->all()['perPage'][$pageName] ?? 10;
		$reservations = new LengthAwarePaginator(
			(new Collection($notCanceledReservations))->forPage($currentPage, $reservationsPerPage),
			count($notCanceledReservations),
			$reservationsPerPage,
			$currentPage,
			[
				'path' => LengthAwarePaginator::resolveCurrentPath(),
				'pageName' => $pageName,
			]
		);

		$announcement = club()
			->getAnnouncements()
			->where('type', AnnouncementType::Panel)
			->where('start_at', '<=', $date)
			->where('end_at', '>=', $date)
			->first();

		$reservationSlotsCollection = ReservationSlot::select('reservation_id', 'slot_id')
			->whereNull('canceled_at')
			->whereIn('reservation_id', array_column($calendarReservations, 'reservation_id'))
			->get();
		$slotsNames =
			club()->getSlots()[$game->id] ?? false
				? club()
					->getSlots()
					[$game->id]?->pluck('name', 'id')
				: [];
		$calendarReservations = (new Collection($calendarReservations))->map(function (
			$reservationEntity
		) use ($reservationSlotsCollection, $slotsNames, $game) {
			$reservationSlots = $reservationSlotsCollection
				->where('reservation_id', $reservationEntity['reservation_id'])
				->whereNull('cancelation_type');

			$displayName = match (true) {
				isset($reservationEntity['custom_display_name']) &&
					$reservationEntity['custom_display_name'] !== null
					=> $reservationEntity['custom_display_name'],
				default => $reservationEntity['display_name'] ?? '',
			};
			if (count($reservationSlots) > 1) {
				$reservationSlotsNames = array_map(static function ($slotId) use ($slotsNames) {
					return $slotsNames[$slotId] ?? null;
				}, $reservationSlots->pluck('slot_id')->toArray());
				$displayName .= ' (' . implode(',', $reservationSlotsNames) . ')';
			}

			return array_merge($reservationEntity, [
				'display_name' => $displayName,
			]);
		});

		$customReservationCalendarView =
			$game->getFeaturesByType('has_custom_views')->first()->data['custom_views'][
				'reservations.calendar'
			] ?? null;

		// slots are unnecessary if has_custom_views feature is present
		$slots = match ($customReservationCalendarView) {
			null => club()
				->slots()
				->when($game->hasFeature('slot_has_parent'), function ($query) {
					$query->whereNotNull('slot_id');
				})
				->where('active', 1)
				->when($game, function ($query) use ($game) {
					$query->whereIn('pricelist_id', club()->pricelists()->where('game_id', $game->id)->pluck('id')->toArray());
				})
				->when($game->hasFeature('slot_has_bulb'), function ($query) {
					$query->with([
						'bulbActions' => function ($query) {
							$query->onlyTrashed()->orderByDesc('run_at');
						},
					]);
				})
				->with([
					'features' => function ($query) {
						$query->where('type', 'slot_has_bulb');
					},
				])
				->get(),
			default => new Collection(),
		};

		$slots = $slots->map(function ($slot) use ($game) {
			$slot->bulb_status = $game->hasFeature('slot_has_bulb') ? $slot?->bulbStatus?->value : null;
            return $slot;
		});

		if ($game->hasFeature('slot_has_bulb')) {
			//Update bulb status when setting is true
			$bulbAdapter =
				club()
					->settings()
					->where('key', 'bulb_status')
					->first()->bulbAdapter ?? false;
			if ($bulbAdapter && $bulbAdapter->synchronize) {
				$slots->each(function ($slot, $key) use ($slots, $bulbAdapter) {
					$diff = $slot->bulbActions
						->where('reason', BulbReason::AUTOSYNC)
						->first()
						?->run_at->diffInMinutes(now('UTC'));
					if ($slot->bulbStatus !== null && ($diff > 300 || $diff === null)) {
						$bulbData = json_decode($slot->features('slot_has_bulb')->first()->pivot->data, true);
						if (isset($bulbData['name'])) {
							$slot->changeBulbStatus(
								$bulbAdapter->isBulbOn($bulbData['name']) ? BulbStatus::ON : BulbStatus::OFF,
								BulbReason::AUTOSYNC,
								null,
								null,
								true
							);
						}
					}
				});
			}
		}

        $clubGamePricelistIds = Pricelist::where('club_id', clubId())->where('game_id', $game->id)->pluck('id')->toArray();


		// prepare parent slot computed data if game has one of mentioned below custom views
		$parentSlotsFillData = match ($customReservationCalendarView) {
			'Club/Reservations/Custom/UnnumberedTables/Calendar' => club()
				->slots()
				->when(
					$game,
					fn($query) => $query->whereHas(
						'pricelist',
						fn($query) => $query->where('game_id', $game->id)
					)
				)
				->whereNull('slot_id')
				->get()
				->map(function ($slot) use ($date) {
					return [
						'id' => $slot->id,
						'name' => $slot->name,
						'capacity' => PersonAsSlot::getParentSlotCapacity($slot, $date),
						'occupied' => $slot
							->childrenSlotsReservationSlots()
							->whereNull('cancelation_type')
							->where('occupied_status', true)
							->active()
							->where(
								'start_at',
								'>=',
								now('UTC')
							)
							->where(
								'end_at',
								'<=',
                                now('UTC')->addMinutes(2)
							)
							->count(),
					];
				})
				->toArray(),
			'Club/Reservations/Custom/NumberedTables/Calendar' => club()
				->slots()
				->when(
					$game,
					fn($query) => $query->whereIn('pricelist_id', $clubGamePricelistIds)
				)
				->whereNull('slot_id')
				->with([
					'childrenSlots',
					'childrenSlots.features',
				])
				->get()
				->map(function ($parentSlot) use ($date, $game) {
					$duration = 60;
					$startAt = now()->format('Y-m-d H:i:s');
					if (
						$game->hasFeature('full_day_reservations') &&
						Setting::getClubGameSetting(clubId(), 'fixed_reservation_duration_status', $game->id)[
							'value'
						] === true &&
						Setting::getClubGameSetting(clubId(), 'fixed_reservation_duration_value', $game->id)[
							'value'
						] === 24
					) {
						$duration = 1440;
						$openingHours = club()->getOpeningHoursForDate($date);
						$startAt = $date . ' ' . $openingHours['club_start'];
					}
					$availableChildren = Slot::getAvailable([
						'start_at' => $startAt,
						'duration' => $duration,
						'parent_slot_id' => $parentSlot->id,
						'vacant' => true,
						'active' => true,
						'club_id' => clubId(),
						'game_id' => $game->id,
					]);
					$childrenData = $parentSlot->childrenSlots
						->map(function ($childSlot) {
							$childSlot->capacity = json_decode(
								$childSlot->features->where('type', 'book_singular_slot_by_capacity')->first()
									->pivot->data,
								true,
								512,
								JSON_THROW_ON_ERROR
							)['capacity'];

							return $childSlot;
						})
						->reduce(
							function ($acc, $childSlot) use ($date, $game, $availableChildren) {
								$cap = $childSlot->capacity;
								$acc['totalCapacity'] += $cap;
								if (!isset($acc['availableCapacities'][$cap]['freeSlotsIds'])) {
									$acc['availableCapacities'][$cap]['freeSlotsIds'] = [];
								}
								$acc['availableCapacities'][$cap]['all_count'] =
									($acc['availableCapacities'][$cap]['all_count'] ?? 0) + 1;
								if ($availableChildren->where('id', $childSlot->id)->first()) {
									$acc['availableCapacities'][$cap]['freeSlots'][] = $childSlot->only(
										'id',
										'name'
									);
								}

								return $acc;
							},
							[
								'availableCapacities' => [],
								'totalCapacity' => 0,
							]
						);

					return [
						'id' => $parentSlot->id,
						'name' => $parentSlot->name,
						'capacities' => $childrenData['availableCapacities'],
						'totalCapacity' => $childrenData['totalCapacity'],
					];
				}),
			default => [],
		};

		$openingHours = club()->getOpeningHoursForDate($date);

		$calendarHourRangeMax =
			$openingHours['club_end'] < $openingHours['club_start']
				? (int) substr($openingHours['club_end'], 0, 2) + 24 . ':00:00'
				: $openingHours['club_end'];

		$calendarHourRange = [
			'from' => $openingHours['club_start'],
			'to' => $calendarHourRangeMax === '23:59:00' ? '24:00:00' : $calendarHourRangeMax,
		];


		return Inertia::render('Club/Reservations/Calendar', [
			'club' => club()->getCalendarResource($game),
			'dateLocale' => now()
				->parse($date)
				->translatedFormat('l, j F Y'),
			'announcement' => $announcement ? new AnnouncementResource($announcement) : null,
			'slots' => SlotResource::collection($slots),
			'parentSlotsFillData' => $parentSlotsFillData,
			'game' => new GameResource( Game::getCached()
                ->where('id', $game->id)
                ->first()
            ),
			'calendarReservations' => array_values($calendarReservations->toArray()),
			'reservations' => $reservations,
			'canceledReservations' => $canceledReservations,
			'reservationTableHeadings' => $this->reservationsTableData['headings'],
			'openingHours' => $openingHours,
			'calendarHourRange' => $calendarHourRange,
			'preview_mode' => club()->preview_mode,
		]);
	}

	public function changeBulb(Request $request, Game $game, Slot $slot)
	{
		$bulbStatus = !!$request->input('status') ? BulbStatus::ON : BulbStatus::OFF;

		if (
			$slot
				->features()
				->where('type', 'slot_has_bulb')
				->exists()
		) {
			$slot->changeBulbStatus($bulbStatus, BulbReason::MANUAL);
		}

		return response()->json([
			'status' => $bulbStatus,
			'reason' => BulbReason::MANUAL,
		]);
	}

	public function search(Request $request, Game $game): JsonResponse
	{
		$reservationSearchResults = [
			'confirmed' => [],
			'canceled' => [],
		];

		if ($request->has('searcher') && isset($request->get('searcher')['reservations'])) {
			$reservationSearchResults['confirmed'] = Reservation::getReservations(
				clubId: clubId(),
				resultsLimit: 4,
				canceled: false,
				tablePreference: $this->reservationsTableData['preference'],
				tableName: $this->reservationsTableData['name'],
				paginationTableName: 'reservations'
			);
			$reservationSearchResults['canceled'] = Reservation::getReservations(
				clubId: clubId(),
				resultsLimit: 4,
				canceled: true,
				tablePreference: $this->reservationsTableData['preference'],
				tableName: $this->reservationsTableData['name'],
				paginationTableName: 'reservations'
			);
		}

		return response()->json($reservationSearchResults);
	}

	/**
	 * @throws JsonException
	 */
	public function store(Request $request, Game $game): JsonResponse
	{
		$reservationData = $request->all();
		$reservationData['game_id'] = $game->id;
		$reservationData['club_id'] = clubId();
		return Reservation::store($reservationData);
	}

	public function cancel(CancelReservationRequest $request): RedirectResponse
	{
		$reasonContent = $request->get('reasonContent', '');
		if ($request->get('reasonType') > 0) {
			$reasonContent = __('reservation.cancelation-types.' . $request->get('reasonType'));
		}

		$reservationNumber = ReservationNumber::find($request->get('reservationNumber'));
		$reservationNumber->cancel(
			$request->get('cancelRelatedReservations'),
			ReservationSlotCancelationType::Club,
			$reasonContent,
			$request->get('reasonType') !== 1
		);

		return redirect()->back();
	}

	/**
	 * @throws JsonException
	 */
	public function show(AccessReservationRequest $request, ReservationNumber $reservationNumber)
	{
		$club = club();
		$numerable = $reservationNumber->numerable;
        $reservation = $numerable->reservation;
        $game = Game::getCached()->find($reservation->game_id);
        $firstReservationSlot = $numerable->firstReservationSlot;
        $slot = Slot::getSlot($firstReservationSlot->slot_id);
        $pricelist = Pricelist::getPricelist($slot->pricelist_id);
        $preloadedCollections = [
            'reservation' => $reservation,
            'game' => $game,
            'slots' => collect([$slot]),
            'firstReservationSlot' => $firstReservationSlot,
            'pricelist' => $pricelist,
            'club' => $club
        ];
        $preloadedCollections['game_feature_statuses'] = [
            'slot_has_parent' => $game->hasFeature('slot_has_parent'),
            'has_timers' => $game->hasFeature('has_timers')
        ];
		$result = $numerable->prepareForOutput(true, $preloadedCollections);
		if (!$club || $club->id !== $pricelist->club_id) {
			$result = [
				'status' => $result->status,
			];
		}
		return response()->json($result);
	}

	/**
	 * @throws JsonException
	 */
	public function history(ReservationNumber $reservationNumber): bool|string
	{
		$fieldNames = array_merge(
			Reservation::getFieldLocaleNames(),
			ReservationSlot::getFieldLocaleNames($reservationNumber->numerable->game->id)
		);

		// load field nicenames assigned to game features
		foreach ($reservationNumber->numerable->game->features as $feature) {
			foreach ($feature->getReservationDataValidationNiceNames() as $key => $value) {
				$fieldNames[$key] = $value;
			}
		}

		$changes = $reservationNumber->numerable->changes
			->map(function ($a) {
				$a['created_at'] = $a['created_at'];
				return $a;
			})
			->toArray();
		if ($reservationNumber->numerable_type === ReservationSlot::class) {
			$changes = array_merge(
				$changes,
				$reservationNumber->numerable->reservation->changes
					->map(function ($a) {
						$a['created_at'] = Timezone::convertToLocal($a['created_at']);
						return $a;
					})
					->toArray()
			);
		} else {
			// In this case all ReservationModel instances are changed in the same way,
			// so one instance is enough
			$changes = array_merge(
				$changes,
				$reservationNumber->numerable->firstReservationSlot->changes->toArray()
			);
		}

		$reservationChangeBlocks = array_map(function ($changeEntry) use ($fieldNames) {
			$date = Timezone::convertToLocal($changeEntry['created_at'])->format('Y-m-d H:i:s');
			$entries = array_map(function ($key) use ($changeEntry, $fieldNames) {
				$user = $changeEntry['triggerer_id'] ? User::find($changeEntry['triggerer_id']) : null;
				$userResource = $user ? new UserResource($user) : null;
				return [
					'name' => $fieldNames[$key] ?? null,
					'old' => DataChange::getReservationValue($key, $changeEntry['old'][$key]),
					'new' => DataChange::getReservationValue($key, $changeEntry['new'][$key]),
					'triggerer' => $userResource,
				];
			}, array_keys($changeEntry['old']));

			return [
				'date' => $date,
				'entries' => $entries,
			];
		}, $changes);

		// merge entries from the same datetime (changes were made at the same request)
		$merged = [];
		foreach ($reservationChangeBlocks as $entry) {
			$date = Timezone::convertToLocal($entry['date'])->format('Y-m-d H:i:s');
			if (!isset($merged[$date])) {
				$merged[$date] = $entry;
			} else {
				$merged[$date]['entries'] = array_merge($merged[$date]['entries'], $entry['entries']);
			}
		}

		$reservationChangeBlocks = array_values($merged);
		usort($reservationChangeBlocks, function ($item1, $item2) {
			return strtotime($item2['date']) <=> strtotime($item1['date']);
		});

		// Changing the value of a reservation sometimes takes place in several rounds.
		// Because of this, several changes may be recorded in the reservation history a second or two apart.
		// Below, we combine such entries into one.
		$result = [];
		$currentIndex = -1;
		foreach ($reservationChangeBlocks as $item) {
			if (
				$currentIndex == -1 ||
				abs(strtotime($item['date']) - strtotime($result[$currentIndex]['date'])) > 3
			) {
				$currentIndex++;
				$result[$currentIndex] = $item;
			} else {
				$result[$currentIndex]['entries'] = array_merge(
					$result[$currentIndex]['entries'],
					$item['entries']
				); // inaczej dodajemy wpisy do istniejącego elementu
			}
		}

		return json_encode($result, JSON_THROW_ON_ERROR);
	}

	public function featureUpdate(Request $request, ReservationNumber $reservationNumber): JsonResponse
	{
		$game = Game::getCached()->find($reservationNumber->numerable->reservation->game_id);
		$feature = Feature::where('code', $request->get('feature'))->first();

		if ($game->hasFeature($feature->code)) {
			$feature->updateReservationData($reservationNumber, [
				'features' => [$feature->id => $request->input('data', [])],
			]);
		}

		return response()->json([
			'success' => true,
		]);
	}

	/**
	 * @throws JsonException
	 */
	public function update(Request $request, ReservationNumber $reservationNumber): RedirectResponse
	{
        $numerables = $reservationNumber->numerable;
		$startDateString = $numerables->reservation->firstReservationSlot->start_at->format(
			'Y-m-d H:i'
		);
		$game = Game::getCached()->find($numerables->reservation->game_id);
		$oldPrice = $numerables->reservation->price;
		$data = $request->all();
		$data['game_id'] = $game->id;
		$data['club_id'] = clubId();
		$data['reservation_number_id'] = $reservationNumber->id;
		$gameFeatures = $game->features;
		foreach ($gameFeatures as $feature) {
			if (!$feature->executablePublicly) {
				continue;
			}
			$data = $feature?->prepareDataForAction($data) ?? $data;
		}
		$data['end_at'] = now()
			->parse($data['start_at'])
            ->addMinutes($data['duration'] ?? 60)
			->format('Y-m-d H:i:s');
        $data = BookSingularSlotByCapacity::prepareSlotVariables($data);


        // Adding $gameFeatures to function so it doesnt need to be querried inside function again.
		Reservation::processReservationInsertDataValidation($data, $reservationNumber, $gameFeatures);
		$data = $request->all();

		$startAt = Timezone::convertFromLocal($data['start_at']);
		$endAt = $startAt->clone()->addMinutes($data['duration'] ?? 60);


		$customer = null;
        // Hack around - if we have customer id in request we should use it to just query database, much faster than Resrevation::getCustomerFromData function
        if(!isset($data['customer']['id']) && $data['customer']['id'] !== null) {
            $customer = Customer::find($data['customer']['id']);
        } else {
            if ($request->get('anonymous_reservation') === false) {
                $customer = Reservation::getCustomerFromData($data);
            }
        }


		$customer?->syncTags($data['customer']['tags'] ?? []);

		// array contain information if reservation slot has been paid and which payment method was used
		$statusArray = json_decode($data['status'], true, 512, JSON_THROW_ON_ERROR);

		$reservationNumber->numerable->reservation->update([
			'show_club_note_on_calendar' => $data['show_club_note_on_calendar'] ?? false,
			'show_customer_note_on_calendar' => $data['show_customer_note_on_calendar'] ?? false,
		]);

		//Do not allow status and payment_method_id change if reservation is paid online
		if (
			$reservationNumber->numerable->reservation->paymentMethod->online &&
			$reservationNumber->numerable->reservation
				->reservationSlots()
				->where('status', ReservationSlotStatus::Confirmed)
				->exists()
		) {
			$data['status'] = ReservationSlotStatus::Confirmed->value;
			$data['custom_price'] = true;
			$paymentMethodId = $reservationNumber->numerable->reservation->payment_method_id;
		} else {
			if (isset($statusArray['status']) && in_array($statusArray['status'], [0, 1], true)) {
				$data['status'] = $statusArray['status'];
			}

            // Again check if payment method id is in request, if not use the one from statusArray
            // Instead of taking this id and query database to get same id??
            if(isset($statusArray['payment_method_id']) && $statusArray['payment_method_id'] !== null) {
                $paymentMethodId = $statusArray['payment_method_id'];
            }  else {
                $paymentMethodId = null;
            }

			if (empty($paymentMethodId)) {
				$paymentMethodId = PaymentMethod::where('online', false)->first()->id;
			}
		}

		$reservationUpdateArray = [
			'club_note' => $data['club_note'] ?? null,
			'customer_note' => $data['customer_note'] ?? null,
			'customer_id' => $customer?->id ?? null,
		];
		$reservationNumber->numerable->reservation->update($reservationUpdateArray);

		if ($reservationNumber->numerable_type === ReservationSlot::class) {
			$reservationNumber->numerable->reservation()->update([
				'payment_method_id' => $paymentMethodId,
			]);

			// If we move all reservations to another slot at the same time, they should be placed next to each other
			if (
				$request->get('apply_to_all_reservations') &&
				!$game->hasFeature('book_singular_slot_by_capacity')
			) {
				$reservationSlotIds = $reservationNumber->numerable->reservation
					->reservationSlots()
					->pluck('id')
					->toArray();
				if ($request->all()['slot_ids'][0] !== $reservationNumber->numerable->slot_id) {

                    //Clear array of empty features to reduce the number of executions in foreach loop later
                    $featureArray = array_filter($request->all()['features']) ?? [];

                    //We use new optimized version that takes into parameters few parameters that we already have in this function
					$slotsIds = self::optimizedGetVacantSlotsIds(
                        $gameFeatures,
                        $data['club_id'] ?? null,
						$game,
						$request->all()['start_at'],
						$request->all()['duration'] ?? 60,
						$request->all()['slot_ids'] ?? [0 => null],
						$request->all()['slot_id'] ?? null,
						count($reservationSlotIds),
						$featureArray,
						preserveOrder: true,
						excludeReservationSlotIds: $reservationSlotIds
					);
                    clock('updating reservation slots');
					foreach (
						$reservationNumber->numerable->reservation->reservationSlots
						as $reservationSlot
					) {
						$reservationSlot->update([
							'slot_id' => array_shift($slotsIds),
						]);
					}
                    clock('updating reservation slots ended');
				}
			} else {
                clock('updating reservation slots');
				$reservationNumber->numerable->update([
					'slot_id' => $data['slot_id'],
				]);
                clock('updating reservation slots ended');
            }

			$reservationSlotsToUpdate = match ($request->get('apply_to_all_reservations')) {
				true => $reservationNumber->numerable->reservation->reservationSlots,
				false => [$reservationNumber->numerable],
			};

			foreach ($reservationSlotsToUpdate as $reservationSlot) {
				if ($reservationSlot->getTimerEnabled(['game' => $game, 'start_at' => $startAt->clone()])) {
					$request->merge([
						'start_at' => $data['start_at']->format('Y-m-d H:i:s'),
						'duration' => $data['end_at']->diffInMinutes($data['start_at']),
					]);
				}
				$baseUpdateArray = [
					'start_at' => $data['start_at'],
					'end_at' => $data['end_at']
				];
				$reservationSlot->update(array_merge(ReservationSlot::fillWithData($data), $baseUpdateArray));
			}

			// each game feature can add its own logic to updating reservation
			foreach ($reservationNumber->numerable->game->features as $feature) {
				if (!$feature->executablePublicly) {
					continue;
				}
				foreach ($reservationSlotsToUpdate as $reservationSlot) {
					$feature->updateReservationData($reservationSlot->numberModel(), $data);
				}
			}
		} else {
			$prices = $reservationNumber->numerable->firstReservationSlot->calculatePrice();
			$reservationNumber->numerable()->update([
				'payment_method_id' => $paymentMethodId,
			]);
			$baseUpdateArray = [
				'start_at' => $startAt,
				'end_at' => $endAt,
				'price' => $prices['basePrice'],
				'final_price' => $prices['finalPrice'],
			];
			$reservationNumber->numerable
				->reservationSlots()
				->update(array_merge(ReservationSlot::fillWithData($data), $baseUpdateArray));
			foreach ($reservationNumber->numerable->game->features as $feature) {
				if (!$feature->executablePublicly) {
					continue;
				}
				$feature->updateReservationData($reservationNumber, $data);
			}
		}

		$request->merge($data);
        $reservation = $reservationNumber->numerable->reservation;
        $reservation->recalculateReservationSlotPrices();

		if (
			$reservationNumber->numerable_type === ReservationSlot::class &&
			($data['custom_price'] ?? false)
		) {
			$prices = $reservationNumber->numerable->firstReservationSlot->calculatePrice(
				$reservationNumber->numerable->reservation->customer_id,
				$data
			);
			$reservationNumber->numerable->firstReservationSlot->update([
				'price' => $prices['basePrice'],
				'final_price' => $prices['finalPrice'],
			]);
		}
        $reservation = Reservation::find($reservation->id);
        $reservation->update([
			'price' => $reservation->getPrice($reservation->reservationSlots),
		]);

		//sms notification
		$notifiable = Customer::getCustomer($reservationNumber->numerable->reservation->customer_id);
		if ($notifiable === null) {
			$notifiable = (new AnonymousNotifiable())
				->route(
					'mail',
					$reservationNumber->numerable->reservation->unregistered_customer_data['email']
				)
				->route(
					'phone',
					$reservationNumber->numerable->reservation->unregistered_customer_data['phone']
				)
				->route(
					'clubId',
					$reservationNumber->numerable->reservation->firstReservationSlot->slot->pricelist->club
						->id
				);
		}

		$newPrice = ReservationNumber::find($reservationNumber->id)->numerable->reservation->price;
		if (
			$startDateString !=
				now('UTC')
					->parse($data['start_at'])
					->format('Y-m-d H:i') ||
			$reservationNumber->numerable->reservation->firstReservationSlot->end_at->diffInMinutes(
				$reservationNumber->numerable->reservation->firstReservationSlot->start_at
			) !== $data['duration'] ||
			($oldPrice && $newPrice && $oldPrice !== $newPrice)
		) {
			$notifiable?->notify(
				new ReservationUpdateNotification(
					$reservationNumber->numerable->firstReservationSlot->numberModel()
				)
			);
		}

		return redirect()->back();
	}

    public static function optimizedGetVacantSlotsIds(
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
        $hasPersonAsSlotFeature = $game->hasFeature('person_as_slot');
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



	/**
	 * @throws JsonException
	 */
	public function calculatePrice(
		Request $request,
		$resultType = 'json',
		$withValidation = true
	): JsonResponse|array {
		$request->merge([
			'club_id' => clubId(),
		]);
		return Reservation::calculatePrice($request, $resultType, $withValidation);
	}

	public function startTimer(ReservationNumber $reservationNumber): JsonResponse
	{
		$reservationNumerable = $reservationNumber->numerable;

		if ($reservationNumber->numerable_type === ReservationSlot::class) {
			$reservationNumerable->startTimer();
			$reservationNumber->numerable->startTimer();
			$reservationNumerable->flushCache();
		}

		return response()->json($reservationNumerable->prepareForOutput(true));
	}

	/**
	 * @throws JsonException
	 */
	public function pauseTimer(ReservationNumber $reservationNumber): JsonResponse
	{
		$reservationNumerable = $reservationNumber->numerable;

		if ($reservationNumber->numerable_type === ReservationSlot::class) {
			$reservationNumerable->pauseTimer();
			$reservationNumerable->flushCache();
		}

		return response()->json($reservationNumerable->prepareForOutput(true));
	}

	public function stopTimer(ReservationNumber $reservationNumber): JsonResponse
	{
		$reservationNumerable = $reservationNumber->numerable;

		if ($reservationNumber->numerable_type === ReservationSlot::class) {
			$reservationNumerable->stopTimer();
			$reservationNumber->numerable->stopTimer();
			SlotHasBulb::clearReservationBulbActions($reservationNumber->numerable->reservation);
			if (
				(Setting::getClubGameSetting(
					clubId(),
					'bulb_status',
					$reservationNumber->numerable->reservation->game_id
				)['value'] ??
					false) ===
				true
			) {
				SlotHasBulb::prepareEndReservationSlotBulbAction(
					$reservationNumber->numerable,
					['start_at' => now(), 'duration' => 0],
					$reservationNumber->numerable->reservation
				);
			}
			$reservationNumerable->flushCache();
		}

		return response()->json($reservationNumerable->prepareForOutput(true));
	}

	/**
	 * The method changes the occupancy status of the slot occupied by the reservation with the given number.
	 * If the occupancy is set to 0, then another reservation can be made for the same slot.
	 *
	 * @param ReservationNumber $reservationNumber
	 *
	 * @return RedirectResponse
	 */
	public function toggleOccupiedStatus(ReservationNumber $reservationNumber): RedirectResponse
	{
		if ($reservationNumber->numerable_type === Reservation::class) {
			$firstReservationSlot = $reservationNumber->numerable->firstReservationSlot;
			$reservationNumber->numerable->reservationSlots()->update([
				'occupied_status' => !$firstReservationSlot->occupied_status,
			]);
		} else {
			$numerable = $reservationNumber->numerable;
			$numerable->update([
				'occupied_status' => !$numerable->occupied_status,
			]);
		}
		return redirect()->back();
	}

	/**
	 * Paginate Eloquent collection.
	 *
	 * @param Collection $results
	 * @param int $showPerPage
	 *
	 * @return LengthAwarePaginator
	 *
	 * @throws BindingResolutionException
	 */
	public static function paginate(
		Collection $results,
		int $showPerPage,
		string $pageName
	): LengthAwarePaginator {
		$pageNumber = Paginator::resolveCurrentPage('page');

		$totalPageNumber = $results->count();

		return self::paginator(
			$results->forPage($pageNumber, $showPerPage),
			$totalPageNumber,
			$showPerPage,
			$pageNumber,
			[
				'path' => Paginator::resolveCurrentPath(),
				'pageName' => $pageName,
			]
		);
	}

	/**
	 * Create a new length-aware paginator instance.
	 *
	 * @param Collection $items
	 * @param int $total
	 * @param int $perPage
	 * @param int $currentPage
	 * @param array $options
	 *
	 * @return LengthAwarePaginator
	 *
	 * @throws BindingResolutionException
	 */
	protected static function paginator(
		Collection $items,
		int $total,
		int $perPage,
		int $currentPage,
		array $options
	): LengthAwarePaginator {
		return Container::getInstance()->makeWith(
			LengthAwarePaginator::class,
			compact('items', 'total', 'perPage', 'currentPage', 'options')
		);
	}
}
