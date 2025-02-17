<template>
	<VueDatePicker
		format="yyyy-MM-dd"
		model-type="yyyy-MM-dd"
		v-bind="{ ...(dayNames && { dayNames }) }"
		v-model="widgetStore.date"
		:allowed-dates="widgetStore.availableDates"
		:enable-time-picker="false"
		:month-change-on-scroll="false"
		:locale="widgetStore.currentLocale"
		auto-apply
		inline>
		<template #month-year="data">
			<slot name="month-year" v-bind="data" />
		</template>
	</VueDatePicker>
</template>

<script lang="ts" setup>
import VueDatePicker from '@vuepic/vue-datepicker';
import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

import '@vuepic/vue-datepicker/dist/main.css';

const widgetStore = useWidgetStore();

const dayNames = computed(
	() => (widgetStore.generalTranslations as Record<string, string[]>)?.['week-day-short'],
);
</script>
