<?php

namespace App\Notifications\Customer;

use App\Custom\Timezone;
use App\Enums\ReminderMethod;
use App\Enums\ReminderType;
use App\Enums\ReservationSource;
use App\Models\Country;
use App\Models\Reservation;
use App\Models\ReservationNumber;
use App\Models\Translation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReservationUpdateNotification extends Notification implements ShouldQueue
{
	use Queueable;
	public $reminders = [];

	public function __construct(protected ReservationNumber $reservationNumber)
	{
	}

	public function via(mixed $notifiable): array
	{
		$reservation = $this->reservationNumber->numerable->reservation;
		$reminders = $this->reservationNumber->numerable->reservation
			->reminders()
			->where('type', '=', ReminderType::NewReservation)
			->get();
		$resultArray = [];

		foreach ($reminders as $reminder) {
			$real = $reminder->real;
			if ($reminder->method === ReminderMethod::Mail) {
				$real = false;
			}
			$club = $reservation->firstReservationSlot->slot->pricelist->club;
			$smsActive =
				($reservation->source === ReservationSource::Widget && $club->sms_notifications_online) ||
				($reservation->source === ReservationSource::Panel && $club->sms_notifications_offline);

			if ($reminder->method === ReminderMethod::Sms && !$smsActive) {
				$real = false;
			}

			$this->reminders[$reminder->method->value] = $this->reservationNumber->numerable->reservation
				->reminders()
				->create([
					'method' => $reminder->method,
					'type' => ReminderType::UpdateReservation,
					'real' => $real,
				]);

			if ($real) {
				$resultArray[] = $reminder->method->value;
			} else {
				$this->afterSms(['system' => 'SMS notifications off']);
			}
		}

		return $resultArray;
	}

	public function toSms()
	{
		$numerable = $this->reservationNumber->numerable;
		$reservation = $numerable->reservation;
        $reservation = Reservation::find($reservation->id);
		$firstReservationSlot = $reservation->firstReservationSlot;
		date_default_timezone_set($firstReservationSlot->slot->pricelist->club->country->timezone);
		app()->setLocale($reservation->locale);

		$country = Country::where('locale', $reservation->locale)
			->where('active', true)
			->first();
		$gameNames = Translation::retrieveGameNames($reservation->club, $country->id);

		$localStartAt = Timezone::convertToLocal($firstReservationSlot->getOriginal('start_at'));
		$data = [
			'number' => $this->reservationNumber->formatted_number,
			'game' => $gameNames[$numerable->reservation->game_id],
			'day' => $localStartAt->format('d.m.Y'),
			'time' => $localStartAt->format('H:i'),
			'payment_status' => $firstReservationSlot->statusLocale(),
			'price' => $reservation->formattedPrice,
		];

		return __('reservation.sms.updated', $data, $reservation->locale);
	}

	public function afterSms(mixed $result)
	{
		if (isset($this->reminders[ReminderMethod::Sms->value])) {
			$this->reminders[ReminderMethod::Sms->value]->data = $result;
			$this->reminders[ReminderMethod::Sms->value]->save();
		}
	}

	public function toMail(mixed $notifiable)
	{
	}
}
