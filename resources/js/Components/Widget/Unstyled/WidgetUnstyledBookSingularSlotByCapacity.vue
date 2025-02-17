<template>
	<template v-if="widgetStore.showingStatuses.bookSingularSlotByCapacity">
		<slot
			:capacity="
				widgetStore.form.features[widgetStore.gameFeatures.book_singular_slot_by_capacity[0].id].capacity
			" />
	</template>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
