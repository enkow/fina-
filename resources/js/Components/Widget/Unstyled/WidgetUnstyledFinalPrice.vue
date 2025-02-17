<template>
	<slot :finalPrice="finalPrice" :currency="currency" :priceShowing="priceShowing" />
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

const widgetStore = useWidgetStore();

const finalPrice = computed(() => (widgetStore.finalPrice / 100).toFixed(2));
const currency = computed(() => widgetStore.currencySymbols[widgetStore.club.country?.currency || '']);
const priceShowing = computed(
	() =>
		widgetStore.priceLoadedStatus &&
		widgetStore.finalPrice &&
		(widgetStore.datetimeBlocks.includes(widgetStore.form.start_at) ||
			widgetStore.showingStatuses.fullDayReservations),
);
</script>
