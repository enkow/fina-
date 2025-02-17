<template>
	<template v-if="widgetStore.showingStatuses.slotsCount">
		<slot :label="label" :slotsCount="widgetStore.form.slots_count" />
	</template>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

const widgetStore = useWidgetStore();

const label = computed(() => {
	const firstConvenienceFeature = widgetStore.gameFeatures.slot_has_convenience[0];

	if (firstConvenienceFeature) {
		if (
			widgetStore.form.features[firstConvenienceFeature.id].status &&
			firstConvenienceFeature.translations['slot-with-convenience'].length
		) {
			return firstConvenienceFeature.translations['slot-with-convenience'];
		}

		if (firstConvenienceFeature.translations['slot-without-convenience'].length) {
			return firstConvenienceFeature.translations['slot-without-convenience'];
		}
	}

	return widgetStore.gameTranslations['slot-singular-short'];
});
</script>
