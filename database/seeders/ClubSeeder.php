<?php

namespace Database\Seeders;

use App\Enums\AgreementContentType;
use App\Enums\AgreementType;
use App\Enums\ReservationSlotCancelationType;
use App\Enums\ReservationSlotStatus;
use App\Models\Club;
use App\Models\Country;
use App\Models\Customer;
use App\Models\DiscountCode;
use App\Models\Features\ParentSlotHasOnlineStatus;
use App\Models\Game;
use App\Models\OpeningHours;
use App\Models\Pricelist;
use App\Models\PricelistItem;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\ReservationType;
use App\Models\Set;
use App\Models\Slot;
use App\Models\SpecialOffer;
use App\Models\Tag;
use App\Models\User;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class ClubSeeder extends Seeder
{
	use WithoutModelEvents;

	private function updateSlotFeatureData($game, $slot, $featureType, $data): void
	{
		if ($game->hasFeature($featureType)) {
			$features = $game->getFeaturesByType($featureType);
			foreach ($features as $feature) {
				$feature->updateSlotData($slot, [
					'features' => [
						$feature->id => $data,
					],
				]);
			}
		}
	}

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function run(): void
	{
		$tagIds = Tag::pluck('id')->toArray();

		Club::factory()
			->sequence([
				'country_id' => Country::where('code', 'PL')->first()->id,
				'fakturownia_id' => '122522055',
			])
			->has(User::factory()->count(10))
			->has(DiscountCode::factory()->count(10))
			->has(
				Pricelist::factory()
					->has(
						Slot::factory()
							->count(2)
							->afterCreating(function ($slot) {
								$game = $slot->pricelist->game;
								if ($game->hasFeature('slot_has_type')) {
									$featureType = $game->getFeaturesByType('slot_has_type')->first();
									$options = $featureType['data']['options'];
									$randomType = $options[array_rand($options)];
									$featureType->updateSlotData($slot, [
										'features' => [
											$featureType->id => ['name' => $randomType],
										],
									]);

									if ($game->hasFeature('slot_has_subtype')) {
										$featureSubtype = $game
											->getFeaturesByType('slot_has_subtype')
											->first();
										$options = array_filter(
											$featureSubtype['data']['options'],
											fn($var) => $var['type'] === $randomType
										);
										$randomSubtype = $options[array_rand($options)];
										$featureSubtype->updateSlotData($slot, [
											'features' => [
												$featureSubtype->id => ['name' => $randomSubtype['name']],
											],
										]);
									}
								}

								if ($game->hasFeature('slot_has_lounge')) {
									$features = $game->getFeaturesByType('slot_has_lounge');
									foreach ($features as $feature) {
										$feature->updateSlotData($slot, [
											'features' => [
												$feature->id => [
													'status' => (bool) random_int(0, 1),
													'min' => random_int(1, 2),
													'max' => random_int(3, 5),
												],
											],
										]);
									}
								}

								if ($game->hasFeature('parent_slot_has_online_status')) {
									$features = $game->getFeaturesByType('parent_slot_has_online_status');
									foreach ($features as $feature) {
										$feature->updateSlotData($slot, [
											'features' => [
												$feature->id => ParentSlotHasOnlineStatus::$defaultSlotData,
											],
										]);
									}
								}

								if ($game->hasFeature('slot_has_convenience')) {
									$features = $game->getFeaturesByType('slot_has_convenience');
									foreach ($features as $feature) {
										$feature->updateSlotData($slot, [
											'features' => [
												$feature->id => [
													'status' => (bool) random_int(0, 1),
												],
											],
										]);
									}
								}
								if (
									$game->hasFeature('slot_has_parent') &&
									$game->hasFeature('person_as_slot') &&
									$game
										->features()
										->where('type', 'person_as_slot')
										->first()['data']['parent_has_capacity_by_week_day']
								) {
									$slotCapacityArray = array_map(
										static fn() => random_int(20, 100) * random_int(1, 3),
										range(1, 7)
									);

									$this->updateSlotFeatureData($game, $slot, 'person_as_slot', [
										'capacity' => $slotCapacityArray,
									]);
								}

								if (
									$game->hasFeature('slot_has_parent') &&
									$game->hasFeature('parent_slot_has_online_status')
								) {
									$this->updateSlotFeatureData(
										$game,
										$slot,
										'parent_slot_has_online_status',
										['status' => random_int(0, 10) > 3]
									);
								}

								if (
									$game->hasFeature('book_singular_slot_by_capacity') &&
									$game->hasFeature('slot_has_parent')
								) {
									$parentSlots = $slot->pricelist->club
										->slots()
										->whereHas('pricelist', function ($query) use ($slot) {
											$query->where('game_id', $slot->pricelist->game->id);
										})
										->whereNull('slot_id')
										->get();
									if (count($parentSlots) > 2) {
										foreach ($parentSlots->take(count($parentSlots) - 2) as $parentSlot) {
											$parentSlot->childrenSlots()->delete();
											$parentSlot->delete();
										}
									}

									$this->updateSlotFeatureData(
										$game,
										$slot,
										'book_singular_slot_by_capacity',
										['capacity' => 5]
									);
									$this->updateSlotFeatureData(
										$game,
										$slot,
										'slot_has_active_status_per_weekday',
										[
											'active' => array_fill(0, 7, true),
										]
									);

									$randomMultipier = random_int(1, 1000);
									foreach (range(1, random_int(5, 30)) as $i) {
										$childSlot = $slot->childrenSlots()->create([
											'name' => $i * $randomMultipier,
											'pricelist_id' => $slot->pricelist_id,
										]);

										$this->updateSlotFeatureData(
											$game,
											$childSlot,
											'book_singular_slot_by_capacity',
											['capacity' => random_int(1, 6)]
										);
										$this->updateSlotFeatureData(
											$game,
											$childSlot,
											'slot_has_active_status_per_weekday',
											[
												'active' => array_fill(0, 7, true),
											]
										);
									}
								}
							})
					)
					->has(
						PricelistItem::factory()
							->sequence(
								['day' => 1],
								['day' => 2],
								['day' => 3],
								['day' => 4],
								['day' => 5],
								['day' => 6],
								['day' => 7]
							)
							->count(7)
					)
					->count(20)
					->afterCreating(function (Pricelist $pricelist) {
						if (
							$pricelist->game->pricelists()->count() > 1 &&
							$pricelist->game
								->features()
								->where('type', 'has_only_one_pricelist')
								->exists()
						) {
							$pricelist->delete();
						}
					})
			)
			->has(ReservationType::factory()->count(6))
			->has(Set::factory()->count(3))
			->has(SpecialOffer::factory()->count(50))
			->has(
				Customer::factory()
					->has(
						Reservation::factory()
							->count(5)
							->afterCreating(function (Reservation $reservation) {
								if (random_int(1, 3) === 1) {
									$reservation->delete();
									return;
								}

								$duration = random_int(1, 3) * 60;
								$start_at = now('UTC')
									->subDays(30)
									->addDays(random_int(0, 60))
									->minutes(0)
									->seconds(0)
									->hours(random_int(10, 16));
								$end_at = $start_at->clone()->addMinutes($duration);
								if ($reservation->game->hasFeature('full_day_reservations')) {
									$start_at = $start_at->hours(0)->subHours(2);
									$end_at = $start_at
										->clone()
										->addDay()
										->hours(21)
										->minutes(59)
										->seconds(59);
								}

								$parentSlot = null;
								if ($reservation->game->hasFeature('slot_has_parent')) {
									$parentSlot = $reservation->customer->club
										->slots()
										->whereHas('pricelist', function ($query) use ($reservation) {
											$query->where('game_id', $reservation->game_id);
										})
										->whereNull('slot_id')
										->inRandomOrder()
										->first();
								}
								$slots = $reservation->customer->club
									->slots()
									->when($parentSlot, function ($query) use ($parentSlot) {
										$query->where('slot_id', $parentSlot->id);
									})
									->whereHas('pricelist', function ($query) use ($reservation) {
										$query->where('game_id', $reservation->game_id);
									})
									->vacant(
										$reservation->customer->club_id,
										$reservation->game->id,
										$start_at->clone(),
										$end_at->clone()
									)
									->inRandomOrder()
									->take(random_int(1, 5))
									->get();

								$discountCodeId =
									random_int(10, 99) % 4 === 0
										? $reservation->customer->club
											->discountCodes()
											->where('game_id', $reservation->game_id)
											->where('active', true)
											->where('start_at', '<=', $start_at)
											->where('end_at', '>=', $start_at)
											->inRandomOrder()
											->first()?->id
										: null;

								$availableSpecialOffers = $specialOffers = [];
								if (random_int(10, 99) % 2 === 0) {
									$specialOffers = $reservation->customer->club
										->specialOffers()
										->where('game_id', $reservation->game_id)
										->where('active', true)
										->get();
								}
								foreach ($specialOffers as $specialOffer) {
									if (
										!in_array(weekDay($start_at), $specialOffer->active_week_days, true)
									) {
										continue;
									}
									if (
										$specialOffer->duration !== null &&
										$specialOffer->duration !== $duration
									) {
										continue;
									}
									if ($specialOffer->applies_default === true) {
										$exclusionApplies = array_filter(
											$specialOffer->when_not_applies,
											static function ($exclusionRange) use (
												$start_at,
												&$continueControl
											) {
												if (
													now()
														->parse($exclusionRange['from'])
														->startOfDay()
														->lte($start_at) &&
													now()
														->parse($exclusionRange['to'])
														->endOfDay()
														->gte($start_at)
												) {
													return true;
												}
											}
										);
										if (count($exclusionApplies)) {
											continue;
										}
									} elseif ($specialOffer->applies_default === false) {
										$inclusionApplies = array_filter(
											$specialOffer->when_not_applies,
											static function ($exclusionRange) use (
												$start_at,
												&$continueControl
											) {
												if (
													now()
														->parse($exclusionRange['from'])
														->startOfDay()
														->lte($start_at) &&
													now()
														->parse($exclusionRange['to'])
														->endOfDay()
														->gte($start_at)
												) {
													return true;
												}
											}
										);
										if (!count($inclusionApplies)) {
											continue;
										}
									}
									$timeRanges = $specialOffer->time_range[$specialOffer->time_range_type];
									$isReservationInSpecialOfferTimeRanges = false;
									foreach ($timeRanges as $key => $timeRange) {
										$startAtMinutesFromMidnight = $start_at
											->clone()
											->diffInMinutes($start_at->clone()->startOfDay());
										$endAtMinutesFromMidnight = $end_at
											->clone()
											->diffInMinutes($end_at->clone()->startOfDay());

										$timeRangeFromCarbonInstance = now()
											->hours(((int) explode(':', $timeRange['from'])[0]))
											->minutes(((int) explode(':', $timeRange['from'])[1]));
										$timeRangeToCarbonInstance = now()
											->hours(((int) explode(':', $timeRange['to'])[0]))
											->minutes(((int) explode(':', $timeRange['to'])[1]));

										$timeRangeFromMinutesFromMidnight = $timeRangeFromCarbonInstance
											->clone()
											->diffInMinutes(
												$timeRangeFromCarbonInstance->clone()->startOfDay()
											);
										$timeRangeToMinutesFromMidnight = $timeRangeToCarbonInstance
											->clone()
											->diffInMinutes(
												$timeRangeToCarbonInstance->clone()->startOfDay()
											);

										if (
											$specialOffer->time_range_type === 'start' &&
											$startAtMinutesFromMidnight >=
												$timeRangeFromMinutesFromMidnight &&
											$startAtMinutesFromMidnight <= $timeRangeToMinutesFromMidnight
										) {
											$isReservationInSpecialOfferTimeRanges = true;
										}
										if (
											$specialOffer->time_range_type === 'end' &&
											(($timeRangeFromMinutesFromMidnight <=
												$startAtMinutesFromMidnight &&
												$timeRangeToMinutesFromMidnight >
													$startAtMinutesFromMidnight) ||
												($timeRangeFromMinutesFromMidnight >
													$endAtMinutesFromMidnight &&
													$timeRangeToMinutesFromMidnight >
														$endAtMinutesFromMidnight))
										) {
											$isReservationInSpecialOfferTimeRanges = true;
										}
									}
									if (!$isReservationInSpecialOfferTimeRanges) {
										continue;
									}
									$availableSpecialOffers[] = $specialOffer;
								}
								$specialOfferId = null;
								if (count($availableSpecialOffers)) {
									$specialOfferId =
										$availableSpecialOffers[count($availableSpecialOffers) - 1]->id;
								}
								$cancelationType = $cancelationReason = $canceledAt = $cancelerId = null;
								if (random_int(1, 20) === 1) {
									$cancelationType = array_column(
										ReservationSlotCancelationType::cases(),
										'value'
									)[random_int(0, 2)];
									$canceledAt = now()
										->subDays(random_int(1, 20))
										->addDays(10);
									$cancelationReason = __(
										'reservation.cancelation-types.' . random_int(0, 2)
									);
									$cancelerId = match ($cancelationType) {
										2 => $reservation->customer->club
											->users()
											->where('first_name', '!=', 'Administrator')
											->inRandomOrder()
											->first()->id,
										default => null,
									};
								}

								$status = [ReservationSlotStatus::Pending, ReservationSlotStatus::Confirmed][
									random_int(1, 100) < 80
								];
								$clubReservation = random_int(1, 10) === 10;
								$presence = random_int(1, 10) > 2;

								foreach ($slots as $slot) {
									$reservationSlot = ReservationSlot::create([
										'status' => $status,
										'reservation_type_id' =>
											random_int(0, 6) === 0
												? $reservation->customer->club
													->reservationTypes()
													->inRandomOrder()
													->first()->id
												: null,
										'reservation_id' => $reservation->id,
										'discount_code_id' => $discountCodeId,
										'club_reservation' => $clubReservation,
										'special_offer_id' => $specialOfferId,
										'cancelation_type' => $cancelationType,
										'cancelation_reason' => $cancelationReason,
										'canceler_id' => $cancelerId,
										'canceled_at' => $canceledAt,
										'slot_id' => $slot->id,
										'start_at' => $start_at->clone(),
										'end_at' => $end_at->clone(),
										'price' => 0,
										'final_price' => 0,
										'presence' => $presence,
									]);

									if (!$reservation->game->hasFeature('person_as_slot')) {
										$reservationSlot->reservationNumber()->create();
									}
									if ($reservation->game->hasFeature('price_per_person')) {
										$reservationSlot->features()->attach(
											$reservation->game
												->features()
												->where('type', 'price_per_person')
												->first(),
											[
												'data' => json_encode(
													[
														'person_count' => random_int(1, 5),
													],
													JSON_THROW_ON_ERROR
												),
											]
										);
									}
									if (random_int(0, 2) === 0) {
										$sets = $reservation->customer->club
											->sets()
											->inRandomOrder()
											->take(random_int(1, 2))
											->get();
										foreach ($sets as $set) {
											$price = random_int(5, 25) * 100;
											$reservationSlot->sets()->attach($set, [
												'price' => $price,
											]);
											if (random_int(1, 8) === 8) {
												$reservationSlot->sets()->attach($set, [
													'price' => $price,
												]);
											}
										}
									}
									$reservationSlotPriceResult = $reservationSlot->calculatePrice(
										$reservation->customer->id
									);
									$reservationSlot->update([
										'price' => $reservationSlotPriceResult['basePrice'],
										'final_price' => $reservationSlotPriceResult['finalPrice'],
									]);
								}

								if ($reservation->game->hasFeature('person_as_slot')) {
									$reservation->reservationNumber()->create();
								}

								$price = $reservation->reservationSlots()->sum('final_price');
								$reservation->update([
									'price' => $reservation->reservationSlots()->sum('final_price'),
									'paid_at' => $start_at->subHours(random_int(10, 24)),
								]);
							})
					)
					->count(5)
					->afterCreating(function (Customer $customer) use ($tagIds) {
						if (count($tagIds)) {
							for ($i = 0; $i < random_int(1, 5); $i++) {
								$customer->tags()->attach($tagIds[random_int(0, count($tagIds) - 1)]);
							}
						}
					})
			)
			->count(($count = $this->command->ask('How many clubs do you want to seed?')) === '' ? 1 : $count)
			->afterCreating(function (Club $club) {
				OpeningHours::passDefaultToClub($club->id);
				$i = 10;
				foreach (Game::all() as $game) {
					$club->games()->attach($game, [
						'weight' => $i--,
						'custom_names' => json_encode([], JSON_THROW_ON_ERROR),
					]);
				}

				foreach (array_column(AgreementType::cases(), 'value') as $agreementType) {
					$club->agreements()->create([
						'type' => $agreementType,
						'content_type' => AgreementContentType::Text->value,
						'required' => true,
						'active' => true,
					]);
				}

				$club->users()->create([
					'type' => 'manager',
					'email' => "administrator{$club->id}@bookgame.io",
					'first_name' => 'Administrator',
					'last_name' => 'Account',
				]);
			})
			->create();
	}
}
