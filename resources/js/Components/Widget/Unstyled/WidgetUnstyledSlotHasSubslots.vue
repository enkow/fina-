<template>
	<template v-if="widgetStore.showingStatuses.slotHasSubslots">
		<slot :parentName="name" />
	</template>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

const widgetStore = useWidgetStore();

const name = computed(
	() =>
		widgetStore.gameParentSlots.find(
			({ id }) =>
				widgetStore.form.features[widgetStore.gameFeatures.slot_has_parent[0].id].parent_slot_id === id,
		)?.name,
);
</script>
