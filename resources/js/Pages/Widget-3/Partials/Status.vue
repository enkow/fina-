<template>
	<div
		class="text-widget border-widget flex h-59 w-full flex-col items-center justify-center space-y-3 rounded-md border-[3px] text-center text-sm font-extrabold uppercase xs:text-base sm:text-xl">
		<p>{{ paymentStatusHeading }}</p>
		<p>
			{{
				$t('widget.reservation-number', {
					reservationId: widgetStore.reservationData['number'],
				})
			}}
		</p>
		<p>
			{{ $t('widget.status-of-your-reservation') }}:
			{{ reservationStatusBadge }}
		</p>
		<p v-show="!receivedStatus && widgetStore.selectedPaymentMethodOnlineStatus">
			{{ $t('widget.complete-payment') }}
		</p>
		<p class="mt-4">
			{{ $t('widget.booking-reservation-info') }}
		</p>
	</div>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import Pusher from 'pusher-js';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { wTrans } from 'laravel-vue-i18n';
import Button from '@/Components/Widget-3/Button.vue';
import axios from 'axios';

const widgetStore = useWidgetStore();
const widgetColor: string = widgetStore.widgetColor;

const receivedStatus = ref<number | null>(null);

const reservationStatusBadge = computed<string>(() => {
	let status = receivedStatus.value ?? '0';
	if (status === '0' && widgetStore.selectedPaymentMethodOnlineStatus === true) {
		return wTrans('reservation.status.during-payment').value;
	}
	return wTrans('reservation.statuses.' + status).value;
});

const paymentStatusHeading = computed<string>(() => {
	let status = receivedStatus.value ?? '0';
	if (widgetStore.selectedPaymentMethodOnlineStatus === false) {
		return wTrans('widget.offline-reservation-finished').value;
	}
	if (status.toString() === '0') {
		return wTrans('widget.payment-initiated').value;
	} else {
		return wTrans('widget.payment-completed').value;
	}
});

let pusher: any = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
	cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
});
let channel: any = null;
let refreshInterval: any = null;

onMounted(() => {
	channel = pusher.subscribe('reservation-number-' + (widgetStore.reservationData?.['number'] ?? ''));
	channel.bind('reservation-status-changed', function (data: any) {
		receivedStatus.value = data.status;
		if (receivedStatus.value === 1) {
			widgetStore.reservationFinished = true;
		}
		widgetStore.paymentTab.close();
	});

	refreshInterval = setInterval(() => {
		axios.get('/reservations/' + widgetStore.reservationData?.['number']).then((response) => {
			receivedStatus.value = response.data.status;
			if (receivedStatus.value === 1) {
				widgetStore.reservationFinished = true;
				widgetStore.paymentTab.close();
			}
		});
		if (widgetStore.reservationFinished) {
			channel.unbind('reservation-status-changed');
		}
	}, 10000);
});

onUnmounted(() => {
  if(refreshInterval) {
    clearInterval(refreshInterval);
  }
	channel.unbind('customer-logged');
});
</script>
