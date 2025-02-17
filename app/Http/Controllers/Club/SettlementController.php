<?php

namespace App\Http\Controllers\Club;

use App\Http\Resources\InvoiceResource;
use App\Models\Game;
use App\Models\Invoice;
use App\Models\Reservation;
use Inertia\Inertia;
use Inertia\Response;

class SettlementController extends \App\Http\Controllers\SettlementController
{
	public function index(): Response
	{
		$date = $this->getDateFromFilter();
		$invoices = InvoiceResource::collection(
			club()
				->invoices()
				->where('from', '<=', $date->endOfMonth()->format('Y-m-d'))
				->where('to', '>=', $date->startOfMonth()->format('Y-m-d'))
				->orderByDesc('id')
				->with('items')
				->paginate(30)
		);
		$club = club()->load(['country']);

		return Inertia::render('Club/Settlements/Index', compact(['invoices', 'club']));
	}

	public function show(Invoice $settlement): Response
	{
		$invoice = new InvoiceResource($settlement->load(['items', 'items.model']));
		$club = club()->load(['country']);

		return Inertia::render('Club/Settlements/Show', compact(['invoice', 'club']));
	}

	public function showReservations(Invoice $settlement, Game $game): Response
	{
		$reservationsTableData = Reservation::tableData(gameId: $game->id);

		$reservations = $this->getReservations(
			club(),
			$game,
			$settlement,
			true,
			itemsPerPageName: 'settlement_reservations'
		);

		$invoice = new InvoiceResource($settlement->load(['items', 'items.model']));
		$club = club()->load(['country']);
		$reservationsTableHeadings = $reservationsTableData['headings'];
        $game->load('features');

		return Inertia::render(
			'Club/Settlements/ShowReservations',
			compact(['reservations', 'club', 'invoice', 'game', 'reservationsTableHeadings'])
		);
	}

	public function export(
		Invoice $settlement,
		Game $game
	): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse {
		return $this->getReservationsExport(club(), $settlement, $game);
	}
}
