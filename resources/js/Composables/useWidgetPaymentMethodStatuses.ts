import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

export const useWidgetPaymentMethodStatuses = () => {
    const widgetStore = useWidgetStore();

    const onlineMethodStatus = computed(() => {
       return widgetStore.club.online_payments_enabled === 'internal' ||
        (widgetStore.club.online_payments_enabled === 'external' && widgetStore.club.paymentMethod && widgetStore.club.paymentMethod.activated === true) ? true : undefined;
    });

    const offlineMethodStatus = computed(() => widgetStore.club.offline_payments_enabled);

    const isOnlinePaymentsDisabled = computed(
        () => widgetStore.customerOnlineActiveReservationsCount >= 3,
    );

    return {
        onlineMethodStatus,
        offlineMethodStatus,
        isOnlinePaymentsDisabled,
    };
};
