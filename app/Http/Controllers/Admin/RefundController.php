<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RefundResource;
use App\Models\Refund;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RefundController extends Controller
{
	public function index(): Response
	{
		$refunds = RefundResource::collection(
			Refund::with('approver')
				->orderByDesc('id')
				->paginate(50)
				->through(function ($refund) {
					$refund->reservation_numbers = $refund->firstReservationSlot?->reservation->game->hasFeature(
						'person_as_slot'
					)
						? $refund->firstReservationSlot->reservation->reservationNumber
						: $refund->reservationNumbers()->get();
					return $refund;
				})
		);

		return Inertia::render('Admin/Refunds/Index', compact('refunds'));
	}

	public function approve(Refund $refund): RedirectResponse
	{
		if ($refund->canBeApproved()) {
			$refund->approve();

			return redirect()
				->back()
				->with('message', [
					'type' => 'success',
					'content' => 'Zwrot został zaakceptowany',
				]);
		}

		return redirect()
			->back()
			->with('message', [
				'type' => 'error',
				'content' => 'Zwrot nie może zostać zaakceptowany',
			]);
	}
}
