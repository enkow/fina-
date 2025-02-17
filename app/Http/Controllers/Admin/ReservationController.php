<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExportManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterReservationsRequest;
use App\Http\Resources\GameResource;
use App\Http\Resources\ReservationResource;
use App\Models\Game;
use App\Models\Reservation;
use App\Models\TablePreference;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReservationController extends Controller
{
	private array $reservationsTableData;

	public function __construct()
	{
		// redirect user to default filters if not present
		$this->middleware(function (Request $request, Closure $next) {
			if (!$request->has('filters') || !isset($request?->get('filters')['reservations'])) {
				return redirect()->route('admin.reservations.index', [
					'filters' => [
						'reservations' => [
							'startRange' => [
								'from' => now()
									->subWeek()
									->format('Y-m-d'),
								'to' => now()->format('Y-m-d'),
							],
							'game' => Game::first()->id ?? 1,
						],
					],
					'sorters' => [
						'reservations' => [
							'created_datetime' => 'desc',
						],
					],
				]);
			}
			$this->reservationsTableData = Reservation::tableData(
				gameId: $request?->get('filters')['reservations']['game']
			);

			return $next($request);
		})->only('index', 'export');
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function index(FilterReservationsRequest $request): Response
	{
		$reservations = Reservation::getReservations(
			paginated: true,
			tablePreference: $this->reservationsTableData['preference'],
			tableName: $this->reservationsTableData['name']
		);

		return Inertia::render('Admin/Reservations/Index', [
			'reservations' => $reservations,
			'reservationsTableHeadings' => $this->reservationsTableData['headings'],
			'games' => GameResource::collection(Game::all()),
		]);
	}

	public function show(Reservation $reservation): JsonResponse
	{
		return response()->json(new ReservationResource($reservation));
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function export(FilterReservationsRequest $request): \Illuminate\Http\Response|BinaryFileResponse
	{
		$reservations = Reservation::getReservations(
			paginated: false,
			tablePreference: $this->reservationsTableData['preference'],
			tableName: $this->reservationsTableData['name']
		)->map(function ($reservation) {
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
}
