<?php

namespace App\Http\Requests\Club;

use App\Custom\Timezone;
use App\Enums\ReservationSlotStatus;
use App\Models\Game;
use App\Models\OpeningHoursException;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOpeningHoursExceptionRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return match (true) {
			!auth()
				->user()
				->isType(['admin', 'manager'])
				=> false,
			$this->route('opening_hours_exception')->club_id !== clubId() => false,
			default => true,
		};
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function prepareForValidation(): void
	{
		$this->merge([
			'club_end' => $this->club_end === '24:00' ? '23:59' : $this->club_end,
			'reservation_end' => $this->reservation_end === '24:00' ? '23:59' : $this->reservation_end,
		]);
	}

	private function parseTime(string $time): Carbon
	{
		return now()
			->hours(substr($time, 0, 2))
			->minute(0)
			->seconds(0)
			->milliseconds(0);
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
		return [
			'start_at' => [
				'date',
				function ($attribute, $value, $fail) {
					$startAt = request()->get('start_at');
					$endAt = request()->get('end_at');
					if (
						OpeningHoursException::where(function ($query) use ($startAt, $endAt) {
							$query
								->where(function ($query) use ($startAt, $endAt) {
									// Scenario 1: An existing record completely overlaps the new range.
									$query->where('start_at', '<=', $startAt)->where('end_at', '>=', $endAt);
								})
								->orWhere(function ($query) use ($startAt, $endAt) {
									// Scenario 2: The new range completely overlaps an existing record.
									$query->where('start_at', '>=', $startAt)->where('end_at', '<=', $endAt);
								})
								->orWhere(function ($query) use ($startAt, $endAt) {
									// Scenario 3: The new range starts in the middle of an existing record.
									$query
										->where('start_at', '<=', $startAt)
										->where('end_at', '>=', $startAt)
										->where('end_at', '<=', $endAt);
								})
								->orWhere(function ($query) use ($startAt, $endAt) {
									// Scenario 4: The new range ends in the middle of an existing record.
									$query
										->where('start_at', '>=', $startAt)
										->where('start_at', '<=', $endAt)
										->where('end_at', '>=', $endAt);
								});
						})
							->where('id', '!=', $this->route('opening_hours_exception')->id)
							->where('club_id', clubId())
							->exists()
					) {
						$fail(__('opening-hours-exception.store-exception-overlapped-error'));
					}
				},
			],
			'end_at' => 'date|after_or_equal:start_at',
			'club_start' => [
				'regex:/^([0-1][0-9]:[0-5][0-9])|(2[0-3]:[0-5][0-9])|24:00/',
				function ($attribute, $value, $fail) use ($gamesWithoutFullDayReservationsFeature) {
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
									now()
								)
								->whereDate('start_at', '>=', $this->get('start_at'))
								->whereDate('end_at', '<=', $this->get('end_at'))
								->whereIn('status', [
									ReservationSlotStatus::Confirmed,
									ReservationSlotStatus::Pending,
								])
								->whereTime('start_at', '<', Timezone::convertFromLocal($value))
								->whereNull('canceled_at')
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
								->whereDate('start_at', '>=', $this->get('start_at'))
								->whereDate('end_at', '<=', $this->get('end_at'))
								->whereIn('status', [
									ReservationSlotStatus::Confirmed,
									ReservationSlotStatus::Pending,
								])
								->whereTime('start_at', '<', Timezone::convertFromLocal($value))
								->whereNull('canceled_at')
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
									->whereDate('start_at', '>=', $this->get('start_at'))
									->whereDate('end_at', '<=', $this->get('end_at'))
									->whereIn('status', [
										ReservationSlotStatus::Confirmed,
										ReservationSlotStatus::Pending,
									])
									->whereTime('start_at', '<', Timezone::convertFromLocal($value))
									->whereNull('canceled_at')
									->exists()))
					) {
						$fail(__('opening-hours.future-reservations-before-new-opening-hour'));
					}
				},
			],
			'club_end' => [
				'regex:/^([0-1][0-9]:[0-5][0-9])|(2[0-3]:[0-5][0-9])|24:00/',
				function ($attribute, $value, $fail) use ($gamesWithoutFullDayReservationsFeature) {
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
							->whereDate('start_at', '>=', $this->get('start_at'))
							->whereDate('end_at', '<=', $this->get('end_at'))
							->whereDate('start_at', '>=', now())
							->whereIn('status', [
								ReservationSlotStatus::Confirmed,
								ReservationSlotStatus::Pending,
							])
							->whereTime('end_at', '>', Timezone::convertFromLocal($value))
							->whereNull('canceled_at')
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
					if ($this->parseTime(request()->get('club_start'))->gt($this->parseTime($value))) {
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
					if ($reservationEnd->lt($clubStart) || $reservationEnd->gt($clubEnd)) {
						$fail(__('opening-hours.reservation-hours-not-in-opening-hours'));
					}
				},
			],
			'reservation_closed' => 'required|boolean',
		];
	}
}
