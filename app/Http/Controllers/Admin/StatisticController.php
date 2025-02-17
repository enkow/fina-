<?php

namespace App\Http\Controllers\Admin;

use App\Custom\Timezone;
use App\Enums\ReservationSlotStatus;
use App\Exports\MainStatisticsExport;
use App\Http\Controllers\Controller;
use App\Models\ReservationSlot;
use App\Models\Customer;
use App\Models\Game;
use App\Models\Club;
use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
	private int|null $gameId;
	private int|null $clubId;
	private int|null $status;

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
		$this->gameId = data_get($this->filters, 'game');
		$this->clubId = data_get($this->filters, 'club');

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
						'game' => '0',
						'club' => '0',
					],
					'chartType' => 'turnover',
				]);
			}

			return $next($request);
		})->only(['index']);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 * @throws Exception
	 */
	public function index(): Response|RedirectResponse
	{
		$statistics = $this->getMainStatistics();
		$translations = [
			'statistics.turnover' => __('statistics.turnover'),
			'statistics.tooltip-turnover' => __('statistics.tooltip-turnover'),
			'statistics.tooltip-offline-turnover' => __('statistics.tooltip-offline-turnover'),
			'statistics.tooltip-online-turnover' => __('statistics.tooltip-online-turnover'),
			'statistics.tooltip-offline-hours' => __('statistics.tooltip-offline-hours'),
			'statistics.tooltip-online-hours' => __('statistics.tooltip-online-hours'),
			'statistics.tooltip-offline-count' => __('statistics.tooltip-offline-count'),
			'statistics.tooltip-online-count' => __('statistics.tooltip-online-count'),
		];

		return Inertia::render('Admin/Statistics/Index', [
			'statistics' => $statistics,
			'translations' => $translations,
			'clubs' => Club::where('country_id', auth()->user()?->country_id)
				->with('country')
				->get(),
			'games' => Game::all(),
		]);
	}

	public function getMainStatistics(): array
	{
		$statistics = [
			'reservationsTurnoverPaid' => [
				'online' => $this->getClubReservationsTurnover($this->from, $this->to, true, '1'),
				'offline' => $this->getClubReservationsTurnover($this->from, $this->to, false, '1'),
			],
			'reservationsTurnoverUnpaid' => [
				'online' => $this->getClubReservationsTurnover($this->from, $this->to, true, '0'),
				'offline' => $this->getClubReservationsTurnover($this->from, $this->to, false, '0'),
			],
			'reservationsHoursPaid' => [
				'online' => $this->getClubReservationHoursCount($this->from, $this->to, true, '1'),
				'offline' => $this->getClubReservationHoursCount($this->from, $this->to, false, '1'),
			],
			'reservationsHoursUnpaid' => [
				'online' => $this->getClubReservationHoursCount($this->from, $this->to, true, '0'),
				'offline' => $this->getClubReservationHoursCount($this->from, $this->to, false, '0'),
			],
			'reservationsCountPaid' => [
				'online' => $this->getClubReservationsCount($this->from, $this->to, true, '1'),
				'offline' => $this->getClubReservationsCount($this->from, $this->to, false, '1'),
			],
			'reservationsCountUnpaid' => [
				'online' => $this->getClubReservationsCount($this->from, $this->to, true, '0'),
				'offline' => $this->getClubReservationsCount($this->from, $this->to, false, '0'),
			],
			'allCustomersCount' => $this->getClubAllCustomersCount(
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
			1 => 'paid',
			2 => 'unpaid',
			3 => 'sum',
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
				'label' => __($statusKey, [], 'pl'),
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
				function (mixed $query) {
					return $query->sum('final_price');
				},
				$statisticsDetailsCategoriesTypeKeys,
				$statusKeys
			)
		);

		$statistics['detailed']['count'] = merge_deeply(
			$statistics['detailed']['count'],
			$this->calculatePaymentTypeMainStatistics(
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

		$chartReservationData = ReservationSlot::when($this->status === '1', function ($query) {
			$query->where('status', ReservationSlotStatus::Confirmed);
		})
			->when($this->clubId !== 0, function ($query) {
				$query->whereHas('slot.pricelist', function ($query) {
					$query->where('club_id', $this->clubId);
				});
			})
			->when($this->status === '2', function ($query) {
				$query->where('status', '!=', ReservationSlotStatus::Confirmed);
			})
			->when($this->gameId !== 0, function ($query1) {
				$query1->whereHas('reservation', function ($query) {
					$query->where('game_id', $this->gameId);
				});
			})
			->whereBetween('start_at', $this->dateTimeRangeFromDate($this->from, $this->to))
			->with('reservation', 'reservation.paymentMethod')
			->get()
			->map(function ($reservationSlot) {
				$diff = $reservationSlot->end_at->diff($reservationSlot->start_at);

				return [
					'paymentType' => $reservationSlot->reservation->paymentMethod->online,
					'date' => $reservationSlot->start_at->format('Y-m-d'),
					'hours' => $diff->h + $diff->i / 60,
					'amount' => $reservationSlot->final_price,
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
		string $from,
		string $to,
		bool $onlineStatus,
		string $status
	): int {
		return (int) ReservationSlot::when($status === '1', function ($query) {
			$query->where('status', ReservationSlotStatus::Confirmed);
		})
			->when($status === '0', function ($query) {
				$query->where('status', ReservationSlotStatus::Pending);
			})
			->whereNull('canceled_at')
			->when($this->clubId !== 0, function ($query) {
				$query->whereHas('slot.pricelist', function ($query) {
					$query->where('club_id', $this->clubId);
				});
			})
			->whereHas('reservation', function ($query) use ($onlineStatus) {
				$query->when($this->gameId !== 0, function ($query1) {
					$query1->where('game_id', $this->gameId);
				});
				$query->whereHas('paymentMethod', function ($query) use ($onlineStatus) {
					$query->where('online', $onlineStatus);
				});
			})
			->whereBetween('start_at', $this->dateTimeRangeFromDate($from, $to))
			->sum('final_price');
		//            ->selectRaw('sum(final_price) as sum_price, reservations.commission as commission')
		//            ->join('reservations', 'reservation_slots.reservation_id', '=', 'reservations.id')
		//            ->groupBy('reservation_slots.reservation_id', 'pricelists.club_id')
		//            ->get()
		//            ->sum(function ($row) {
		//                return $row->sum_price + $row->commission;
		//            });
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
		string $from,
		string $to,
		bool $onlineStatus,
		string $status
	): float {
		return (float) ReservationSlot::when($status === '1', function ($query) {
			$query->where('status', ReservationSlotStatus::Confirmed);
		})
			->when($status === '0', function ($query) {
				$query->where('status', ReservationSlotStatus::Pending);
			})
			->whereNull('canceled_at')
			->when($this->clubId !== 0, function ($query) {
				$query->whereHas('slot.pricelist', function ($query) {
					$query->where('club_id', $this->clubId);
				});
			})
			->whereHas('reservation', function ($query) use ($onlineStatus) {
				$query->when($this->gameId !== 0, function ($query1) {
					$query1->where('game_id', $this->gameId);
				});
				$query->whereHas('paymentMethod', function ($query) use ($onlineStatus) {
					$query->where('online', $onlineStatus);
				});
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
		string $from,
		string $to,
		bool $onlineStatus,
		string $status
	): int {
		return (int) ReservationSlot::when($status === '1', function ($query) {
			$query->where('status', ReservationSlotStatus::Confirmed);
		})
			->when($status === '0', function ($query) {
				$query->where('status', ReservationSlotStatus::Pending);
			})
			->whereNull('canceled_at')
			->when($this->clubId !== 0, function ($query) {
				$query->whereHas('slot.pricelist', function ($query) {
					$query->where('club_id', $this->clubId);
				});
			})
			->whereHas('reservation', function ($query) use ($onlineStatus) {
				$query->when($this->gameId !== 0, function ($query1) {
					$query1->where('game_id', $this->gameId);
				});
				$query->whereHas('paymentMethod', function ($query) use ($onlineStatus) {
					$query->where('online', $onlineStatus);
				});
			})
			->whereBetween('start_at', $this->dateTimeRangeFromDate($from, $to))
			->count();
	}

	public function getClubAllCustomersCount(
		string $from,
		string $to,
		bool|null $onlineStatus,
		string $status
	): int {
		return (int) Customer::whereHas('reservations', function (Builder $query) use (
			$from,
			$to,
			$onlineStatus,
			$status
		) {
			$query->when($this->clubId !== 0, function ($query1) {
				$query1->where('club_id', $this->clubId);
			});
			$query->when($this->gameId !== 0, function ($query1) {
				$query1->where('game_id', $this->gameId);
			});
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
			});
		})->count();
	}

	public function getClubNewCustomersCount(
		string $from,
		string $to,
		bool|null $onlineStatus,
		string $status
	): int {
		return (int) Customer::whereHas('reservations', function (Builder $query) use (
			$from,
			$to,
			$onlineStatus,
			$status
		) {
			$query->when($this->clubId !== 0, function ($query1) {
				$query1->where('club_id', $this->clubId);
			});
			$query->when($this->gameId !== 0, function ($query1) {
				$query1->where('game_id', $this->gameId);
			});
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
			});
		})
			->whereDoesntHave('reservations', function ($query) use ($from) {
				$query->whereHas('reservationSlots', function ($query) use ($from) {
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
		Closure $calculationMethod,
		array $statisticsDetailsCategoriesTypeKeys,
		array $statusKeys
	): array {
		$result = [];
		foreach ($statisticsDetailsCategoriesTypeKeys as $statisticsDetailsCategoriesTypeKey) {
			$sum = 0;
			foreach ($statusKeys as $statusKey) {
				$reservationQuery = ReservationSlot::whereBetween(
					'start_at',
					$this->dateTimeRangeFromDate($this->from, $this->to)
				)
					->when($this->clubId !== 0, function ($query) {
						$query->whereHas('slot.pricelist', function ($query) {
							$query->where('club_id', $this->clubId);
						});
					})
					->whereHas('reservation', function ($query) use ($statusKeys, $statusKey) {
						$query->when($this->gameId !== 0, function ($query1) {
							$query1->where('game_id', $this->gameId);
						});
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
					});
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

	public function mainExport(): BinaryFileResponse
	{
		$statistics = $this->getMainStatistics();

		return Excel::download(new MainStatisticsExport($statistics), 'statistics_' . time() . '.xlsx');
	}
}
