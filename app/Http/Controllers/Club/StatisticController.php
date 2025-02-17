<?php

namespace App\Http\Controllers\Club;

use App\Custom\Timezone;
use App\Enums\ReservationSlotStatus;
use App\Exports\MainStatisticsExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\DiscountCodeResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\SetResource;
use App\Http\Resources\SpecialOfferResource;
use App\Models\Game;
use App\Models\ReservationSlot;
use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StatisticController extends Controller
{
	private string|null $from;
	private string|null $to;
	private string|null $paymentType;
	private array|null $filters;

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function __construct()
	{
		// here we make sure that the query array has valid values
		$this->filters = request()?->get('filters', []);
		$this->from = data_get($this->filters, 'startRange.from');
		$this->to = data_get($this->filters, 'startRange.to');
		$this->status = data_get($this->filters, 'status');
		$this->paymentType = data_get($this->filters, 'paymentType');
		$this->fromTime = data_get($this->filters, 'timeRange.from');
		$this->toTime =
			data_get($this->filters, 'timeRange.to') === '24:00'
				? '23:59'
				: data_get($this->filters, 'timeRange.to');

		// promotions are special offers and discount codes collected in one array
		$this->promotion = data_get($this->filters, 'promotion');

		$this->middleware(function (Request $request, Closure $next) {
			if (
				empty($this->filters) ||
				!$this->from ||
				!$this->to ||
				!$this->paymentType ||
				!$this->status ||
				!$request->has('chartType')
			) {
				return redirect()->route($request->route()->action['as'], [
					'game' => $request->route('game'),
					'filters' => [
						'startRange' => [
							'from' => now()
								->startOfMonth()
								->format('Y-m-d'),
							'to' => now()
								->endOfMonth()
								->format('Y-m-d'),
						],
						'paymentType' => '3',
						'status' => '3',
						'promotion' => 'all',
					],
					'chartType' => 'turnover',
				]);
			}

			return $next($request);
		})->only(['main']);

		$this->middleware(function (Request $request, Closure $next) {
			if (empty($this->filters) || !$this->from || !$this->to || !$this->paymentType) {
				return redirect()->route($request->route()->action['as'], [
					'game' => $request->route('game'),
					'filters' => [
						'startRange' => [
							'from' => now()
								->startOfMonth()
								->format('Y-m-d'),
							'to' => now()
								->endOfMonth()
								->format('Y-m-d'),
						],
						'paymentType' => '3',
					],
				]);
			}

			return $next($request);
		})->only(['sets']);

		$this->middleware(function (Request $request, Closure $next) {
			if (
				empty($this->filters) ||
				!$this->from ||
				!$this->status ||
				!$this->to ||
				!$this->paymentType ||
				$this->fromTime === null ||
				!$this->toTime
			) {
				return redirect()->route($request->route()->action['as'], [
					'game' => $request->route('game'),
					'filters' => [
						'startRange' => [
							'from' => now()
								->startOfMonth()
								->format('Y-m-d'),
							'to' => now()
								->endOfMonth()
								->format('Y-m-d'),
						],
						'timeRange' => [
							'from' => '00:00',
							'to' => '24:00',
						],
						'status' => '3',
						'paymentType' => '3',
					],
				]);
			}

			return $next($request);
		})->only(['weekly']);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 * @throws Exception
	 */
	public function main(Game $game): Response|RedirectResponse
	{
		$game->load('features');
		$statistics = $this->getMainStatistics($game);
		$translations = [
			'statistics.turnover' => __('statistics.turnover'),
			'statistics.tooltip-turnover' => __('statistics.tooltip-turnover'),
			'statistics.tooltip-count' => __('statistics.tooltip-count'),
			'statistics.tooltip-hours' => __('statistics.tooltip-hours'),
			'statistics.tooltip-offline-turnover' => __('statistics.tooltip-offline-turnover'),
			'statistics.tooltip-online-turnover' => __('statistics.tooltip-online-turnover'),
			'statistics.tooltip-offline-hours' => __('statistics.tooltip-offline-hours'),
			'statistics.tooltip-online-hours' => __('statistics.tooltip-online-hours'),
			'statistics.tooltip-offline-count' => __('statistics.tooltip-offline-count'),
			'statistics.tooltip-online-count' => __('statistics.tooltip-online-count'),
		];

		return Inertia::render('Club/Statistics/Main', [
			'game' => new GameResource($game),
			'statistics' => $statistics,
			'translations' => $translations,
			'discountCodes' => DiscountCodeResource::collection(club()->discountCodes->loadCount('reservations')),
			'specialOffers' => SpecialOfferResource::collection(club()->specialOffers),
		]);
	}

	public function getMainStatistics(Game $game): array
	{
		$statistics = [
			'reservationsTurnoverPaid' => [
				'online' => $this->getClubReservationsTurnover($game, $this->from, $this->to, true, '1'),
				'offline' => $this->getClubReservationsTurnover($game, $this->from, $this->to, false, '1'),
			],
			'reservationsTurnoverUnpaid' => [
				'online' => $this->getClubReservationsTurnover($game, $this->from, $this->to, true, '0'),
				'offline' => $this->getClubReservationsTurnover($game, $this->from, $this->to, false, '0'),
			],
			'reservationsHoursPaid' => [
				'online' => $this->getClubReservationHoursCount($game, $this->from, $this->to, true, '1'),
				'offline' => $this->getClubReservationHoursCount($game, $this->from, $this->to, false, '1'),
			],
			'reservationsHoursUnpaid' => [
				'online' => $this->getClubReservationHoursCount($game, $this->from, $this->to, true, '0'),
				'offline' => $this->getClubReservationHoursCount($game, $this->from, $this->to, false, '0'),
			],
			'reservationsCountPaid' => [
				'online' => $this->getClubReservationsCount($game, $this->from, $this->to, true, '1'),
				'offline' => $this->getClubReservationsCount($game, $this->from, $this->to, false, '1'),
			],
			'reservationsCountUnpaid' => [
				'online' => $this->getClubReservationsCount($game, $this->from, $this->to, true, '0'),
				'offline' => $this->getClubReservationsCount($game, $this->from, $this->to, false, '0'),
			],
			'allCustomersCount' => $this->getClubAllCustomersCount(
				$game,
				$this->from,
				$this->to,
				match ($this->paymentType) {
					'1' => true,
					'2' => false,
					'3' => null,
				},
				$this->status
			),
			'newCustomersCount' => $this->getClubNewCustomersCount(
				$game,
				$this->from,
				$this->to,
				match ($this->paymentType) {
					'1' => true,
					'2' => false,
					'3' => null,
				},
				$this->status
			),
		];
		$statistics['reservationsTurnoverSum']['online'] =
			$statistics['reservationsTurnoverPaid']['online'] +
			$statistics['reservationsTurnoverUnpaid']['online'];
		$statistics['reservationsHoursSum']['online'] =
			$statistics['reservationsHoursPaid']['online'] + $statistics['reservationsHoursUnpaid']['online'];
		$statistics['reservationsCountSum']['online'] =
			$statistics['reservationsCountPaid']['online'] + $statistics['reservationsCountUnpaid']['online'];
		$statistics['reservationsTurnoverSum']['offline'] =
			$statistics['reservationsTurnoverPaid']['offline'] +
			$statistics['reservationsTurnoverUnpaid']['offline'];
		$statistics['reservationsHoursSum']['offline'] =
			$statistics['reservationsHoursPaid']['offline'] +
			$statistics['reservationsHoursUnpaid']['offline'];
		$statistics['reservationsCountSum']['offline'] =
			$statistics['reservationsCountPaid']['offline'] +
			$statistics['reservationsCountUnpaid']['offline'];

		$dateRangeDiffInDays = Carbon::parse($this->from)->diffInDays(Carbon::parse($this->to)) + 1;

		$statusKey = match ($this->status) {
			'1' => 'paid',
			'2' => 'unpaid',
			'3' => 'sum',
		};
		$statistics['averageReservationsTurnover'] = [
			'online' =>
				$statistics['reservationsTurnover' . ucfirst($statusKey)]['online'] / $dateRangeDiffInDays,
			'offline' =>
				$statistics['reservationsTurnover' . ucfirst($statusKey)]['offline'] / $dateRangeDiffInDays,
		];
		$statistics['averageReservationsHours'] = [
			'online' =>
				$statistics['reservationsHours' . ucfirst($statusKey)]['online'] / $dateRangeDiffInDays,
			'offline' =>
				$statistics['reservationsHours' . ucfirst($statusKey)]['offline'] / $dateRangeDiffInDays,
		];
		$statistics['averageReservationsCount'] = [
			'online' =>
				$statistics['reservationsCount' . ucfirst($statusKey)]['online'] / $dateRangeDiffInDays,
			'offline' =>
				$statistics['reservationsCount' . ucfirst($statusKey)]['offline'] / $dateRangeDiffInDays,
		];

		$statusKeys = [];
		if (in_array((int) $this->status, [2, 3], true)) {
			$statusKeys[] = 'reservation.status.unpaid';
		}
		if (in_array((int) $this->status, [1, 3], true)) {
			$statusKeys = array_merge($statusKeys, [
				'reservation.status.paid-cash',
				'reservation.status.paid-card',
				'reservation.status.paid-cashless',
			]);
		}
		if (in_array((int) $this->status, [2, 3], true)) {
			$statusKeys[] = 'reservation.status.during-payment';
		}
		if (in_array((int) $this->status, [1, 3], true)) {
			$statusKeys = array_merge($statusKeys, ['reservation.status.paid-online']);
		}
		$statusKeys[] = null;

		$baseDetailedStatisticsValues = [];
		foreach ($statusKeys as $statusKey) {
			$baseDetailedStatisticsValues[$statusKey] = [
				'label' => __($statusKey),
				'value' => 0,
			];
		}

		$statisticsDetailsCategories = ['turnover', 'hours', 'count'];
		$statisticsDetailsCategoriesTypeKeys = ['general', 'canceled'];
		$statisticsDetailsCategoriesValues = array_fill_keys(
			$statisticsDetailsCategoriesTypeKeys,
			$baseDetailedStatisticsValues
		);
		$statistics['detailed'] = array_fill_keys(
			$statisticsDetailsCategories,
			$statisticsDetailsCategoriesValues
		);

		$statistics['detailed']['turnover'] = merge_deeply(
			$statistics['detailed']['turnover'],
			$this->calculatePaymentTypeMainStatistics(
				$game,
				function (mixed $query) {
					return $query->first()->total_price ?? 0;
				},
				$statisticsDetailsCategoriesTypeKeys,
				$statusKeys
			)
		);

		$statistics['detailed']['count'] = merge_deeply(
			$statistics['detailed']['count'],
			$this->calculatePaymentTypeMainStatistics(
				$game,
				function (mixed $query) {
					return $query->count();
				},
				$statisticsDetailsCategoriesTypeKeys,
				$statusKeys
			)
		);

		$statistics['detailed']['hours'] = merge_deeply(
			$statistics['detailed']['hours'],
			$this->calculatePaymentTypeMainStatistics(
				$game,
				function (mixed $query) {
					return $query
						->get()
						->map(function ($reservationSlot) {
							$diff = $reservationSlot->end_at->diff($reservationSlot->start_at);

							return [
								'hours' => $diff->h + $diff->i / 60,
							];
						})
						->sum('hours');
				},
				$statisticsDetailsCategoriesTypeKeys,
				$statusKeys
			)
		);

		$statistics['chart']['online'] = [];
		$statistics['chart']['offline'] = [];

		for (
			$timeIterator = now()->parse($this->from);
			$timeIterator->lte(now()->parse($this->to));
			$timeIterator->addDay()
		) {
			$date = $timeIterator->format('Y-m-d');
			$statistics['chart']['online'][$date] = [
				'turnover' => 0,
				'hours' => 0,
				'count' => 0,
			];
			$statistics['chart']['offline'][$date] = [
				'turnover' => 0,
				'hours' => 0,
				'count' => 0,
			];
		}

		$chartReservationData = club()
			->reservationSlots()
			->when($this->status === '1', function ($query) {
				$query->where('status', ReservationSlotStatus::Confirmed);
			})
			->when($this->status === '2', function ($query) {
				$query->where('status', '!=', ReservationSlotStatus::Confirmed);
			})
			->whereHas('reservation', function ($query) use ($game) {
				$query->where('game_id', $game->id);
			})
			->when(!empty($this->promotion) && str_contains($this->promotion, 'specialOffer_'), function (
				$query
			) {
				$query->where('special_offer_id', str_replace('specialOffer_', '', $this->promotion));
			})
			->when(!empty($this->promotion) && str_contains($this->promotion, 'discountCode_'), function (
				$query
			) {
				$query->where('discount_code_id', str_replace('discountCode_', '', $this->promotion));
			})
            ->whereNull('cancelation_type')
			->whereBetween('start_at', $this->dateTimeRangeFromDate($this->from, $this->to))
			->with('reservation', 'reservation.paymentMethod')
			->get()
			->map(function ($reservationSlot) use($game) {
				$diff = $reservationSlot->getEndAt(['game' => $game])->diff($reservationSlot->start_at);

				return [
					'paymentType' => $reservationSlot->reservation->paymentMethod->online,
					'date' => $reservationSlot->start_at->format('Y-m-d'),
					'hours' => $diff->h + $diff->i / 60,
					'amount' => $reservationSlot->final_price + $reservationSlot->club_commission_partial,
				];
			})
			->groupBy('date')
			->sortBy('date');

		$chartReservationData->each(function ($group, $date) use (&$statistics) {
			$statistics['chart']['online'][$date] = [
				'turnover' => $group->where('paymentType', true)->sum('amount') / 100,
				'count' => $group->where('paymentType', true)->count(),
				'hours' => $group->where('paymentType', true)->sum('hours'),
			];
			$statistics['chart']['offline'][$date] = [
				'turnover' => $group->where('paymentType', false)->sum('amount') / 100,
				'count' => $group->where('paymentType', false)->count(),
				'hours' => $group->where('paymentType', false)->sum('hours'),
			];
		});

		return $statistics;
	}

	public function getClubReservationsTurnover(
		Game $game,
		string $from,
		string $to,
		bool $onlineStatus,
		string $status
	): int {
		return (int) club()
			->reservationSlots()
			->when($status === '1', function ($query) {
				$query->where('status', ReservationSlotStatus::Confirmed);
			})
			->when($status === '0', function ($query) {
				$query->where('status', ReservationSlotStatus::Pending);
			})
			->whereNull('canceled_at')
			->whereHas('reservation', function ($query) use ($game, $onlineStatus) {
				$query->where('game_id', $game->id);
				$query->whereHas('paymentMethod', function ($query) use ($onlineStatus) {
					$query->where('online', $onlineStatus);
				});
			})
			->when(!empty($this->promotion) && str_contains($this->promotion, 'specialOffer_'), function (
				$query
			) {
				$query->where('special_offer_id', str_replace('specialOffer_', '', $this->promotion));
			})
			->when(!empty($this->promotion) && str_contains($this->promotion, 'discountCode_'), function (
				$query
			) {
				$query->where('discount_code_id', str_replace('discountCode_', '', $this->promotion));
			})
			->whereBetween('start_at', $this->dateTimeRangeFromDate($from, $to))
			->selectRaw('sum(final_price) as sum_price, sum(club_commission_partial) as sum_commission')
			->groupBy('reservation_slots.reservation_id', 'pricelists.club_id')
			->get()
			->sum(function ($row) {
				return $row->sum_price + $row->sum_commission;
			});
	}

	public function dateTimeRangeFromDate(string|Carbon $from, string|Carbon $to): array
	{
		return [
			Timezone::convertFromLocal(
				(is_string($from) ? now()->parse($from) : $from)
					->hours(0)
					->minutes(0)
					->seconds(0)
			),
			Timezone::convertFromLocal(
				(is_string($to) ? now()->parse($to) : $to)
					->hours(23)
					->minutes(59)
					->seconds(59)
			),
		];
	}

	public function getClubReservationHoursCount(
		Game $game,
		string $from,
		string $to,
		bool $onlineStatus,
		string $status
	): float {
		return (float) club()
			->reservationSlots()
			->when($status === '1', function ($query) {
				$query->where('status', ReservationSlotStatus::Confirmed);
			})
			->when($status === '0', function ($query) {
				$query->where('status', ReservationSlotStatus::Pending);
			})
			->whereNull('canceled_at')
			->whereHas('reservation', function ($query) use ($game, $onlineStatus) {
				$query->where('game_id', $game->id);
				$query->whereHas('paymentMethod', function ($query) use ($onlineStatus) {
					$query->where('online', $onlineStatus);
				});
			})
			->when(!empty($this->promotion) && str_contains($this->promotion, 'specialOffer_'), function (
				$query
			) {
				$query->where('special_offer_id', str_replace('specialOffer_', '', $this->promotion));
			})
			->when(!empty($this->promotion) && str_contains($this->promotion, 'discountCode_'), function (
				$query
			) {
				$query->where('discount_code_id', str_replace('discountCode_', '', $this->promotion));
			})
			->whereBetween('start_at', $this->dateTimeRangeFromDate($from, $to))
			->get()
			->map(function (ReservationSlot $reservationSlot) {
				$diff = $reservationSlot->end_at->diff($reservationSlot->start_at);

				return [
					'hours' => $diff->h + $diff->i / 60,
				];
			})
			->sum('hours');
	}

	public function getClubReservationsCount(
		Game $game,
		string $from,
		string $to,
		bool $onlineStatus,
		string $status
	): int {
		return (int) club()
			->reservationSlots()
			->when($status === '1', function ($query) {
				$query->where('status', ReservationSlotStatus::Confirmed);
			})
			->when($status === '0', function ($query) {
				$query->where('status', ReservationSlotStatus::Pending);
			})
			->whereNull('canceled_at')
			->whereHas('reservation', function ($query) use ($game, $onlineStatus) {
				$query->where('game_id', $game->id);
				$query->whereHas('paymentMethod', function ($query) use ($onlineStatus) {
					$query->where('online', $onlineStatus);
				});
			})
			->when(!empty($this->promotion) && str_contains($this->promotion, 'specialOffer_'), function (
				$query
			) {
				$query->where('special_offer_id', str_replace('specialOffer_', '', $this->promotion));
			})
			->when(!empty($this->promotion) && str_contains($this->promotion, 'discountCode_'), function (
				$query
			) {
				$query->where('discount_code_id', str_replace('discountCode_', '', $this->promotion));
			})
			->whereBetween('start_at', $this->dateTimeRangeFromDate($from, $to))
			->count();
	}

	public function getClubAllCustomersCount(
		Game $game,
		string $from,
		string $to,
		bool|null $onlineStatus,
		string $status
	): int {
		return (int) club()
			->customers()
			->whereHas('reservations', function (Builder $query) use (
				$game,
				$from,
				$to,
				$onlineStatus,
				$status
			) {
				$query->where('game_id', $game->id);
				$query->when($onlineStatus !== null, function ($query) use ($onlineStatus) {
					$query->whereHas('paymentMethod', function ($query) use ($onlineStatus) {
						$query->where('online', $onlineStatus);
					});
				});
				$query->whereHas('reservationSlots', function ($query) use ($from, $to, $status) {
					$query->when($status === '1', function ($query) {
						$query->where('status', ReservationSlotStatus::Confirmed);
					});
					$query->when($status === '0', function ($query) {
						$query->where('status', '===', ReservationSlotStatus::Pending);
					});
					$query->whereBetween('start_at', $this->dateTimeRangeFromDate($from, $to));
					$query->whereNull('canceled_at');
					$query->when(
						!empty($this->promotion) && str_contains($this->promotion, 'specialOffer_'),
						function ($query) {
							$query->where(
								'special_offer_id',
								str_replace('specialOffer_', '', $this->promotion)
							);
						}
					);
					$query->when(
						!empty($this->promotion) && str_contains($this->promotion, 'discountCode_'),
						function ($query) {
							$query->where(
								'discount_code_id',
								str_replace('discountCode_', '', $this->promotion)
							);
						}
					);
				});
			})
			->count();
	}

	public function getClubNewCustomersCount(
		Game $game,
		string $from,
		string $to,
		bool|null $onlineStatus,
		string $status
	): int {
		return (int) club()
			->customers()
			->whereHas('reservations', function (Builder $query) use (
				$game,
				$from,
				$to,
				$onlineStatus,
				$status
			) {
				$query->where('game_id', $game->id);
				$query->when($onlineStatus !== null, function ($query) use ($onlineStatus) {
					$query->whereHas('paymentMethod', function ($query) use ($onlineStatus) {
						$query->where('online', $onlineStatus);
					});
				});
				$query->whereHas('reservationSlots', function ($query) use ($from, $to, $status) {
					$query->whereBetween('start_at', $this->dateTimeRangeFromDate($from, $to));
					$query->when($status === '1', function ($query) {
						$query->where('status', ReservationSlotStatus::Confirmed);
					});
					$query->when($status === '0', function ($query) {
						$query->where('status', ReservationSlotStatus::Pending);
					});
					$query->whereNull('canceled_at');
					$query->when(
						!empty($this->promotion) && str_contains($this->promotion, 'specialOffer_'),
						function ($query) {
							$query->where(
								'special_offer_id',
								str_replace('specialOffer_', '', $this->promotion)
							);
						}
					);
					$query->when(
						!empty($this->promotion) && str_contains($this->promotion, 'discountCode_'),
						function ($query) {
							$query->where(
								'discount_code_id',
								str_replace('discountCode_', '', $this->promotion)
							);
						}
					);
				});
			})
			->whereDoesntHave('reservations', function ($query) use ($game, $from, $to, $status) {
				$query->whereHas('reservationSlots', function ($query) use ($from, $status) {
					$query->where('start_at', '<', $from);
				});
			})
			->withCount('reservations')
			->havingRaw('reservations_count = 1')
			->groupBy(
				'customers.id',
				'customers.club_id',
				'customers.email',
				'customers.first_name',
				'customers.last_name',
				'customers.password',
				'customers.phone',
				'customers.widget_channel',
				'customers.widget_channel_expiration',
				'customers.locale',
				'customers.verified',
				'customers.deleted_at',
				'customers.created_at',
				'customers.updated_at'
			)
			->count();
	}

	public function calculatePaymentTypeMainStatistics(
		Game $game,
		Closure $calculationMethod,
		array $statisticsDetailsCategoriesTypeKeys,
		array $statusKeys
	): array {
		$result = [];
		foreach ($statisticsDetailsCategoriesTypeKeys as $statisticsDetailsCategoriesTypeKey) {
			$sum = 0;
			foreach ($statusKeys as $statusKey) {
				$reservationQuery = club()
					->reservationSlots()
					->whereBetween('start_at', $this->dateTimeRangeFromDate($this->from, $this->to))
					->whereHas('reservation', function ($query) use ($game, $statusKeys, $statusKey) {
						$query->where('game_id', $game->id);
						$query->whereHas('paymentMethod', function ($query) use ($statusKeys, $statusKey) {
							$query->when(
								in_array($statusKey, [
									'reservation.status.paid-cash',
									'reservation.status.paid-card',
									'reservation.status.paid-cashless',
								]),
								function ($query) use ($statusKey) {
									return $query->where(
										'code',
										match ($statusKey) {
											'reservation.status.paid-cash' => 'cash',
											'reservation.status.paid-card' => 'card',
											'reservation.status.paid-cashless' => 'cashless',
										}
									);
								}
							);

							$query->when(
								in_array($statusKey, [
									'reservation.status.paid-online',
									'reservation.status.during-payment',
								]),
								function ($query) {
									return $query->where('online', true);
								}
							);

							$query->when($statusKey === 'reservation.status.unpaid', function ($query) {
								return $query->where('online', false);
							});
						});
					})
					->when(
						!empty($this->promotion) && str_contains($this->promotion, 'specialOffer_'),
						function ($query) {
							$query->where(
								'special_offer_id',
								str_replace('specialOffer_', '', $this->promotion)
							);
						}
					)
					->when(
						!empty($this->promotion) && str_contains($this->promotion, 'discountCode_'),
						function ($query) {
							$query->where(
								'discount_code_id',
								str_replace('discountCode_', '', $this->promotion)
							);
						}
					)
					->whereIn(
						'status',
						match ($statusKey) {
							'reservation.status.unpaid' => [
								ReservationSlotStatus::Expired,
								ReservationSlotStatus::Pending,
							],
							'reservation.status.during-payment' => [ReservationSlotStatus::Pending],
							default => [ReservationSlotStatus::Confirmed],
						}
					)
					->when($statisticsDetailsCategoriesTypeKey === 'general', function ($query) use (
						$statusKey
					) {
						$query->whereNull('cancelation_type');
					})
					->when($statisticsDetailsCategoriesTypeKey === 'canceled', function ($query) {
						$query->whereNotNull('cancelation_type');
					})->addSelect(DB::raw('final_price + club_commission_partial as total_price'));
				$value = $calculationMethod($reservationQuery);

				$result[$statisticsDetailsCategoriesTypeKey] =
					$result[$statisticsDetailsCategoriesTypeKey] ?? [];
				$result[$statisticsDetailsCategoriesTypeKey][$statusKey] = $result[
					$statisticsDetailsCategoriesTypeKey
				][$statusKey] ?? ['value' => 0];

				$result[$statisticsDetailsCategoriesTypeKey][$statusKey]['value'] +=
					$statusKey === null ? $sum : $value;
				$sum += $result[$statisticsDetailsCategoriesTypeKey][$statusKey]['value'];
			}
		}

		return $result;
	}

	public function mainExport(Game $game): BinaryFileResponse
	{
		$statistics = $this->getMainStatistics($game);

		return Excel::download(new MainStatisticsExport($statistics), 'statistics_' . time() . '.xlsx');
	}

	public function weekly(Game $game): Response
	{
		$game->load('features');
		$statistics = [];
		for ($i = 1; $i <= 7; $i++) {
			$statistics['online'][$i] = [
				'weekDay' => $i,
				'amount' => 0,
				'count' => 0,
				'hours' => 0,
			];
			$statistics['offline'][$i] = [
				'weekDay' => $i,
				'amount' => 0,
				'count' => 0,
				'hours' => 0,
			];
		}

		foreach ($this->calculatePaymentTypeWeeklyStatistics($game, true, $this->status) as $key => $value) {
			$statistics['online'][$key] = $value;
		}
		foreach ($this->calculatePaymentTypeWeeklyStatistics($game, false, $this->status) as $key => $value) {
			$statistics['offline'][$key] = $value;
		}
		$translations = [
			'statistics.tooltip-average-turnover' => __('statistics.tooltip-average-turnover'),
			'statistics.tooltip-online-average-turnover' => __('statistics.tooltip-online-average-turnover'),
			'statistics.tooltip-offline-average-turnover' => __(
				'statistics.tooltip-offline-average-turnover'
			),
			'statistics.tooltip-hours' => __('statistics.tooltip-average-hours'),
			'statistics.tooltip-online-average-hours' => __('statistics.tooltip-online-average-hours'),
			'statistics.tooltip-offline-average-hours' => __('statistics.tooltip-offline-average-hours'),
			'main.hours-postfix' => __('main.hours-postfix'),
		];

		$filterFrom = $this->from;
		$filterTo = $this->to;

		return Inertia::render(
			'Club/Statistics/Weekly',
			compact(['game', 'statistics', 'translations', 'filterFrom', 'filterTo'])
		);
	}

	public function calculatePaymentTypeWeeklyStatistics(Game $game, bool $online, string $status): array
	{
		return club()
			->reservationSlots()
			->when($status === '1', function ($query) {
				$query->where('status', ReservationSlotStatus::Confirmed);
			})
			->when($status === '2', function ($query) {
				$query->where('status', ReservationSlotStatus::Pending);
			})
			->whereNull('canceled_at')
			->where('start_at', '>=', $this->from)
			->where(
				'start_at',
				'<=',
				now()
					->parse($this->to)
					->addDay()
			)
			->whereTime('start_at', '>=', $this->fromTime)
			->whereTime('start_at', '<=', Timezone::convertFromLocal($this->toTime)->format('H:i'))
			->whereHas('reservation', function ($query) use ($game, $online) {
				$query->where('game_id', $game->id);
				$query->whereHas('paymentMethod', function ($query) use ($online) {
					$query->where('online', $online);
				});
			})
			->get()
			->map(function ($reservationSlot) {
				$diff = $reservationSlot->end_at->diff($reservationSlot->start_at);

				return [
					'weekDay' => weekDay($reservationSlot->start_at),
					'amount' => $reservationSlot->final_price,
					'hours' => $diff->h + $diff->i / 60,
				];
			})
			->groupBy('weekDay')
			->mapWithKeys(function ($group, $weekDay) {
				return [
					$weekDay => $group->reduce(
						function ($carry, $reservation) use ($weekDay) {
							return [
								'weekDay' => $weekDay,
								'amount' => $carry['amount'] + $reservation['amount'],
								'count' => $carry['count'] + 1,
								'hours' => $carry['hours'] + $reservation['hours'],
							];
						},
						['hours' => 0, 'amount' => 0, 'count' => 0]
					),
				];
			})
			->toArray();
	}

	public function sets(Game $game): Response
	{
		$filteredSet = request()->get('filters')['set'] ?? null;
		$queryBuilder = club()
			->reservationSlots()
			->where('status', ReservationSlotStatus::Confirmed)
			->where('start_at', '>=', $this->from)
			->where(
				'start_at',
				'<=',
				now()
					->parse($this->to)
					->addDay()
			)
			->with(['sets']);

		$summarizedData = $queryBuilder->get()->map(function ($reservationSlot) use ($filteredSet) {
			if ($filteredSet) {
				return [
					'date' => $reservationSlot->start_at->toDateString(),
					'amount' => $reservationSlot->sets
						->where('pivot.set_id', $filteredSet)
						->sum('pivot.price'),
					'count' => $reservationSlot->sets->where('pivot.set_id', $filteredSet)->count(),
				];
			}

			return [
				'date' => $reservationSlot->start_at->toDateString(),
				'amount' => $reservationSlot->sets->sum('pivot.price'),
				'count' => $reservationSlot->sets->count(),
			];
		});

		$statistics['turnover'] = $summarizedData->sum('amount');
		$statistics['count'] = $summarizedData->sum('count');

		$statistics['chart'] = $summarizedData
			->groupBy('date')
			->map(function ($groupedData) {
				return [
					'date' => $groupedData->first()['date'],
					'amount' => $groupedData->sum('amount'),
				];
			})
			->values()
			->toArray();

		for (
			$timeIterator = now()->parse($this->from);
			$timeIterator->lte(now()->parse($this->to));
			$timeIterator->addDay()
		) {
			if (
				!count(
					array_filter(
						$statistics['chart'],
						static fn($item) => ($item['date'] = $timeIterator->format('Y-m-d'))
					)
				)
			) {
				$statistics['chart'][] = [
					'date' => $timeIterator->format('Y-m-d'),
					'amount' => 0,
				];
			}
		}

		usort($statistics['chart'], static fn($a, $b) => $a['date'] <=> $b['date']);

		$sets = SetResource::collection(club()->sets);

		$translations = [
			'statistics.sets-turnover' => __('statistics.sets-turnover'),
		];

		return Inertia::render(
			'Club/Statistics/Sets',
			compact(['game', 'statistics', 'sets', 'translations'])
		);
	}

	public function rates(Request $request, Game $game): Response
	{
		return Inertia::render('Club/Rates');
	}
}
