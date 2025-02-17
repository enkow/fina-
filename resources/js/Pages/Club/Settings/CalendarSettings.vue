<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.settings.calendar'),
				label: $t('reservation.club-calendar'),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 space-y-4 xl:col-span-9">
				<EmailNotificationStatus :setting="settings.email_notifications_status" setting-icon-color="info" />
				<SmsNotificationsStatus
					v-if="smsNotificationAvaible"
					:setting="settings.sms_notifications_status"
					setting-icon-color="info" />
				<VisibleCalendarSlotsQuantity
					v-for="(setting, index) in getSettingsByFeatureType('has_visible_calendar_slots_quantity_setting')"
					:setting="settings[`visible_calendar_slots_quantity_${setting.feature.id}`]"
					setting-icon-color="info" />
				<CalendarTimeScale :setting="settings.calendar_time_scale" setting-icon-color="info" />
				<ReservationTypes :reservation-types="reservationTypes" />
				<ReservationNumberOnCalendarStatus
					:setting="settings.reservation_number_on_calendar_status"
					setting-icon-color="info" />
				<ReservationNotesOnCalendarStatus
					:setting="settings.reservation_notes_on_calendar_status"
					setting-icon-color="info" />
			</div>
			<div class="col-span-12 space-y-4 xl:col-span-3">
				<ResponsiveHelper width="3">
					<template #mascot>
						<Mascot :type="17" />
					</template>
					<template #button>
						<Button
							:href="usePage().props.generalTranslations['help-calendar-settings-link']"
							class="grey xl:!px-0"
							type="link">
							{{ $t('help.learn-more') }}
						</Button>
					</template>
					{{ usePage().props.generalTranslations['help-calendar-settings-content'] }}
				</ResponsiveHelper>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import CalendarTimeScale from '@/Components/Dashboard/Settings/CalendarTimeScale.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import SmsNotificationsStatus from '@/Components/Dashboard/Settings/SmsNotificationsStatus.vue';
import EmailNotificationStatus from '@/Components/Dashboard/Settings/EmailNotificationsStatus.vue';
import { PaginatedResource, SettingEntity } from '@/Types/responses';
import ReservationTypes from '@/Components/Dashboard/Settings/ReservationTypes.vue';
import { Club, ReservationType, User } from '@/Types/models';
import VisibleCalendarSlotsQuantity from '@/Components/Dashboard/Settings/VisibleCalendarSlotsQuantity.vue';
import { usePage } from '@inertiajs/vue3';
import ReservationNumberOnCalendarStatus from '@/Components/Dashboard/Settings/ReservationNumberOnCalendarStatus.vue';
import ReservationNotesOnCalendarStatus from '@/Components/Dashboard/Settings/ReservationNotesOnCalendarStatus.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const props = defineProps<{
	settings: Record<string, SettingEntity>;
	reservationTypes: PaginatedResource<ReservationType[]>;
	user: User;
}>();

const smsNotificationAvaible = usePage().props.user.club.sms_notifications_offline;

function getSettingsByFeatureType(featureType: string): Record<string, SettingEntity> {
	let results: Record<string, SettingEntity> = {};
	Object.keys(props.settings).forEach((key) => {
		if (
			props.settings[key]?.feature?.type === featureType &&
			Object.values(props.user.club.games)
				.map((item) => item.id)
				.includes(props.settings[key]?.feature.game.id)
		) {
			results[key] = props.settings[key];
		}
	});

	return results;
}
</script>
