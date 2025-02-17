<?php

namespace App\Http\Controllers\Club;

use App\Exports\ExportManager;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\TablePreference;
use App\Sorters\Reservation\GameNameSorter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RateController extends Controller
{
	private array $reservationsTableData;

	public function __construct()
	{
		$this->middleware(function (Request $request, Closure $next) {
			if (
				!$request->has('filters') ||
				!isset(
					$request->get('filters')['rates']['startRange']['from'],
					$request->get('filters')['rates']['startRange']['to']
				)
			) {
				return redirect()->route('club.rates.index', [
					'filters' => [
						'rates' => [
							'startRange' => [
								'from' => now()
									->startOfMonth()
									->format('Y-m-d'),
								'to' => now()
									->endOfMonth()
									->format('Y-m-d'),
							],
						],
					],
				]);
			}

			$this->reservationsTableData = Reservation::tableData('rates');

			return $next($request);
		})->only(['index', 'export']);
	}

	public function index(): Response
	{
        $clubPricelistIds = DB::table('pricelists')
            ->where('club_id', clubId())
            ->pluck('id');
        $clubSlotIds = DB::table('slots')
            ->whereIn('pricelist_id', $clubPricelistIds)
            ->pluck('id');
		$averageRates = Reservation::whereHas('reservationSlots', function ($query) use ($clubSlotIds) {
			$query->whereIn('slot_id', $clubSlotIds);
		})
			->filterable($this->reservationsTableData['name'], Reservation::$availableFilters)
			->selectRaw(
				DB::raw(
					'avg(rate_service) as service, avg(rate_atmosphere) as atmosphere, avg(rate_staff) as staff'
				)
			)
			->first() ?? [
			'service' => 0,
			'atmosphere' => 0,
			'staff' => 0,
		];
		$reservations = Reservation::whereHas('reservationSlots', function ($query) use($clubSlotIds) {
            $query->whereIn('slot_id', $clubSlotIds);
		})
			->with('customer', 'reservationNumber', 'firstReservationSlot.reservationNumber')
			->filterable($this->reservationsTableData['name'], Reservation::$availableFilters)
			->sortable(
				$this->reservationsTableData['name'],
				array_merge(Reservation::$availableSorters, [
					'rate_service',
					'rate_atmosphere',
					'rate_staff',
					GameNameSorter::class,
					'start_at',
				])
			)
			->searchable(
				$this->reservationsTableData['name'],
				array_merge(Reservation::$availableSearchers, [
					'rate_content',
					\App\Searchers\Rate\NumberSearcher::class,
				])
			)
			->withMax('reservationSlots', 'end_at')
			->withMin('reservationSlots', 'start_at')
			->withSum('reservationSlots', 'final_price')
			->withCount('reservationSlots')
			->whereNotNull('rate_staff')
			->paginate(request()['perPage']['rates'] ?? 10)
			->through(function (Reservation $reservation) {
				// prepare reservation rates data to send only necessary data
				$result = TablePreference::getDataArrayFromModel(
					$reservation->prepareForOutput(
						withoutFields: [
							'sets',
							'parent_slot_name',
							'reservation_type_color',
							'status',
							'club_reservation',
							'status_color',
							'status_locale',
						]
					),
					$this->reservationsTableData['preference']
				);

				$result['rate_service'] = $reservation->formattedRate('service');
				$result['rate_atmosphere'] = $reservation->formattedRate('atmosphere');
				$result['rate_staff'] = $reservation->formattedRate('staff');
				$result['rate_final'] = $reservation->formattedRate('final');
				return $result;
			});

		return Inertia::render('Club/Rates', [
			'reservations' => $reservations,
			'reservationsTableHeadings' => $this->reservationsTableData['headings'],
			'averageRates' => $averageRates,
		]);
	}

	public function export(Request $request): \Illuminate\Http\Response|BinaryFileResponse
	{
		$reservations = Reservation::whereHas('reservationSlots', function ($query) {
			$query->whereHas('slot', function ($query) {
				$query->whereHas('pricelist', function ($query) {
					$query->where('club_id', clubId());
				});
			});
		})
			->with(
				'customer',
				'game',
				'reservationNumber',
				'reservationSlots',
				'reservationSlots.reservationNumber'
			)
			->filterable($this->reservationsTableData['name'], Reservation::$availableFilters)
			->sortable($this->reservationsTableData['name'], Reservation::$availableSorters)
			->searchable(
				$this->reservationsTableData['name'],
				array_merge(Reservation::$availableSearchers, ['rate_content'])
			)
			->whereNotNull('rate_staff')
			->get()
			->map(function (Reservation $reservation) {
				$reservation->rate_final = round(
					($reservation->rate_staff + $reservation->rate_atmosphere + $reservation->rate_service) /
						3,
					2
				);

				$reservation->rate_service = $reservation->formattedRate('service');
				$reservation->rate_atmosphere = $reservation->formattedRate('atmosphere');
				$reservation->rate_staff = $reservation->formattedRate('staff');
				$reservation->rate_final = $reservation->formattedRate('final');
				return TablePreference::getDataArrayFromModel(
					$reservation->prepareForOutput(),
					$this->reservationsTableData['preference']
				);
			});

		return ExportManager::export(
			TablePreference::getEnabledColumns($this->reservationsTableData['preference']),
			$this->reservationsTableData['headings'],
			$reservations,
			$request->get('extension'),
			['a4', 'landscape']
		);
	}
}
