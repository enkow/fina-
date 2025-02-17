<?php

namespace App\Http\Controllers\Club;

use App\Enums\ReservationSlotCancelationType;
use App\Http\Controllers\Controller;
use App\Http\Resources\RefundResource;
use App\Models\Refund;
use App\Searchers\Refund\CustomerNameSearcher;
use App\Searchers\Refund\NumberSearcher;
use App\Sorters\Refund\ReservationNumberSorter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RefundController extends Controller
{
	public function index(): Response
	{
		ini_set('memory_limit', '512M');
		$refunds = RefundResource::collection(
			Refund::with('approver')
				->whereHas('reservationSlots', function ($query) {
					$query->whereHas('reservation', function ($query) {
						$query->whereHas('paymentMethod', function ($query) {
							$query->where('online', true);
						});
					});
					$query->whereHas('slot', function ($query) {
						$query->whereHas('pricelist', function ($query) {
							$query->where('club_id', clubId());
						});
					});
				})
				->whereDoesntHave('reservationSlots', function ($query) {
					$query->where('cancelation_type', ReservationSlotCancelationType::System);
				})
				->sortable('refunds', ['id', 'created_at', 'price', 'status', ReservationNumberSorter::class])
				->searchable('refunds', [NumberSearcher::class])
				->with('firstReservationSlot.reservation.customer')
				->orderByDesc('id')
				->paginate(request()['perPage']['refunds'] ?? 10)
				->through(function ($refund) {
					$refund->cancelation_type = $refund->firstReservationSlot->cancelation_type;
					$refund->reservation_numbers = $refund->firstReservationSlot->game->hasFeature(
						'person_as_slot'
					)
						? $refund->firstReservationSlot->reservation->reservationNumber
						: $refund->reservationNumbers()->get();
					$refund->customer = $refund->firstReservationSlot->reservation->customer;
					return $refund;
				})
		);

		return Inertia::render('Club/Refunds/Index', compact('refunds'));
	}

	public function approve(Refund $refund): RedirectResponse
	{
		if ($refund->canBeApproved()) {
			$result = $refund->approve();

			if ($result['message']) {
				return redirect()
					->back()
					->with('message', [
						'type' => $result['refunded'] ? 'success' : 'error',
						'content' => $result['message'],
						'timeout' => $result['timeout'] ?? 1500,
					]);
			}
		}

		return redirect()
			->back()
			->with('message', [
				'type' => 'error',
				'content' => __('refund.refund-cannot-be-accepted'),
			]);
	}
}
