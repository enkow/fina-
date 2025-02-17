import Pusher from 'pusher-js';
import { computed, onMounted, ref } from 'vue';
import { useWidgetStore } from '@/Stores/widget';
import { wTrans } from 'laravel-vue-i18n';

export const useWidgetPayment = () => {
	const widgetStore = useWidgetStore();

	const receivedStatus = ref(0);

	const reservationNumber = computed(() => widgetStore.reservationData?.number.toString() ?? '0');

	const statusHeading = computed(() => {
		const trans = !widgetStore.selectedPaymentMethodOnlineStatus
			? wTrans('widget.offline-reservation-finished')
			: receivedStatus.value
			? wTrans('widget.payment-completed')
			: wTrans('widget.payment-initiated');

		return trans.value;
	});

	const statusBadge = computed(() =>
		receivedStatus.value === 0 && widgetStore.selectedPaymentMethodOnlineStatus === true
			? wTrans('reservation.status.during-payment').value
			: wTrans(`reservation.statuses.${receivedStatus.value}`).value,
	);

	const isSuccess = computed(
		() => widgetStore.selectedPaymentMethodOnlineStatus && Boolean(receivedStatus.value),
	);

	const paymentBadge = wTrans(
		`calendar.success-${widgetStore.selectedPaymentMethodOnlineStatus ? 'online' : 'offline'}-payment`,
	);

	const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
		cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
	});

	var channel: any = null;
	onMounted(() => {
		channel = pusher.subscribe('reservation-number-' + (widgetStore.reservationData?.['number'] ?? ''));
		channel.bind('reservation-status-changed', function (data: any) {
			console.log(data);
			receivedStatus.value = data.status;
			if (receivedStatus.value === 1) {
				widgetStore.reservationFinished = true;
			}
			widgetStore.paymentTab.close();
		});
	});

	return { statusHeading, reservationNumber, statusBadge, paymentBadge, isSuccess };
};
