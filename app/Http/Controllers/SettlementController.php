<?php

namespace App\Http\Controllers;

use App\Exports\ExportManager;
use App\Models\Club;
use App\Models\Game;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\TablePreference;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SettlementController extends Controller
{
	private function formatAmount(Club $club, float $amount): string
	{
		return number_format($amount / 100, 2, ',', ' ') . ' ' . $club->country->currency;
	}
	protected function getDateFromFilter(): \Illuminate\Support\Carbon
	{
		$filters = request()->get('filters');
		return now()->parse($filters['date'] ?? now()->format('Y-m') . '-01');
	}

	protected function getReservations(
		Club $club,
		Game $game,
		Invoice $settlement,
		bool $onlyOnlinePayment = false,
		$itemsPerPage = null,
		$itemsPerPageName = null
	) {
		$reservationsTableData = Reservation::tableData(gameId: $game->id);
		$reservations = Reservation::getReservations(
			clubId: $club->id,
			invoiceId: $settlement->id,
			paginated: true,
			itemsPerPage: $itemsPerPage ??
				(request()->all()['perPage'][$itemsPerPageName ?? $reservationsTableData['name']] ?? 10),
			tablePreference: $reservationsTableData['preference'],
			tableName: $reservationsTableData['name'],
			paginationTableName: $reservationsTableData['name'],
			onlyOnlinePayment: $onlyOnlinePayment
		);
		$reservationsCollection = Reservation::whereIn(
			'id',
			$reservations->getCollection()->pluck('reservation_id')
		)->get();
		$reservations->through(function ($reservation) use ($club, $reservationsCollection) {
			$reservationModel = $reservationsCollection->where('id', $reservation['reservation_id'])->first();
			$reservation['app_commission'] = $this->formatAmount($club, $reservationModel->app_commission);
			$reservation['provider_commission'] = $this->formatAmount(
				$club,
				$reservationModel->provider_commission
			);
			return $reservation;
		});
		return $reservations;
	}

	protected function getReservationsExport(
		Club $club,
		Invoice $settlement,
		Game $game
	): \Illuminate\Http\Response|BinaryFileResponse {
		$reservations = $this->getReservations(
			$club,
			$game,
			$settlement,
			true,
			itemsPerPage: $settlement->reservations()->count()
		)->map(function ($reservation) {
			return Reservation::prepareOutputForExport($reservation);
		});

		$reservationsTableData = Reservation::tableData(
			tableName: 'settlement_reservations_' . $game->id,
			gameId: $game->id
		);

		return ExportManager::export(
			TablePreference::getEnabledColumns(
				$reservationsTableData['preference'],
				Reservation::$exportFieldExclusions,
				Reservation::$exportFieldInclusions
			),
			$reservationsTableData['headings'],
			$reservations,
			'csv'
		);
	}
}
