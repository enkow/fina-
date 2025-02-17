<template>
	<div class="space-y-6 rounded-10 bg-white px-6 py-8 lg:mt-14 lg:h-fit lg:w-86">
		<div class="space-y-2.5">
			<h2 class="my-0 text-xl font-bold text-ui-black">{{ $t('calendar.sets') }}</h2>
			<ul class="space-y-3.5">
				<CalendarOrderSetsSummaryItem v-for="set in sets" :set="set" :currency-symbol="currencySymbol" />
			</ul>
		</div>
		<div class="flex justify-between lg:gap-x-10">
			<p class="text-xl font-bold text-ui-black">{{ $t('calendar.subtotal') }}</p>
			<p class="whitespace-nowrap text-2xl font-semibold text-ui-green">
				{{ formatPrice(widgetStore.calculateSetsPrice()) }} {{ currencySymbol }}
			</p>
		</div>
	</div>
</template>

<script lang="ts" setup>
import CalendarOrderSetsSummaryItem from './CalendarOrderSetsSummaryItem.vue';
import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';
import { formatPrice } from '@/Utils';

const widgetStore = useWidgetStore();

const sets = computed(() => widgetStore.sets.filter(({ selected }) => selected && selected > 0));
const currencySymbol = widgetStore.getCurrencySymbol();
</script>
