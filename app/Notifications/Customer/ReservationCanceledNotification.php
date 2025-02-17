<?php

namespace App\Notifications\Customer;

use App\Custom\Timezone;
use App\Enums\ReminderMethod;
use App\Enums\ReminderType;
use App\Enums\ReservationSlotCancelationType;
use App\Enums\ReservationSource;
use App\Models\Country;
use App\Models\Reservation;
use App\Models\ReservationNumber;
use App\Models\TablePreference;
use App\Models\Translation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCanceledNotification extends Notification implements ShouldQueue
{
	use Queueable;
	public $reminders = [];

	public function __construct(
		protected ReservationNumber $reservationNumber,
		protected bool $refunded = false
	) {
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
					'type' => ReminderType::CancelReservation,
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

		return __('reservation.sms.canceled', $data, $reservation->locale);
	}

	public function afterSms(mixed $result)
	{
		if (isset($this->reminders[ReminderMethod::Sms->value])) {
			$this->reminders[ReminderMethod::Sms->value]->data = $result;
			$this->reminders[ReminderMethod::Sms->value]->save();
		}
	}

	public function toMail(mixed $notifiable): MailMessage
	{
		$reservation = $this->reservationNumber->numerable->reservation;
		$firstReservationSlot = $this->reservationNumber->numerable->firstReservationSlot;
		$club = $firstReservationSlot->slot->pricelist->club;
		app()->setLocale($reservation->locale);
		$reservationData['country_id'] = Country::where('locale', $reservation->locale)
			->where('active', true)
			->first()->id;
		$reservationData = TablePreference::getDataArrayFromModel(
			$this->reservationNumber
				->numerable()
				->first()
				->prepareForOutput(true),
			array_merge(Reservation::tableData(gameId: $reservation->game_id)['preference'], [
				['key' => 'extended', 'enabled' => true],
			])
		);

		if ($firstReservationSlot->cancelation_type === ReservationSlotCancelationType::Club) {
			$reservationData['cancelation_reason'] = $firstReservationSlot->cancelation_reason;
		}

		$outroLines = [];
		if ($firstReservationSlot->cancelation_type === ReservationSlotCancelationType::Club) {
			$outroLines[] = __('reservation.contact-with-club-mail');
		}

		$emailData = [
			'greeting' => __('reservation.reservation-canceled'),
			'markdown' => false,
			'level' => 'primary',
			'reservationData' => $reservationData,
			'outroLines' => $outroLines,
		];
		if ($reservationData['payment_method_online']) {
			$emailData['afterGreeting'] = match ($this->refunded) {
				true => __('reservation.canceled-notification.refunded-info'),
				false => __('reservation.canceled-notification.not-refunded-info'),
			};
		}

		return (new MailMessage())
			->subject(config('app.name') . ' - ' . __('reservation.reservation-canceled'))
			->markdown('notifications::email', $emailData);
	}
}
