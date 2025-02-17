<template>
	<template v-if="widgetStore.showingStatuses.personAsSlot">
		<slot :slotsCount="widgetStore.form.slots_count" />
	</template>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
