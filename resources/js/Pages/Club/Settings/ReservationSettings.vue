<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.settings.reservation'),
				label: $t('reservation.online-reservations'),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 space-y-4 xl:col-span-9">
				<AccordionTab>
					<template #title>{{ $t('settings.general-settings') }}</template>
					<div class="space-y-3.5">
						<OnlinePaymentMethodStatus
							v-if="isOnlinePaymentSettings"
							:setting="settings.payment_method"
							setting-icon-color="info"
							with-border />

						<ReservationMaxAdvanceTime
							:setting="settings.reservation_max_advance_time"
							setting-icon-color="info"
							with-border />

						<ReservationMinAdvanceTime
							:setting="settings.reservation_min_advance_time"
							setting-icon-color="info"
							with-border />

						<FullHourStartReservationStatus
							:setting="settings.full_hour_start_reservations_status"
							setting-icon-color="info"
							with-border />

						<RefundTimeLimit setting-icon-color="info" :setting="settings.refund_time_limit" with-border />

						<AdditionalCommissionFixed
							v-if="page.props.user.club.online_payments_enabled !== 'disabled'"
							setting-icon-color="info"
							:setting="settings.additional_commission_fixed"
							with-border />

						<AdditionalCommissionPercent
							v-if="page.props.user.club.online_payments_enabled !== 'disabled'"
							setting-icon-color="info"
							:setting="settings.additional_commission_percent"
							with-border />

						<WidgetColor
							:setting="settings.widget_color"
							name="widget_color"
							setting-icon-color="info"
							with-border />
						<WidgetMessage
							:setting="settings.widget_message"
							name="widget_message"
							setting-icon-color="info"
							with-border />

						<ClubTerms
							:setting="settings.club_terms"
							pathPrepend="club-assets/terms"
							setting-icon-color="info"
							v-if="false" />
					</div>
				</AccordionTab>

				<GameSettings
					v-for="game in games.slice(0, Math.floor(games.length / 2) - 1)"
					:announcements="announcements.filter((item) => item.game_id === game.id)"
					:game="game"
					:manager-emails="managerEmails.filter((item) => item.game_id === game.id)"
					:settings="settings"
					with-border />

				<GameSettings
					v-for="game in games.slice(Math.floor(games.length / 2) - 1)"
					:announcements="announcements.filter((item) => item.game_id === game.id)"
					:game="game"
					:manager-emails="managerEmails.filter((item) => item.game_id === game.id)"
					:settings="settings"
					with-border />
			</div>
			<div class="col-span-12 space-y-4 xl:col-span-3">
				<ResponsiveHelper width="3">
					<template #mascot>
						<Mascot :type="17" />
					</template>
					<template #button>
						<Button
							:href="usePage().props.generalTranslations['help-reservation-settings-link']"
							class="grey xl:!px-0"
							type="link">
							{{ $t('help.learn-more') }}
						</Button>
					</template>
					{{ usePage().props.generalTranslations['help-reservation-settings-content'] }}
				</ResponsiveHelper>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ReservationMaxAdvanceTime from '@/Components/Dashboard/Settings/ReservationMaxAdvanceTime.vue';
import OnlinePaymentMethodStatus from '@/Components/Dashboard/Settings/OnlinePaymentMethodStatus.vue';
import FullHourStartReservationStatus from '@/Components/Dashboard/Settings/FullHourStartReservationStatus.vue';
import ClubTerms from '@/Components/Dashboard/Settings/ClubTerms.vue';
import { Announcement, Game, ManagerEmail } from '@/Types/models';
import ReservationMinAdvanceTime from '@/Components/Dashboard/Settings/ReservationMinAdvanceTime.vue';
import { SettingEntity } from '@/Types/responses';
import WidgetColor from '@/Components/Dashboard/Settings/WidgetColor.vue';
import GameSettings from '@/Pages/Club/Settings/Partials/GameSettings.vue';
import { capitalize, computed } from 'vue';
import RefundTimeLimit from '@/Components/Dashboard/Settings/RefundTimeLimit.vue';
import WidgetMessage from '@/Components/Dashboard/Settings/WidgetMessage.vue';
import AdditionalCommissionFixed from '@/Components/Dashboard/Settings/AdditionalCommissionFixed.vue';
import AdditionalCommissionPercent from '@/Components/Dashboard/Settings/AdditionalCommissionPercent.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { usePage } from '@inertiajs/vue3';
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import { Club} from '@/Types/models';

const props = defineProps<{
	games: Game[];
	settings: Record<string, SettingEntity>;
	managerEmails: ManagerEmail[];
	announcements: Announcement[];
}>();

const page = usePage() as { props: { user: { club: Club } } };


const isOnlinePaymentSettings = computed(() => {
	const club = page.props.user.club as Club;

	const isOnlinePayments = club.online_payments_enabled === 'internal' ||
        (club.online_payments_enabled === 'external' && Array.isArray(club.payment_methods) && club.payment_methods[0]?.activated === true)
	const isOfflinePayments = club.offline_payments_enabled

	return isOnlinePayments && isOfflinePayments
})
</script>
