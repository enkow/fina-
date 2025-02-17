<template>
	<template v-if="widgetStore.showingStatuses.pricePerPerson && pricePerPerson > 0">
		<slot
			:pricePerPerson="pricePerPerson.toFixed(2)"
			:pricePerPersonIcon="pricePerPersonIcon"
			:pricePerPersonCount="pricePerPersonCount"
			:currency="currency" />
	</template>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

const widgetStore = useWidgetStore();

const pricePerPerson = computed(() => {
	if (
		widgetStore.settings[widgetStore.getSettingKey('price_per_person', 'price_per_person_type') || '']
			?.value === 2
	) {
		return 0;
	}

	return (
		widgetStore.form.features[widgetStore.gameFeatures.price_per_person[0].id].person_count *
		(widgetStore.settings[widgetStore.getSettingKey('price_per_person', 'price_per_person') || '']
			.value as number)
	);
});
const pricePerPersonIcon = computed(() => widgetStore.gameFeatures['price_per_person'][0].data.icon);
const pricePerPersonCount = computed(
	() => widgetStore.form.features[widgetStore.gameFeatures.price_per_person[0].id].person_count,
);
const currency = computed(() => widgetStore.currencySymbols[widgetStore.club.country?.currency || '']);
</script>
