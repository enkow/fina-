<template>
	<AccordionTab>
		<template #title>{{ usePage().props.gameNames[game.id] }}</template>
		<div class="space-y-3.5">
			<WidgetSlotsLimit
				v-for="setting in getSettingsByGameIdAndFeatureType(game.id, 'has_widget_slots_limit_setting')"
				:setting="settings[`widget_slots_limit_${setting.feature.id}`]"
				:setting-icon-color="game.setting_icon_color"
        v-if="!game.features.find((feature) => feature.type === 'book_singular_slot_by_capacity')"
				:with-border="withBorder" />
			<WidgetDurationLimit
				v-for="setting in getSettingsByGameIdAndFeatureType(game.id, 'has_widget_duration_limit_setting')"
        v-if="!fixedReservationDurationStatus"
				:setting="settings[`widget_duration_limit_${setting.feature.id}`]"
				:setting-icon-color="game.setting_icon_color"
				:with-border="withBorder" />

				<WidgetDurationLimit
					v-for="setting in getSettingsByGameIdAndFeatureType(
					game.id,
					'has_widget_duration_limit_minimum_setting'
					)"
					:setting="
					settings[`widget_duration_limit_minimum_${setting.feature.id}`]
					"
					:setting-icon-color="game.setting_icon_color"
					:with-border="withBorder"
					name="widget_duration_limit_minimum"
				/>

			<!--  <div v-for="(setting, index) in getSettingsByGameIdAndFeatureType(game.id, 'person_as_slot')">-->
			<!--    <PersonAsSlotQuantityDefault-->
			<!--      v-if="-->
			<!--        index.includes(`person_as_slot_quantity_default`) &&-->
			<!--        game.features.filter((item) => item.type === 'person_as_slot')[0]['data']['slots_default_setting']-->
			<!--      "-->
			<!--      :setting="settings[`person_as_slot_quantity_default_${setting.feature.id}`]"-->
			<!--      :setting-icon-color="game.setting_icon_color"-->
			<!--    />-->

			<div v-for="(setting, index) in sortedLimitSettings">
				<OfflineReservationDailyLimit
					v-if="index.includes(`offline_reservation_daily_limit`)"
					:setting="settings[`offline_reservation_daily_limit_${setting.feature.id}`]"
					:setting-icon-color="game.setting_icon_color"
					:with-border="withBorder" />
				<OfflineReservationSlotLimit
					v-if="index.includes(`offline_reservation_slot_limit`) && !game.features.find((feature) => feature.type === 'book_singular_slot_by_capacity')"
					:setting="settings[`offline_reservation_slot_limit_${setting.feature.id}`]"
					:setting-icon-color="game.setting_icon_color"
					:with-border="withBorder" />
				<OfflineReservationDurationLimit
					v-if="index.includes(`offline_reservation_duration_limit`) && !fixedReservationDurationStatus"
					:setting="settings[`offline_reservation_duration_limit_${setting.feature.id}`]"
					:setting-icon-color="game.setting_icon_color"
					:with-border="withBorder" />
			</div>

			<div
				v-for="(setting, index) in getSettingsByGameIdAndFeatureType(
					game.id,
					'has_price_announcements_settings',
				)">
				<PriceZeroAnnouncement
					v-if="
						index.includes(`price_zero_announcement`) &&
						game.features.filter((item) => item.type === 'has_price_announcements_settings')[0]['data'][
							'price_zero_announcement_status'
						]
					"
					:setting="settings[`price_zero_announcement_${setting.feature.id}`]"
					:setting-icon-color="game.setting_icon_color"
					:with-border="withBorder" />
				<PriceNonZeroAnnouncement
					v-if="
						index.includes(`price_non_zero_announcement`) &&
						game.features.filter((item) => item.type === 'has_price_announcements_settings')[0]['data'][
							'price_non_zero_announcement_status'
						]
					"
					:setting="settings[`price_non_zero_announcement_${setting.feature.id}`]"
					:setting-icon-color="game.setting_icon_color"
					:with-border="withBorder" />
			</div>

			<div
				v-if="
					Object.keys(getSettingsByGameIdAndFeatureType(game.id, 'slot_has_lounge')).length ||
					Object.keys(getSettingsByGameIdAndFeatureType(game.id, 'has_widget_slots_selection')).length
				">
				<WidgetSlotsSelectionFeaturesSettings
					:game="game"
					:settings="settings"
					:setting-icon-color="game.setting_icon_color"
					:with-border="withBorder" />
			</div>
			<div
				v-if="
					pricePerPersonSettings?.[Object.keys(pricePerPersonSettings).find((key) => key.includes('type'))]?.[
						'value'
					]
				"
				v-for="(setting, index) in getSettingsByGameIdAndFeatureType(
					game.id,
					'has_slot_people_limit_settings',
				)"
				class="space-y-3.5">
				<SlotPeopleMaxLimit
					v-if="index.includes(`slot_people_max_limit`)"
					:setting="settings[`slot_people_max_limit_${setting.feature.id}`]"
					:setting-icon-color="game.setting_icon_color"
					:with-border="withBorder" />
				<SlotPeopleMinLimit
					v-if="index.includes(`slot_people_max_limit`)"
					:setting="settings[`slot_people_min_limit_${setting.feature.id}`]"
					:setting-icon-color="game.setting_icon_color"
					:with-border="withBorder" />
			</div>
			<ClubMap
				v-for="(setting, index) in getSettingsByGameIdAndFeatureType(game.id, 'has_map_setting')"
				:setting="settings[`club_map_${setting.feature.id}`]"
				:setting-icon-color="game.setting_icon_color"
				pathPrepend="club-assets/maps"
				:with-border="withBorder" />

			<GamePhoto
				v-for="(setting, index) in getSettingsByGameIdAndFeatureType(game.id, 'has_game_photo_setting')"
				:setting="settings[`game_photo_${setting.feature.id}`]"
				:setting-icon-color="game.setting_icon_color"
				pathPrepend="club-assets/game-photos"
				:with-border="withBorder" />

			<CalendarAnnouncements
				v-if="
					game?.features?.filter((item) => item.type === 'has_calendar_announcements_setting').length &&
					usePage().props.user.club.calendar_enabled
				"
				:announcements="announcements.filter((item) => item.type === 2)"
				:game="game"
				:setting-icon-color="game.setting_icon_color"
				:with-border="withBorder" />

			<WidgetAnnouncements
				v-if="game?.features?.filter((item) => item.type === 'has_widget_announcements_setting').length"
				:announcements="announcements.filter((item) => item.type === 1)"
				:game="game"
				:setting-icon-color="game.setting_icon_color"
				:with-border="withBorder" />

			<ManagerEmails
				v-if="game?.features?.filter((item) => item.type === 'has_manager_emails_setting').length"
				:game="game"
				:manager-emails="managerEmails"
				:setting-icon-color="game.setting_icon_color"
				:with-border="withBorder" />
			<div
				v-for="(setting, index) in getSettingsByGameIdAndFeatureType(game.id, 'fixed_reservation_duration')">
				<FixedReservationDuration
					v-if="index.includes('status')"
					:custom-title="setting.translations['group-setting-title']"
					:status-setting="settings[`fixed_reservation_duration_status_${setting.feature.id}`]"
					:value-setting="settings[`fixed_reservation_duration_value_${setting.feature.id}`]"
					:setting-icon-color="game.setting_icon_color"
					:with-border="withBorder" />
			</div>
		</div>
	</AccordionTab>
