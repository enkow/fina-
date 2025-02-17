<template>
	<template v-if="!widgetStore.showingStatuses.fullDayReservations">
		<slot :label="widgetStore.durationLabel" :time="dayjs(widgetStore.form.start_at).format('HH:mm')" />
	</template>
</template>

<script lang="ts" setup>
import dayjs from 'dayjs';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
