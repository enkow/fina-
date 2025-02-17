<?php

namespace App\Http\Requests\Club;

use App\Custom\Timezone;
use App\Enums\ReservationSlotStatus;
use App\Models\Game;
use App\Models\OpeningHours;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOpeningHoursRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return auth()
			->user()
			->isType(['admin', 'manager']);
	}

	public function prepareForValidation(): void
	{
		$this->merge([
			'club_end' => $this->club_end === '24:00' ? '23:59' : $this->club_end,
			'reservation_end' => $this->reservation_end === '24:00' ? '23:59' : $this->reservation_end,
		]);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		$gamesWithoutFullDayReservationsFeature = Game::whereDoesntHave('features', function ($query) {
			$query->where('type', 'full_day_reservations');
		})->get();

		$openingHour = club()
			->openingHours()
			->where('day', request()->get('day'))
			->first();

		return [
			'day' => 'integer|min:1|max:7',
			'club_start' => [
				'regex:/^([0-1][0-9]:[0-5][0-9])|(2[0-3]:[0-5][0-9])|24:00/',
				function ($attribute, $value, $fail) use (
					$gamesWithoutFullDayReservationsFeature,
					$openingHour
				) {
					if (
						($this->parseTime(request()->get('club_start'))->lt(
							$this->parseTime(request()->get('club_end'))
						) &&
							club()
								->reservationSlots()
								->whereHas('reservation', function ($query) use (
									$gamesWithoutFullDayReservationsFeature
								) {
									$query->whereIn(
										'game_id',
										$gamesWithoutFullDayReservationsFeature->pluck('id')
									);
								})
								->whereDate(
									'start_at',
									now()->gt($this->parseTime($value)) ? '>' : '>=',
									now()->format('Y-m-d')
								)
								->whereRaw(
									'WEEKDAY(reservation_slots.start_at) =' . ($this->all()['day'] - 1)
								)
								->whereIn('status', [
									ReservationSlotStatus::Confirmed,
									ReservationSlotStatus::Pending,
								])
								->where(function ($query) {
									$targetDay = $this->all()['day'];
									$previousDay = $targetDay == 1 ? 0 : $targetDay - 1;
									$carbonInstance = now();
									if ($carbonInstance->dayOfWeekIso <= $previousDay) {
										$carbonInstance->previous($previousDay);
									} else {
										$carbonInstance->next($previousDay)->subWeek();
									}
									$previousDayOpeningHours = club()->getOpeningHoursForDate(
										$carbonInstance
									);
									if (
										$previousDayOpeningHours['club_end'] <
										$previousDayOpeningHours['club_start']
									) {
										$query->whereTime(
											'start_at',
											'>',
											Timezone::convertFromLocal($previousDayOpeningHours['club_start'])
										);
									}
								})
								->whereNull('canceled_at')
								->whereTime('start_at', '<', Timezone::convertFromLocal($value))
								->whereTime(
									'start_at',
									'>=',
									Timezone::convertFromLocal($openingHour->club_start)
								)
								->exists()) ||
						($this->parseTime(request()->get('club_start'))->gt(
							$this->parseTime(request()->get('club_end'))
						) &&
							(club()
								->reservationSlots()
								->whereHas('reservation', function ($query) use (
									$gamesWithoutFullDayReservationsFeature
								) {
									$query->whereIn(
										'game_id',
										$gamesWithoutFullDayReservationsFeature->pluck('id')
									);
								})
								->whereDate(
									'start_at',
									now()->gt($this->parseTime($value)) ? '>' : '>=',
									now()
								)
								->whereRaw(
									'WEEKDAY(reservation_slots.start_at) = ' . ($this->all()['day'] - 1)
								)
								->whereIn('status', [
									ReservationSlotStatus::Confirmed,
									ReservationSlotStatus::Pending,
								])
								->whereNull('canceled_at')
								->whereTime('start_at', '<', Timezone::convertFromLocal($value))
								->whereTime('start_at', '>=', $openingHour->club_start)
								->exists() ||
								club()
									->reservationSlots()
									->whereHas('reservation', function ($query) use (
										$gamesWithoutFullDayReservationsFeature
									) {
										$query->whereIn(
											'game_id',
											$gamesWithoutFullDayReservationsFeature->pluck('id')
										);
									})
									->whereDate(
										'start_at',
										now()->gt($this->parseTime($value)) ? '>' : '>=',
										now()
									)
									->whereRaw(
										'WEEKDAY(reservation_slots.start_at) = ' . ($this->all()['day'] - 1)
									)
									->whereIn('status', [
										ReservationSlotStatus::Confirmed,
										ReservationSlotStatus::Pending,
									])
									->whereNull('canceled_at')
									->whereTime('start_at', '<', Timezone::convertFromLocal($value))
									->whereTime(
										'start_at',
										'>=',
										Timezone::convertFromLocal($openingHour->club_start)
									)
									->exists()))
					) {
						if (
							Timezone::convertFromLocal($openingHour->club_start)->lt(
								Timezone::convertFromLocal($value)
							)
						) {
							$fail(__('opening-hours.future-reservations-before-new-opening-hour'));
						}
					}
				},
			],
			'club_end' => [
				'regex:/^([0-1][0-9]:[0-5][0-9])|(2[0-3]:[0-5][0-9])|24:00/',
				function ($attribute, $value, $fail) use (
					$gamesWithoutFullDayReservationsFeature,
					$openingHour
				) {
					if (
						$this->parseTime(request()->get('club_start'))->lt(
							$this->parseTime(request()->get('club_end'))
						) &&
						club()
							->reservationSlots()
							->whereHas('reservation', function ($query) use (
								$gamesWithoutFullDayReservationsFeature
							) {
								$query->whereIn(
									'game_id',
									$gamesWithoutFullDayReservationsFeature->pluck('id')
								);
							})
							->whereRaw('WEEKDAY(reservation_slots.start_at) = ' . ($this->all()['day'] - 1))
							->whereDate('start_at', '>=', now())
							->whereIn('status', [
								ReservationSlotStatus::Confirmed,
								ReservationSlotStatus::Pending,
							])
							->whereNull('canceled_at')
							->whereTime('end_at', '<', Timezone::convertFromLocal($openingHour->club_start))
							->whereTime('end_at', '>', Timezone::convertFromLocal($value))
							->exists()
					) {
						$fail(__('opening-hours.future-reservations-after-new-closing-hour'));
					}
				},
			],
			'club_closed' => 'required|boolean',
			'reservation_start' => [
				'regex:/^([0-1][0-9]:[0-5][0-9])|(2[0-3]:[0-5][0-9])|24:00/',

				function ($attribute, $value, $fail) {
					if (
						!request()->get('reservation_closed') &&
						$this->parseTime(request()->get('club_start'))->gt($this->parseTime($value))
					) {
						$fail(__('opening-hours.reservation-hours-not-in-opening-hours'));
					}
				},
			],
			'reservation_end' => [
				'regex:/^([0-1][0-9]:[0-5][0-9])|(2[0-3]:[0-5][0-9])|24:00/',
				function ($attribute, $value, $fail) {
					$clubStart = $this->parseTime(request()->get('club_start'));
					$clubEnd = $this->parseTime(request()->get('club_end'));
					if ($clubEnd->lt($clubStart)) {
						$clubEnd->addDay();
					}

					$reservationStart = $this->parseTime(request()->get('reservation_start'));
					$reservationEnd = $this->parseTime(request()->get('reservation_end'));
					if ($reservationEnd->lt($reservationStart)) {
						$reservationEnd->addDay();
					}
					if (
						!request()->get('reservation_closed') &&
						($reservationEnd->lt($clubStart) || $reservationEnd->gt($clubEnd))
					) {
						$fail(__('opening-hours.reservation-hours-not-in-opening-hours'));
					}
				},
			],
			'reservation_closed' => 'required|boolean',
		];
	}

	private function parseTime(string $time): Carbon
	{
		return now('UTC')
			->hours(substr($time, 0, 2))
			->minute(0)
			->seconds(0)
			->milliseconds(0);
	}
}