</template>

<script lang="ts" setup>
import { Announcement, Game, ManagerEmail } from '@/Types/models';
import { SettingEntity } from '@/Types/responses';
import ManagerEmails from '@/Components/Dashboard/Settings/ManagerEmails.vue';
import PriceNonZeroAnnouncement from '@/Components/Dashboard/Settings/PriceNonZeroAnnouncement.vue';
import PriceZeroAnnouncement from '@/Components/Dashboard/Settings/PriceZeroAnnouncement.vue';
import OfflineReservationDurationLimit from '@/Components/Dashboard/Settings/OfflineReservationDurationLimit.vue';
import { usePage } from '@inertiajs/vue3';
import WidgetSlotsLimit from '@/Components/Dashboard/Settings/WidgetSlotsLimit.vue';
import ClubMap from '@/Components/Dashboard/Settings/ClubMap.vue';
import WidgetAnnouncements from '@/Components/Dashboard/Settings/WidgetAnnouncements.vue';
import CalendarAnnouncements from '@/Components/Dashboard/Settings/CalendarAnnouncements.vue';
import WidgetDurationLimit from '@/Components/Dashboard/Settings/WidgetDurationLimit.vue';
import OfflineReservationSlotLimit from '@/Components/Dashboard/Settings/OfflineReservationSlotLimit.vue';
import OfflineReservationDailyLimit from '@/Components/Dashboard/Settings/OfflineReservationDailyLimit.vue';
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import {computed, ref} from 'vue';
import WidgetSlotsSelectionFeaturesSettings from '@/Components/Dashboard/Settings/WidgetSlotsSelectionFeaturesSettings.vue';
import SlotPeopleMinLimit from '@/Components/Dashboard/Settings/SlotPeopleMinLimit.vue';
import SlotPeopleMaxLimit from '@/Components/Dashboard/Settings/SlotPeopleMaxLimit.vue';
import GamePhoto from '@/Components/Dashboard/Settings/GamePhoto.vue';
import FixedReservationDuration from '@/Components/Dashboard/Settings/FixedReservationDuration.vue';

const props = defineProps<{
	game: Game;
	settings: Record<string, SettingEntity>;
	managerEmails: ManagerEmail[];
	announcements: Announcement[];
	withBorder?: boolean;
}>();

function getSettingsByGameIdAndFeatureType(
	gameId: number,
	featureType: string,
): Record<string, SettingEntity> {
	let results: Record<string, SettingEntity> = {};
	Object.keys(props.settings).forEach((key) => {
		if (
			props.settings[key]?.feature?.type === featureType &&
			props.settings[key]?.feature?.game?.id === gameId
		) {
			results[key] = props.settings[key];
		}
	});

	return results;
}

const sortedLimitSettings = computed<Object>(() => {
	return Object.entries(
		getSettingsByGameIdAndFeatureType(props.game.id, 'has_offline_reservation_limits_settings'),
	)
		.sort((a, b) => {
			return a[0].localeCompare(b[0]);
		})
		.reduce((acc, [key, value]) => {
			acc[key] = value;
			return acc;
		}, {});
});

const fixedReservationDurationStatus = ref<boolean>(false);

Object.keys(getSettingsByGameIdAndFeatureType(props.game.id, 'fixed_reservation_duration')).forEach((key) => {
  if (key.includes('status')) {
    fixedReservationDurationStatus.value = !!props.settings[key].value;
  }
})


const pricePerPersonSettings = getSettingsByGameIdAndFeatureType(props.game.id, 'price_per_person');
</script>
