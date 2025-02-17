<template>
	<WidgetCompactAlert
		v-if="widgetStore.showingStatuses.fixedReservationDuration && hasTime(widgetStore.form.start_at)">
		{{
			$tChoice('calendar.reservation-time-alert', datesDuration.minutes(), {
				hours: datesDuration.hours().toString(),
				minutes: datesDuration.minutes().toString(),
			})
		}}
	</WidgetCompactAlert>
</template>

<script lang="ts" setup>
import dayjs from 'dayjs';
import WidgetCompactAlert from '@/Components/Widget/Ui/WidgetCompactAlert.vue';
import { useWidgetStore } from '@/Stores/widget';
import { hasTime } from '@/Utils';
import { computed } from 'vue';

const widgetStore = useWidgetStore();

const startDate = computed(() => dayjs(widgetStore.form.start_at));
const endDate = computed(() => startDate.value.clone().add(widgetStore.form.duration, 'minutes'));
const datesDuration = computed(() => dayjs.duration(endDate.value.diff(startDate.value)));
</script>
