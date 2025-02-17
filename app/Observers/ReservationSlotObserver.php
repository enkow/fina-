<?php

namespace App\Observers;

use App\Models\DataChange;
use App\Models\ReservationSlot;

class ReservationSlotObserver
{
	public function created(ReservationSlot $reservationSlot): void
	{
		$reservationSlot->update([
			'creator_id' => $reservationSlot->source === 1 ? auth()->user()?->id : null,
		]);
	}

	public function updated(ReservationSlot $reservationSlot): void
	{
		// do not save initial price calculation update
		DataChange::storeChange($reservationSlot);

		if ($reservationSlot->refund) {
			$reservationSlot->refund->refreshHelperColumns();
		}
	}
}
