<?php

namespace App\Http\Requests\Club;

use App\Custom\Timezone;
use App\Enums\ReservationSlotStatus;
use App\Models\Club;
use App\Models\Customer;
use App\Models\DiscountCode;
use App\Models\Game;
use App\Models\Setting;
use App\Models\Slot;
use App\Models\Translation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
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

	public function prepareForValidation(): void
	{
		$club = request()->route('club') ?? club();
		$arrayToMerge = [];
		if ($club->customer_registration_required === false) {
			$arrayToMerge['anonymous_reservation'] = true;
		} elseif (request()->routeIs('widget.*') && session()->has('customer_id')) {
			$customer = Customer::getCustomer(session()->get('customer_id'));
			$arrayToMerge['customer'] = [
				'first_name' => $customer->first_name,
				'last_name' => $customer->last_name,
				'phone' => $customer->phone,
				'email' => $customer->email,
				'locale' => $customer->locale,
			];
		}

		$discountCodeId = $this->get('discount_code_id');
		if ($discountCodeId === null && $this->get('discount_code', null)) {
			$discountCodeId = DiscountCode::where('code', $this->get('discount_code', null))
				->where('game_id', $this->get('game_id'))
				->first()?->id;
		}
		$arrayToMerge['discount_code_id'] = $discountCodeId;

		// if reservation is made from widget, its status should be always set to pending at the beggining
		if (request()->routeIs('widget.*')) {
			$arrayToMerge['status'] = ReservationSlotStatus::Pending->value;
		}
		$arrayToMerge['price'] = isset($arrayToMerge['price']) ? round($arrayToMerge['price']) : 0;

		request()->merge($arrayToMerge);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		$club = Club::getClub(request()->all()['club_id'] ?? 0) ?? (request()->route('club') ?? club());
		$game = request()->route('game');
		$games = Game::getCached();
		if ($game === null && request()->get('game_id', null)) {
			$game = $games->where('id', request()->get('game_id', null))->first();
		} elseif ($game === null) {
			$game = $games->first();
		}
		$startAt = now()->parse(request()->get('start_at'));
		$clubOpeningHours = $club->getOpeningHoursForDate($startAt);
		$duration = request()->get('duration');
		if ($duration === null) {
			$duration =
				Setting::getClubGameSetting($club->id, 'fixed_reservation_duration_value', $game->id)[
					'value'
				] ?? 0;
		}
		$endAt = $startAt->clone()->addMinutes($duration);

		$calendarTimeScale = Setting::getClubGameSetting($club->id, 'calendar_time_scale')['value'];
		$gameTranslations = Translation::gamesTranslationsArray()[$game->id];

		$durationRules = ['nullable', 'integer', 'max:1440'];

		if (
			$game->hasFeature('fixed_reservation_duration') &&
			(int) (Setting::getClubGameSetting($club->id, 'fixed_reservation_duration_value', $game->id)[
				'value'
			] ?? 0) === 24
		) {
			$startAt = applyTimeToCarbon($startAt->clone(), $clubOpeningHours['club_start']);
			$endAt = applyTimeToCarbon($endAt->clone(), $clubOpeningHours['club_end']);
		} else {
			$durationRules = array_merge($durationRules, [
				'min:' . $calendarTimeScale,
				"multiple_of:$calendarTimeScale",
			]);
		}

		$customerEmailRules = ['nullable', 'max:255'];
		if (!session()->has('customer_id') || !request()->routeIs('widget.*')) {
			$customerEmailRules[] = Rule::unique('customers', 'email')->where(
				fn($query) => $query
					->where(function ($query) {
						$query
							->where('first_name', '!=', request()->get('customer')['first_name'])
							->orWhere('last_name', '!=', request()->get('customer')['last_name'])
							->orWhere('phone', '!=', request()->get('customer')['phone']);
					})
					->where('club_id', $club->id)
					->where('email', '!=', request()->all()['customer']['email'] ?? '')
			);
		}
		$customerPhoneRules = [
			Rule::requiredIf(
				request()->get('anonymous_reservation') === false &&
					($club->customer_registration_required === true || (bool) auth()->user())
			),
		];

		$vacantSlots = Slot::getAvailable(
			array_merge(request()->all(), [
				'vacant' => true,
				'active' => true,
				'include_slot_ids' => [],
				'club_id' => $club->id,
			])
		);

		return [
			'customer.first_name' => [
				Rule::requiredIf(
					request()->get('anonymous_reservation') === false &&
						$club->customer_registration_required === true
				),
			],
			'customer.email' => $customerEmailRules,
			'customer.phone' => $customerPhoneRules,
			'start_at' => [
				function ($attribute, $value, $fail) use ($club, $game, $startAt, $endAt, $gameTranslations) {
					if (!$club->isDatetimeRangeWithinOpeningHours($startAt, $endAt)) {
						$fail(__('reservation.the-club-is-closed-during-these-hours'));
					}
				},
			],
			'duration' => $durationRules,
			'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
			'discount_code_id' => [
				'nullable',
				Rule::exists('discount_codes', 'id')
					->where('club_id', $club->id)
					->where('active', true)
					->where('game_id', $game->id),
			],
			'slots_count' => [
				'nullable',
				'integer',
				'min:1',
				function ($attribute, $value, $fail) use ($gameTranslations, $vacantSlots, $game) {
					// person as slot has its own validation rules for that
					if (count($vacantSlots) < $value && !$game->hasFeature('person_as_slot')) {
						$fail($gameTranslations['not-enough-vacant-slots']);
					}
				},
				function ($attribute, $value, $fail) use ($gameTranslations, $game, $club) {
					if (!request()->routeIs('widget.*', 'calendar.*')) {
						return true;
					}
					// if the game has limits on the number of people per reserved slot, we must check whether the end customer does not reserve more slots than he should
					if (
						!$game->hasFeature('price_per_person') ||
						!$game->hasFeature('has_slot_people_limit_settings')
					) {
						return true;
					}
					$pricePerPersonType = Setting::getClubGameSetting(
						$club->id,
						'price_per_person_type',
						$game->id
					)['value'];
					if ($pricePerPersonType === 0) {
						return true;
					}
					$slotPeopleMinLimit = Setting::getClubGameSetting(
						$club->id,
						'slot_people_min_limit',
						$game->id
					)['value'];
					$slotsCount = (int) request()->all()['slots_count'];
					$peopleCount = (int) request()->all()['features'][
						$game->getFeaturesByType('price_per_person')->first()->id
					]['person_count'];
					if ($slotPeopleMinLimit && $slotsCount > $peopleCount / $slotPeopleMinLimit) {
						$fail('slot-people-min-limit-exceeded');
					}
				},
				function ($attribute, $value, $fail) use ($gameTranslations, $vacantSlots) {
					$slotId = request()->all()['slot_id'] ?? null;
					$slotIds = request()->get('slot_ids', null);
					if (
						request()->get('reservation_number_id', null) === null &&
						($slotId && !count($vacantSlots->where('id', $slotId)))
					) {
						$fail(
							str_replace(
								':value',
								Slot::find($slotId)->name,
								$gameTranslations['the-slot-is-occupied-at-the-given-time-value']
							)
						);
					}
					if (
						request()->get('reservation_number_id', null) === null &&
						($slotIds &&
							count(array_filter($slotIds)) &&
							count($vacantSlots->whereIn('id', $slotIds)) !== count($slotIds))
					) {
						foreach ($slotIds as $item) {
							if (!count($vacantSlots->where('id', $item))) {
								$fail(
									str_replace(
										':value',
										Slot::find($item)->name,
										$gameTranslations['the-slot-is-occupied-at-the-given-time-value']
									)
								);
							}
						}
					}
				},
			],
			'occupied_status' => ['boolean'],
			'slot_ids' => [
				Rule::requiredIf(
					static fn() => $game->hasFeature('book_singular_slot_by_capacity') &&
						!request()->routeIs('widget.*')
				),
				function ($attribute, $value, $fail) use ($club) {
					$valueToCheck = array_filter($slotIds ?? []);

					// check if all slot ids belong to the club
					if (
						count($valueToCheck) !==
						$club
							->slots()
							->whereIn('slots.id', $valueToCheck)
							->count()
					) {
						$fail('not-all-slots-belongs-to-the-club');
					}
				},
				'array',
			],
			'slot_ids.*' => [
				Rule::requiredIf(
					static fn() => $game->hasFeature('book_singular_slot_by_capacity') &&
						!request()->routeIs('widget.*')
				),
				function ($attribute, $value, $fail) use ($game, $gameTranslations, $vacantSlots) {
					if (!$game->hasFeature('book_singular_slot_by_capacity')) {
						return true;
					}
					$slot = Slot::find(request()->get('slot_ids')[explode('.', $attribute)[1]]);
					if ($slot && !$vacantSlots->where('id', $slot->id)->first()) {
						$fail(
							str_replace(
								':value',
								$slot->name,
								$gameTranslations['the-slot-is-occupied-at-the-given-time-value']
							)
						);
					}
				},
				function ($attribute, $value, $fail) use ($club, $game, $startAt, $endAt, $gameTranslations) {
					if (!$game->hasFeature('book_singular_slot_by_capacity')) {
						return true;
					}
					$slot = Slot::find(request()->get('slot_ids')[explode('.', $attribute)[1]]);
					if ($slot === null) {
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
								str_replace(
									':value',
									$slot->name,
									Translation::retrieve($club->country_id, $slot->game_id, $feature->id)[
										'slot-not-active-validation-error-value'
									]
								)
							);
						}
					}
				},
			],
			'special_offer_id' => [
				'nullable',
				Rule::exists('special_offers', 'id')
					->where('club_id', $club->id)
					->where('active', true)
					->where('game_id', $game->id),
			],
			'reservation_type_id' => [
				'nullable',
				Rule::exists('reservation_types', 'id')->where('club_id', $club->id),
			],
			'customer_note' => 'nullable|string|max:10000',
			'club_note' => 'nullable|string|max:10000',
			'club_reservation' => ['required', 'boolean'],
			'notification' => 'array|min:2|max:2',
			'notification.sms' => 'required|boolean',
			'notification.mail' => 'required|boolean',
			'payment_type' => 'nullable|in:online,offline',
		];
	}
}
