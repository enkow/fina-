<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('admin.settings.index'),
				label: 'Ustawienia',
			},
		]">
		<ContentContainer>
			<div class="col-span-12 space-y-4 md:col-span-6">
				<div v-for="setting in getSettingsByFeatureType('offline_reservation_duration_limit')">
					<OfflineReservationDurationLimit
						v-if="setting.feature.data.duration_limit_status"
						:custom-title="'Maksymalny czas rezerwacji offline - ' + setting.feature.game.name"
						:customRoute="
							route('admin.settings.update', {
								feature_id: setting.feature.id,
								key: 'offline_reservation_duration_limit',
							})
						"
						:setting="setting"
						setting-icon-color="info" />
				</div>
				<div v-for="setting in getSettingsByFeatureType('offline_reservation_daily_limit')">
					<OfflineReservationDailyLimit
						v-if="setting.feature.data.daily_reservation_limit_status"
						:custom-title="'Maksymalna ilość rezerwacji offline - ' + setting.feature.game.name"
						:customRoute="
							route('admin.settings.update', {
								feature_id: setting.feature.id,
								key: 'offline_reservation_daily_limit',
							})
						"
						:setting="setting"
						setting-icon-color="brand" />
				</div>
				<div v-for="setting in getSettingsByFeatureType('offline_reservation_slot_limit')">
					<OfflineReservationSlotLimit
						v-if="setting.feature.data.slot_limit_status"
						:custom-title="'Maksymalna ilość slotów w rezerwacji offline - ' + setting.feature.game.name"
						:customRoute="
							route('admin.settings.update', {
								feature_id: setting.feature.id,
								key: 'offline_reservation_slot_limit',
							})
						"
						:setting="setting"
						setting-icon-color="info" />
				</div>
			</div>
			<div class="col-span-12 space-y-4 md:col-span-6">
				<RefundTimeLimit :setting="settings.refund_time_limit" scope="admin" setting-icon-color="grey" />
				<PaymentMethodFee
					v-for="paymentMethod in paymentMethods.filter((paymentMethod) => paymentMethod.online)"
					custom-symbol="zł"
					:paymentMethod="paymentMethod" />
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import OfflineReservationDurationLimit from '@/Components/Dashboard/Settings/OfflineReservationDurationLimit.vue';
import RefundTimeLimit from '@/Components/Dashboard/Settings/RefundTimeLimit.vue';
import { SettingEntity } from '@/Types/responses';
import OfflineReservationDailyLimit from '@/Components/Dashboard/Settings/OfflineReservationDailyLimit.vue';
import OfflineReservationSlotLimit from '@/Components/Dashboard/Settings/OfflineReservationSlotLimit.vue';
import { useSettings } from '@/Composables/useSettings';
import { PaymentMethod } from '@/Types/models';
import PaymentMethodFee from '@/Components/Dashboard/Settings/PaymentMethodFee.vue';

const props = defineProps<{
	settings: Record<string, SettingEntity>;
	paymentMethods: PaymentMethod[];
}>();

const { getSettingsByFeatureType } = useSettings(props.settings);
</script>
