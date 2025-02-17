<template>
	<CalendarBookingContainer v-if="sets.length > 0">
		<CalendarBookingSectionHeader :title="$t('calendar.sets')" :price="widgetStore.calculateSetsPrice()" />
		<div class="flex font-medium [&>ol]:space-y-1.5">
			<ol class="flex-1">
				<li v-for="{ name } in sets">{{ name }}</li>
			</ol>
			<ol class="mr-2.5 text-end">
				<li v-for="{ selected } in sets">x{{ selected }}</li>
			</ol>
			<ol class="text-end font-semibold text-ui-black">
				<li v-for="{ price } in sets">{{ formatPrice(price) }} {{ widgetStore.getCurrencySymbol() }}</li>
			</ol>
		</div>
	</CalendarBookingContainer>
</template>

<script lang="ts" setup>
import CalendarBookingSectionHeader from './CalendarBookingSectionHeader.vue';
import CalendarBookingContainer from './CalendarBookingContainer.vue';
import { useWidgetStore } from '@/Stores/widget';
import { formatPrice } from '@/Utils';

const widgetStore = useWidgetStore();
const sets = widgetStore.sets.filter(({ selected }) => selected && selected > 0);
</script>
