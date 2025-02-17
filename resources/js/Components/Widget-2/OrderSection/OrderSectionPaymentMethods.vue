<template>
	<ul class="mt-7.5 flex flex-col gap-x-5 gap-y-2.5 md:flex-row [&>li]:flex-1">
		<li v-if="onlineMethodStatus">
			<TippyWrapper
				v-bind="{
					...(isOnlinePaymentsDisabled && {
						content: $t('widget.online-reservation-limit-exceeded', {
							count: String(widgetStore.customerOnlineActiveReservationsCount),
						}),
					}),
				}">
				<WidgetButton
					size="compact-md"
					radius="sm"
					:disabled="isOnlinePaymentsDisabled || widgetStore.paymentSubmitted"
					@click="widgetStore.store('online')"
					fill>
					{{ $t('widget-2.online-payment') }}
				</WidgetButton>
			</TippyWrapper>
		</li>
		<li v-if="offlineMethodStatus">
			<WidgetButton
				size="compact-md"
				radius="sm"
				@click="widgetStore.store('offline')"
				:disabled="widgetStore.paymentSubmitted"
				fill>
				{{ $t('widget-2.offline-payment') }}
			</WidgetButton>
		</li>
	</ul>
	<p
		v-if="
			onlineMethodStatus &&
			widgetStore.customerOnlineActiveReservationsCount < 3 &&
			widgetStore.finalPrice > 0
		"
		v-html="$t('widget-2.payment-alert')"
		class="mt-2 text-xs text-ui-black/60 md:text-sm"></p>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import TippyWrapper from '@/Components/TippyWrapper.vue';
import { useWidgetPaymentMethodStatuses } from '@/Composables/useWidgetPaymentMethodStatuses';
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';
import { watch } from 'vue';

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();
const { onlineMethodStatus, offlineMethodStatus, isOnlinePaymentsDisabled } =
	useWidgetPaymentMethodStatuses();

watch(
	() => widgetStore.reservationData,
	(value) => {
		if (value) {
			routingStore.nextStep();
		}
	},
);
</script>
