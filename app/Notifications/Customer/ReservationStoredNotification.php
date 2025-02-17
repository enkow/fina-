<?php

namespace App\Notifications\Customer;

use App\Custom\Timezone;
use App\Enums\ReminderMethod;
use App\Enums\ReminderType;
use App\Enums\ReservationSource;
use App\Models\Club;
use App\Models\Country;
use App\Models\Pricelist;
use App\Models\Reservation;
use App\Models\ReservationNumber;
use App\Models\ReservationSlot;
use App\Models\Slot;
use App\Models\Setting;
use App\Models\TablePreference;
use App\Models\Translation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;

class ReservationStoredNotification extends Notification implements ShouldQueue
{
	use Queueable;

	public $reminders = [];

	public function __construct(
		protected ReservationNumber $reservationNumber,
		private readonly array $forceMethods = [],
		private readonly array $excludeMethods = [],
		private readonly array $preloadedData = []
	) {
	}

	public function via(mixed $notifiable): array
	{
		$resultArray = [];
		$reservation = (
			$this->preloadedData['reservation'] ?? $this->reservationNumber->numerable->reservation ?? $this->reservationNumber->numerable
		)->load('reminders');
		$club = $this->preloadedData['club'] ?? $reservation->firstReservationSlot->slot->pricelist->club;
		foreach ([ReminderMethod::Sms, ReminderMethod::Mail] as $reminderMethod) {
			$reminderMethodEntryExists = $reservation->reminders
				->where('method', $reminderMethod)
				->where('type', ReminderType::NewReservation)
				->first();

			if (!empty($reminderMethodEntryExists)) {
				$this->reminders[$reminderMethod->value] = $reminderMethodEntryExists;
				continue;
			}

			$real = $reminderMethod === ReminderMethod::Mail;

			if (
				$reminderMethod === ReminderMethod::Sms &&
				(($reservation->source === ReservationSource::Widget && $club->sms_notifications_online) ||
					($reservation->source === ReservationSource::Panel && $club->sms_notifications_offline))
			) {
				$real = true;
			}

			if ($real) {
				$real = !in_array($reminderMethod->value, $this->excludeMethods);
			}

			$this->reminders[$reminderMethod->value] = $reservation->reminders()->create([
				'method' => $reminderMethod,
				'type' => ReminderType::NewReservation,
				'real' => $real,
			]);

			if ($real) {
				$resultArray[] = $reminderMethod->value;
			} else {
				$this->afterSms(['system' => 'SMS notifications off']);
			}
		}

		foreach ($this->forceMethods as $forceMethod) {
			if (!in_array($forceMethod, $resultArray)) {
				$resultArray[] = $forceMethod;

				$this->reminders[$forceMethod] = $reservation->reminders()->create([
					'method' => $forceMethod,
					'type' => ReminderType::NewReservation,
					'real' => true,
				]);
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

		return __('reservation.sms.stored', $data, $reservation->locale);
	}

	public function afterSms(mixed $result)
	{
		$this->reminders[ReminderMethod::Sms->value]->data = $result;
		$this->reminders[ReminderMethod::Sms->value]->save();
	}

	public function toMail(mixed $notifiable): MailMessage
	{
		app()->setLocale($this->reservationNumber->numerable->reservation->locale);
		$reservationData = TablePreference::getDataArrayFromModel(
			$this->reservationNumber
				->numerable()
				->first()
				->prepareForOutput(true),
			array_merge(
				Reservation::tableData(gameId: $this->reservationNumber->numerable->reservation->game_id)[
					'preference'
				],
				[['key' => 'extended', 'enabled' => true]]
			)
		);
		$reservationData['country_id'] = Country::where(
			'locale',
			$this->reservationNumber->numerable->reservation->locale
		)
			->where('active', true)
			->first()->id;
		$club = $this->reservationNumber->numerable->firstReservationSlot->slot->pricelist->club;
		$gameTranslations = Translation::retrieve(
			$reservationData['country_id'] ?? $club->country_id,
			$this->reservationNumber->numerable->game->id
		);

		return (new MailMessage())
			->subject(config('app.name') . ' - ' . __('reservation.new-reservation'))
			->markdown('notifications::email', [
				'greeting' => $gameTranslations['reservation-confirmed-customer-notification-greeting'],
				'markdown' => false,
				'level' => 'primary',
				'reservationData' => $reservationData,
				'outroLines' => [
					'<a href="' .
					$this->reservationNumber->google_calendar_link .
					'">' .
					__('reservation.add-reservation-to-google-calendar') .
					'</a><br><br>',
					'<a href="' .
					route('widget.customers.reservations.cancel', [
						'club' => $club,
						'encryptedCustomerId' => Crypt::encrypt($notifiable->id ?? null),
						'reservationNumber' => $this->reservationNumber->id,
					]) .
					'">' .
					__('reservation.cancel-reservation') .
					'</a>',
				],
			]);
	}
}
