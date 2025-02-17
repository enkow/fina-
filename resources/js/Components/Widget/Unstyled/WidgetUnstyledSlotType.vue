<template>
	<template v-if="widgetStore.showingStatuses.slotHasType">
		<slot :slotTypeName="slotTypeName" :slotSubTypeName="slotSubTypeName" />
	</template>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

const widgetStore = useWidgetStore();

const slotTypeName = computed(
	() =>
		widgetStore.gameFeatures.slot_has_type[0]?.translations[
			`type-${widgetStore.form.features[widgetStore.gameFeatures.slot_has_type[0].id].name}`
		],
);
const slotSubTypeName = computed(
	() =>
		widgetStore.gameFeatures.slot_has_subtype[0]?.translations[
			`type-${widgetStore.form.features[widgetStore.gameFeatures.slot_has_subtype[0].id].name}`
		],
);
</script>
