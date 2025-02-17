<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Feature;
use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
	private Collection $activeCountries;
	public function featureEntry(string $type, array $data = []): array
	{
		return [
			'type' => $type,
			'code' => $type,
			'data' => $data,
		];
	}
	public function setFeatureTranslations(string $gameName, string $featureType, array $translations): void
	{
		foreach ($this->activeCountries as $country) {
			foreach ($translations as $key => $value) {
				Feature::where('game_id', Game::where('name', $gameName)->first()->id)
					->where('type', $featureType)
					->first()
					->translations()
					->create([
						'country_id' => $country->id,
						'key' => $key,
						'value' => $value,
					]);
			}
		}
	}

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function run(): void
	{
		$this->activeCountries = Country::where('active', true)->get();

		$uniqueFeatures = [
			$this->featureEntry('has_calendar_announcements_setting'),
			$this->featureEntry('has_game_photo_setting'),
			$this->featureEntry('has_widget_announcements_setting'),
			$this->featureEntry('has_manager_emails_setting'),
			$this->featureEntry('has_offline_reservation_limits_settings', [
				'slot_limit_status' => true,
				'duration_limit_status' => true,
				'daily_reservation_limit_status' => true,
			]),
			$this->featureEntry('has_map_setting'),
		];

		$features = [
			'billiard' => [
				$this->featureEntry('price_per_person'),
				$this->featureEntry('has_widget_slots_limit_setting'),
				$this->featureEntry('has_widget_duration_limit_setting'),
				$this->featureEntry('has_visible_calendar_slots_quantity_setting'),
				$this->featureEntry('slot_has_lounge'),
				$this->featureEntry('slot_has_type'),
				$this->featureEntry('slot_has_subtype'),
				$this->featureEntry('has_timers'),
				$this->featureEntry('slot_has_bulb'),
				$this->featureEntry('has_custom_views', [
					'custom_views' => [
						'slots.index' => null,
						'slots.create' => null,
						'slots.edit' => null,
						'reservations.calendar' => null,
						'pricelists.index' => null,
						'pricelists.create' => null,
						'pricelists.edit' => null,
						'reservations.create-form' =>
							'Club/Reservations/CustomReservationForm/Billiard/Create',
						'reservations.edit-form' => 'Club/Reservations/CustomReservationForm/Billiard/Edit',
					],
				]),
				$this->featureEntry('reservation_slot_has_display_name'),
				$this->featureEntry('has_widget_slots_selection'),
			],
			'bowling' => [
				$this->featureEntry('price_per_person'),
				$this->featureEntry('has_widget_duration_limit_setting'),
				$this->featureEntry('has_widget_slots_limit_setting'),
				$this->featureEntry('has_slot_people_limit_settings'),
				$this->featureEntry('has_visible_calendar_slots_quantity_setting'),
				$this->featureEntry('slot_has_convenience'),
				$this->featureEntry('has_timers'),
				$this->featureEntry('has_custom_views', [
					'custom_views' => [
						'slots.index' => null,
						'slots.create' => 'Club/Slots/Custom/Bowling/Create',
						'slots.edit' => 'Club/Slots/Custom/Bowling/Edit',
						'reservations.calendar' => null,
						'pricelists.index' => null,
						'pricelists.create' => null,
						'pricelists.edit' => null,
						'reservations.create-form' =>
							'Club/Reservations/CustomReservationForm/Bowling/Create',
						'reservations.edit-form' => 'Club/Reservations/CustomReservationForm/Bowling/Edit',
					],
				]),
				$this->featureEntry('reservation_slot_has_display_name'),
				$this->featureEntry('has_widget_slots_selection'),
			],
			'numbered_tables' => [
				$this->featureEntry('slot_has_active_status_per_weekday'),
				$this->featureEntry('book_singular_slot_by_capacity'),
				$this->featureEntry('fixed_reservation_duration'),
				$this->featureEntry('full_day_reservations'),
				$this->featureEntry('slot_has_parent'),
				$this->featureEntry('has_only_one_pricelist'),
				$this->featureEntry('has_price_announcements_settings', [
					'price_zero_announcement_status' => true,
					'price_non_zero_announcement_status' => true,
				]),
				$this->featureEntry('has_custom_views', [
					'custom_views' => [
						'slots.index' => 'Club/Slots/Custom/NumberedTables/Index',
						'slots.create' => 'Club/Slots/Custom/NumberedTables/Create',
						'slots.edit' => 'Club/Slots/Custom/NumberedTables/Edit',
						'reservations.calendar' => 'Club/Reservations/Custom/NumberedTables/Calendar',
						'reservations.edit' => 'Club/Reservations/Custom/NumberedTables/Edit',
						'reservations.create' => 'Club/Reservations/Custom/NumberedTables/Create',
						'pricelists.index' => 'redirect:edit',
						'pricelists.create' => 'redirect:edit',
						'pricelists.edit' => 'Club/Pricelists/Custom/NumberedTables/Edit',
						'reservations.create-form' => null,
						'reservations.edit-form' => null,
					],
				]),
				$this->featureEntry('parent_slot_has_online_status'),
				$this->featureEntry('reservation_slot_has_occupied_status'),
				$this->featureEntry('has_widget_slots_selection'),
			],
			'unnumbered_tables' => [
				$this->featureEntry('fixed_reservation_duration'),
				$this->featureEntry('full_day_reservations'),
				$this->featureEntry('has_only_one_pricelist'),
				$this->featureEntry('slot_has_parent'),
				$this->featureEntry('has_price_announcements_settings', [
					'price_non_zero_announcement_status' => true,
					'price_zero_announcement_status' => true,
				]),
				$this->featureEntry('person_as_slot', [
					'slots_default_setting' => true,
					'slots_limit_setting' => true,
					'parent_has_capacity_by_week_day' => true,
				]),
				$this->featureEntry('has_custom_views', [
					'custom_views' => [
						'slots.index' => 'Club/Slots/Custom/UnnumberedTables/Index',
						'slots.create' => 'Club/Slots/Custom/UnnumberedTables/Create',
						'slots.edit' => 'Club/Slots/Custom/UnnumberedTables/Edit',
						'reservations.calendar' => 'Club/Reservations/Custom/UnnumberedTables/Calendar',
						'pricelists.index' => 'redirect:edit',
						'pricelists.create' => 'redirect:edit',
						'pricelists.edit' => 'Club/Pricelists/Custom/UnnumberedTables/Edit',
						'reservations.create-form' => null,
						'reservations.edit-form' => null,
					],
				]),
				$this->featureEntry('parent_slot_has_online_status'),
				$this->featureEntry('reservation_slot_has_occupied_status'),
				$this->featureEntry('has_widget_slots_selection'),
			],
		];
		$games = Game::all();
		foreach ($features as $gameName => $features) {
			$game = $games->where('name', $gameName)->firstOrFail();
			foreach (array_merge($uniqueFeatures, $features) as $feature) {
				$game->features()->create([
					'type' => $feature['type'],
					'code' => $feature['code'],
					'data' => $feature['data'],
				]);
			}
		}
		$this->setFeatureTranslations('bowling', 'has_visible_calendar_slots_quantity_setting', [
			'setting-title' => 'Liczba torów bowlingowych bez scrolla na kalendarzu',
			'setting-description' =>
				'Jest to bardzo przydatne jeśli masz dużo torów bowlingowych a pracujesz np. na tablecie gdzie jest mały ekran. I ustawienie wszystkich dostępnych stołów spowoduje,że kolumny będą bardzo wąskie i praca na kalendarzu będzie utrudniona.',
			'setting-description-bolder' =>
				'Ustawiając ten parametr określisz ile torów bowlingowych będzie widocznych bez scrolla na kalendarzu klubu.',
			'setting-label' => 'Ilość torów bowlingowych bez scrolla',
		]);
		$this->setFeatureTranslations('bowling', 'has_widget_slots_limit_setting', [
			'setting-title' => 'Ilość torów bowlingowych na wtyczce rezerwacyjnej',
			'setting-description' =>
				'Ustawiając ten parametr - określisz ile torów maksymalnie będzie mógł zarezerwować klient online',
		]);
		$this->setFeatureTranslations('bowling', 'slot_has_convenience', [
			'slot-create-label' => 'Bandy dla dzieci',
		]);
	}
}
