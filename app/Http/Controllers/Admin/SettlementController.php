<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\InvoiceResource;
use App\Models\Club;
use App\Models\Game;
use App\Models\Invoice;
use App\Models\Reservation;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SettlementController extends \App\Http\Controllers\SettlementController
{
	public function index()
	{
		$date = $this->getDateFromFilter();
		$clubs = Club::with(['invoices', 'invoices.items', 'country'])
			->whereHas('invoices', function ($query) use ($date) {
				$query
					->where('from', '<=', $date->endOfMonth()->format('Y-m-d'))
					->where('to', '>=', $date->startOfMonth()->format('Y-m-d'));
			})
			->searchable('club', ['name', 'email', 'city'])
			->paginate(15);

		return Inertia::render('Admin/Settlements/Index', compact(['clubs']));
	}

	public function showClub(Club $club): Response
	{
		$date = $this->getDateFromFilter();
		$invoices = InvoiceResource::collection(
			$club
				->invoices()
				->where('from', '<=', $date->endOfMonth()->format('Y-m-d'))
				->where('to', '>=', $date->startOfMonth()->format('Y-m-d'))
				->orderByDesc('id')
				->with('items')
				->paginate(30)
		);
		$club = $club->load(['country']);

		return Inertia::render('Admin/Settlements/ShowClub', compact(['invoices', 'club']));
	}

	public function show(Club $club, Invoice $settlement): Response
	{
		$invoice = new InvoiceResource($settlement->load(['items', 'items.model']));
		$club = $club->load(['country']);

		return Inertia::render('Admin/Settlements/Show', compact(['invoice', 'club']));
	}

	public function showReservations(Club $club, Invoice $settlement, Game $game): Response
	{
		$reservationsTableData = Reservation::tableData(gameId: $game->id);
		$invoice = new InvoiceResource($settlement->load(['items', 'items.model']));
		$club = $club->load(['country']);

		$reservations = $this->getReservations($club, $game, $settlement);

		$reservationsTableHeadings = $reservationsTableData['headings'];

		return Inertia::render(
			'Admin/Settlements/ShowReservations',
			compact(['reservations', 'club', 'invoice', 'game', 'reservationsTableHeadings'])
		);
	}

	public function export(
		Club $club,
		Invoice $settlement,
		Game $game
	): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse {
		return $this->getReservationsExport($club, $settlement, $game);
	}
}
