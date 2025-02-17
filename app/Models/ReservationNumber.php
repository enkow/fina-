<?php

namespace App\Models;

use App\Enums\BulbReason;
use App\Enums\BulbStatus;
use App\Enums\RefundStatus;
use App\Enums\ReservationSlotCancelationType;
use App\Enums\ReservationSlotStatus;
use App\Events\CalendarDataChanged;
use App\Models\Features\SlotHasBulb;
use App\Notifications\Manager\ReservationCanceledNotification;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Cache;

class ReservationNumber extends BaseModel
{
	public static function fromNumber(string $number)
	{
		return Cache::remember(
			'reservation_number:' . $number . ':reservationNumberModel',
			config('cache.model_cache_time'),
			function () use ($number) {
				return self::where('id', self::numberToId($number))->first();
			}
		);
	}

	protected function formattedNumber(): Attribute
	{
		return Attribute::make(get: fn() => str_pad($this->id, 5, '0', STR_PAD_LEFT));
	}

	public static function numberToId(string $number): int
	{
		return ltrim($number, '0');
	}

	public function numerable(): MorphTo
	{
		return $this->morphTo();
	}

	public function belongsToUser(): bool
	{
		return Pricelist::getPricelist(Slot::getSlot($this->numerable->firstReservationSlot->slot_id)->pricelist_id)
			->club_id === clubId();
	}

	public function cancel(
		bool $cancelRelated,
		ReservationSlotCancelationType $cancelationType,
		string|null $cancelationReason,
		bool $presence = true,
		ReservationSlotStatus $status = null
	): bool {
		if ($this->isCanceled()) {
			return false;
		}
		$numerable = $this->numerable;
		$reservation = $numerable->reservation;
		$allReservationSlots = $reservation->reservationSlots;
		if ($this->numerable_type === ReservationSlot::class) {
			if (
				Game::getCached()
					->find($this->numerable->reservation->game_id)
					->hasFeature('has_timers')
			) {
				$this->numerable->stopTimer(true);
			}

			if ($cancelRelated) {
				$updatableSlotIds = $allReservationSlots->pluck('id')->toArray();
				$price = $allReservationSlots->whereNull('refund_id')->sum('final_price');
			} else {
				$updatableSlotIds = [$numerable['id']];
				$price = $this->numerable->final_price;
			}

			if (
				$numerable->slot
					->features()
					->where('type', 'slot_has_bulb')
					->exists()
			) {
				SlotHasBulb::clearReservationBulbActions($reservation);
				$numerable->slot->changeBulbStatus(BulbStatus::OFF, BulbReason::MANUAL);
			}
		} else {
			$price = $numerable->price - $numerable->club_commission;
			$updatableSlotIds = $allReservationSlots->pluck('id')->toArray();
		}

		BulbAction::where('assigned_to_type', (new Reservation())->getMorphClass())
			->where('assigned_to_id', $reservation->id)
			->update([
				'run_at' => null,
				'deleted_at' => now(),
			]);

		// add commission to refund if all reservation slots will be returned
		if (count($updatableSlotIds) === count($allReservationSlots->whereNull('refund_id'))) {
			$price += $reservation->club_commission;
		}

		if ($cancelationType !== ReservationSlotCancelationType::System) {
			$reservation->customer?->notify(
				new \App\Notifications\Customer\ReservationCanceledNotification($this, $this->canBeRefunded())
			);
		}

		$refund = null;
		$paymentMethod = PaymentMethod::getPaymentMethod($reservation->payment_method_id);
		if ($paymentMethod->online === true) {
			$refund = Refund::create([
				'status' => $this->canBeRefunded() ? RefundStatus::Pending : RefundStatus::Rejected,
				'price' => $price,
			]);
		}

		$updateArray = [
			'cancelation_type' => $cancelationType,
			'cancelation_reason' => $cancelationReason,
			'canceler_id' => auth()->user()->id ?? null,
			'canceled_at' => now('UTC'),
			'presence' => $presence,
			'refund_id' => $refund?->id,
		];

		if ($status) {
			$updateArray['status'] = $status;
		}

        foreach ($updatableSlotIds as $slotId) {
            $slot = ReservationSlot::find($slotId);
            $slot->fill($updateArray);
            $slot->save();
        }
		$club = $this->numerable->getClub();

		if ($refund) {
			$refund->refreshHelperColumns();
		}

		event(new CalendarDataChanged($club));

		if ($cancelationType === ReservationSlotCancelationType::Customer) {
			$gameId = $reservation->game_id;
			$managerEmails = $club
				?->managerEmails()
				->where('game_id', $gameId)
				->pluck('email')
				->toArray();
			foreach ($managerEmails as $email) {
				$notifiable = (new AnonymousNotifiable())->route('mail', $email);
				$notifiable->notify(new ReservationCanceledNotification($this));
			}
		}

		return true;
	}

	protected function reservationSlots(): Attribute
	{
		return Attribute::make(get: fn() => $this->numerable?->reservationSlots);
	}

	protected function googleCalendarLink(): Attribute
	{
		return Attribute::make(
			get: function () {
				$numerable = $this->numerable;
				$club = $numerable->reservation->firstReservationSlot->slot->pricelist->club;
				$gameNames = Translation::retrieveGameNames($club);

				$eventData = [
					'text' => __('reservation.calendar-reservation-title', [
						'club_name' => $club->name,
						'reservation_number' => $numerable->number,
						'game_name' => $gameNames[$numerable->reservation->game_id],
					]),
					'dates' =>
						$numerable->firstReservationSlot->start_at->format('Ymd\THis\Z') .
						'/' .
						$numerable->firstReservationSlot->end_at->format('Ymd\THis\Z'),
					'location' => $club->address . ', ' . $club->postal_code . ' ' . $club->city,
					'details' => __('reservation.calendar-reservation-details', [
						'club_name' => $club->name,
						'reservation_number' => $numerable->number,
						'game_name' => $gameNames[$numerable->reservation->game_id],
					]),
					'sf' => 'true',
					'output' => 'xml',
				];

				$url = 'https://www.google.com/calendar/render?action=TEMPLATE';
				foreach ($eventData as $key => $value) {
					$url .= '&' . $key . '=' . urlencode($value);
				}

				return $url;
			}
		);
	}

	public function isCanceled(): bool
	{
		return $this->numerable->firstReservationSlot->cancelation_type !== null;
	}

	public function canBeRefunded(): bool
	{
		$firstReservationSlot =
			$this->numerable_type === ReservationSlot::class
				? $this->numerable
				: $this->numerable->firstReservationSlot;
		$pricelist = Pricelist::getPricelist($this->numerable->firstReservationSlot->slot->pricelist_id);
		$clubId = $pricelist->club_id;
		$refundTimeLimit = Setting::getClubGameSetting($clubId, 'refund_time_limit')['value'];
		$startAt = $firstReservationSlot->getOriginal('start_at');

		// cancellation of the reservation is not possible if part of it has already been canceled
		return match (true) {
			$firstReservationSlot->cancelation_type => false,
			now('UTC')
				->addHours($refundTimeLimit)
				->gt($startAt)
				=> false,
			default => true,
		};
	}
}
