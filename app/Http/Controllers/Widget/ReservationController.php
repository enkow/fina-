<?php

namespace App\Http\Controllers\Widget;

use App\Enums\ReservationSlotCancelationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Widget\CancelReservationRequest;
use App\Http\Requests\Widget\RateReservationRequest;
use App\Models\Club;
use App\Models\Customer;
use App\Models\ReservationNumber;
use App\Models\Setting;
use App\Notifications\Manager\ReservationRatedNotification;
use App\Notifications\Manager\ReservationStoredNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
	public function cancelAction(
		CancelReservationRequest $request,
		Club $club,
		string $encryptedCustomerId,
		string $reservationNumber
	): RedirectResponse {
		$reservationNumberModel = ReservationNumber::find($reservationNumber);
		if (!$reservationNumberModel->isCanceled()) {
			$reservationNumberModel->cancel(true, ReservationSlotCancelationType::Customer, null);
		}

		return redirect()->back();
	}

	public function cancel(
		CancelReservationRequest $request,
		Club $club,
		string $encryptedCustomerId,
		string $reservationNumber
	): Response {
		$reservationNumberModel = ReservationNumber::find($reservationNumber);
		$customer = Customer::find(Crypt::decrypt($encryptedCustomerId));

		$firstReservationSlot = $reservationNumberModel->numerable->firstReservationSlot;

		return Inertia::render(
			'Widget-3/ReservationCancelation',
			$firstReservationSlot->slot->pricelist->club->getWidgetProps($customer, [
				'reservationNumberId' => $reservationNumberModel->id,
				'reservationNumberFormatted' => $reservationNumberModel->formattedNumber,
				'encryptedCustomerId' => $encryptedCustomerId,
				'isCanceled' => $reservationNumberModel->isCanceled(),
				'refundTimeLimit' => Setting::retrieve()['refund_time_limit']['value'],
				'canBeRefunded' => $reservationNumberModel->canBeRefunded(),
				'paymentMethod' => $reservationNumberModel->numerable->reservation->paymentMethod,
			])
		);
	}

	public function rating(
		RateReservationRequest $request,
		Club $club,
		string $encryptedCustomerId,
		string $reservationNumber
	): Response {
		$reservationNumberModel = ReservationNumber::find($reservationNumber);
		$customerId = Crypt::decrypt($encryptedCustomerId);
		$customer = Customer::find($customerId);

		return Inertia::render(
			'Widget-3/ReservationRating',
			$club->getWidgetProps($customer, [
				'reservationNumberId' => $reservationNumberModel->id,
				'ratedStatus' => $reservationNumberModel->numerable->reservation->rate_service !== null,
			])
		);
	}

	public function rate(
		RateReservationRequest $request,
		Club $club,
		string $encryptedCustomerId,
		string $reservationNumber
	): RedirectResponse {
		$reservationNumberModel = ReservationNumber::find($reservationNumber);
		$reservationNumberModel->numerable->reservation->update(
			$request->only(['rate_service', 'rate_staff', 'rate_atmosphere', 'rate_content'])
		);

		$managerEmails = $club
			->users()
			->where('type', 'manager')
			->pluck('email')
			->toArray();
		foreach ($managerEmails as $email) {
			$notifiable = (new AnonymousNotifiable())->route('mail', $email);
			$notifiable->notify(new ReservationRatedNotification($reservationNumberModel));
		}

		return redirect()->back();
	}
}
