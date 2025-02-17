<?php

namespace App\Http\Requests\Club;

use App\Custom\Timezone;
use App\Models\Club;
use App\Models\Game;
use App\Models\Pricelist;
use App\Models\Reservation;
use App\Models\ReservationNumber;
use App\Models\Setting;
use App\Models\Slot;
use App\Models\Translation;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReservationRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return true;
	}

	// pass date values from one carbon object to another

	public function prepareForValidation(): void
	{
		$price = null;
		if ($this->all()['custom_price'] ?? false) {
			$price = amountToSmallestUnits($this->all()['price']);
		}
		$this->merge([
			'price' => $price,
		]);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(ReservationNumber $reservationNumber): array
	{
		$club = Club::getClub(request()->all()['club_id']);
		$game = $reservationNumber->numerable->game;
		$calendarTimeScale = Setting::getClubGameSetting(clubId(), 'calendar_time_scale')['value'];
		$slot = Slot::getSlot(request()->all()['slot_id'] ?? 0);
		$startAt = now()->parse(request()->get('start_at'));
		$endAt = $startAt->clone()->addMinutes(request()->get('duration'));
		$gameTranslations = Translation::gamesTranslationsArray()[$game->id];
		$timerEntries = $reservationNumber->numerable->timerEntries ?? null;
		$hasEnabledTimer = false;
		if ($timerEntries) {
			if (count($timerEntries) && count($timerEntries->where('stopped', true)) === 0) {
				$hasEnabledTimer = true;
			}
		}

		$vacantSlots = Slot::getAvailable(
			array_merge(request()->all(), [
				'club_id' => $club->id,
				'vacant' => true,
				'active' => true,
				'include_slot_ids' => [],
				'reservation_slot_to_exclude' => /*$reservationNumber->numerable->reservation->reservationSlots()->pluck('id')->toArray()*/ [
					$reservationNumber->numerable?->id,
				],
			])
		);

		$durationRules = ['nullable', 'integer', 'max:1440'];
        $fixedReservationDurationStatus = Setting::getClubGameSetting(clubId(), 'fixed_reservation_duration_status')['value'] ?? false;
		if (!$hasEnabledTimer && !$fixedReservationDurationStatus) {
			$durationRules[] = "multiple_of:$calendarTimeScale";
			$durationRules[] = "min:$calendarTimeScale";
		}

		return [
			'customer.first_name' => [Rule::requiredIf(request()->get('anonymous_reservation') === false)],
			'customer.email' => [
				'nullable',
				Rule::unique('customers', 'email')->where(
					fn($query) => $query
						->where(function ($query) {
							$query
								->where('first_name', '!=', request()->get('customer')['first_name'] ?? '')
								->orWhere('last_name', '!=', request()->get('customer')['last_name'] ?? '')
								->orWhere('phone', '!=', request()->get('customer')['phone'] ?? '');
						})
						->where('club_id', clubId())
						->where('email', '!=', request()->all()['customer']['email'] ?? '')
				),
			],
			'customer.phone' => [Rule::requiredIf(request()->get('anonymous_reservation') === false)],
			'duration' => $durationRules,
			'price' => [
                'min:0',
                'max:1000000'
            ],
			'start_at' => [
				'required',
				function ($attribute, $value, $fail) use ($game, $club, $startAt, $endAt) {
					if (!$club->isDatetimeRangeWithinOpeningHours($startAt, $endAt)) {
						$fail(__('reservation.the-club-is-closed-during-these-hours'));
					}
				},
				function ($attribute, $value, $fail) use ($calendarTimeScale, $hasEnabledTimer) {
					if (!$hasEnabledTimer && now()->parse($value)->minute % $calendarTimeScale) {
						$fail('The minute of the start time is not a multiple of the calendar time scale');
					}
				},
			],
			'slot_id' => [
				Rule::requiredIf(static fn() => !$game->hasFeature('person_as_slot')),
				function ($attribute, $value, $fail) use ($slot, $game) {
					if ($game->hasFeature('person_as_slot')) {
						return true;
					}
					$feature = $slot
						->features()
						->where('type', 'slot_has_active_status_per_weekday')
						->first();
					if ($feature) {
						$pivotData = json_decode($feature->pivot->data, true, 512, JSON_THROW_ON_ERROR);
						$weekDay = weekDay(request()->get('start_at'));
						if ($pivotData['active'][$weekDay - 1] === false) {
							$fail(
								Translation::retrieve(club()->country_id, $slot->game_id, $feature->id)[
									'slot-not-active-validation-error'
								]
							);
						}
					}
				},
				function ($attribute, $value, $fail) use (
					$reservationNumber,
					$gameTranslations,
					$slot,
					$game,
					$vacantSlots,
					$club
				) {
					if ($game->hasFeature('person_as_slot')) {
						return true;
					}
					if (
						request()->has('apply_to_all_reservations') &&
						request()->get('apply_to_all_reservations')
					) {
						$groupReservationReservationSlotsIds = $reservationNumber->numerable->reservation
							->reservationSlots()
							->pluck('id')
							->toArray();
						$pricelistVacantSlots = Slot::getAvailable(
							array_merge(request()->all(), [
								'club_id' => $club->id,
								'vacant' => true,
								'active' => true,
								'include_slot_ids' => [],
								'reservation_slot_to_exclude' => $groupReservationReservationSlotsIds,
								'pricelist_id' => $slot->pricelist_id,
							])
						);
						if (!count($vacantSlots->where('id', $slot->id))) {
							$fail(
								Translation::retrieve(club()->country->id, $game->id)[
									'the-slot-is-occupied-at-the-given-time'
								]
							);
						}
						if (count($pricelistVacantSlots) < count($groupReservationReservationSlotsIds)) {
							$fail($gameTranslations['not-enough-vacant-slots']);
						}
					} else {
						$pricelist = Pricelist::getPricelist($slot->pricelist_id);
						$game = Game::getCached()->find($pricelist->game_id);
						if (!count($vacantSlots->where('id', $slot->id))) {
							$fail(
								Translation::retrieve(club()->country->id, $game->id)[
									'the-slot-is-occupied-at-the-given-time'
								]
							);
						}
					}
				},
			],
			'discount_code_id' => [
				'nullable',
				Rule::exists('discount_codes', 'id')
					->where('club_id', clubId())
					->where('active', true)
					->where('game_id', $game->id),
			],
			'special_offer_id' => [
				'nullable',
				Rule::exists('special_offers', 'id')
					->where('club_id', clubId())
					->where('game_id', $game->id),
			],
			'occupied_status' => ['boolean'],
			'reservation_type_id' => [
				'nullable',
				Rule::exists('reservation_types', 'id')->where('club_id', clubId()),
			],
			'customer_note' => 'nullable|string|max:10000',
			'club_note' => 'nullable|string|max:10000',
			'apply_to_all_reservations' => [
				Rule::requiredIf(fn() => $reservationNumber->numerable_type === Reservation::class),
				'boolean',
			],
			'club_reservation' => ['required', 'boolean'],
		];
	}

	public function setDateFromInstance($date, $fromDate): Carbon
	{
		return $date
			->day($fromDate->day)
			->month($fromDate->month)
			->year($fromDate->year);
	}
}
