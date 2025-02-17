<?php

namespace App\Observers;

use App\Events\CalendarDataChanged;
use App\Events\ReservationStored;
use App\Models\Club;
use App\Models\DataChange;
use App\Models\PaymentMethod;
use App\Models\Pricelist;
use App\Models\Reservation;
use App\Models\Slot;
use Illuminate\Database\Eloquent\Collection;

class ReservationObserver
{
	public function updated(Reservation $reservation, Collection|null $reservationSlots = null): void
	{
		if (now('UTC')->diffInSeconds($reservation->getRawOriginal('created_at')) > 3) {
			DataChange::storeChange($reservation);
			$club = $reservation->getClub();
			event(new CalendarDataChanged($club));
		}

		($reservationSlots ?? $reservation->reservationSlots)->each(function ($reservationSlot) use (
			$reservation,
			$reservationSlots
		) {
			$reservationSlot->updatePartialCommissions($reservation, $reservationSlots);
		});
	}

	public function created(Reservation $reservation, Collection|null $reservationSlots = null): void
	{
		//        if(!$reservation->firstReservationSlot->slot->pricelist->club->offline_payments_enabled && $reservation->paymentMethod->online === false && $reservation->price > 0) {
		//            $reservation->numberModel->cancel(true, ReservationSlotCancelationType::System, null);
		//        }

		$firstReservationSlot = $reservationSlots?->first() ?? $reservation->firstReservationSlot;
		$slot = Slot::getSlot($firstReservationSlot->slot_id);
		$pricelist = Pricelist::getPricelist($slot->pricelist_id);
		$club = Club::getClub($pricelist->club_id);
		if (PaymentMethod::getPaymentMethod($reservation->payment_method_id)->online === false) {
			$reservation->sendStoreNotification();
			event(new ReservationStored($reservation));
		}
		event(new CalendarDataChanged($club));
	}
}
