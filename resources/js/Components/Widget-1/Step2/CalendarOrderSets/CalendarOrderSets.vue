<template>
	<section>
		<h2 class="mb-6 mt-0 text-2xl font-bold text-black">{{ $t('calendar.sets') }}</h2>
		<ul class="grid grid-cols-1 gap-x-10 gap-y-7 md:grid-cols-2 xl:grid-cols-3">
			<CalendarOrderSet
				v-for="set in widgetStore.availableSets"
				:set="set"
				:currency-symbol="widgetStore.getCurrencySymbol()" />
		</ul>
	</section>
</template>

<script lang="ts" setup>
import CalendarOrderSet from './CalendarOrderSet.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
