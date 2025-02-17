<template>
	<template v-if="widgetStore.showingStatuses.slotSelection">
		<slot :value="value" :items="items" :toggle-slot-id="toggleSlotId" />
	</template>
</template>

<script lang="ts" setup>
import axios from 'axios';
import { useWidgetStore } from '@/Stores/widget';
import { capitalize, computed } from 'vue';
import { computedAsync } from '@vueuse/core';
import { wTrans } from 'laravel-vue-i18n';
import type { Slot } from '@/Types/models';

const widgetStore = useWidgetStore();

const slots = computedAsync(async () => {
	const { data } = await axios.get<Slot[]>(
		route('widget.slots', { club: widgetStore.club.slug, ...widgetStore.form }),
	);

	return data;
});

const items = computed(
	() =>
		slots.value?.map(({ id }) => ({
			label: `${capitalize(widgetStore.gameTranslations['slot-singular-short'])} ${
				slots.value?.find((slot) => slot.id === id)?.name ?? 'x'
			}`,
			value: id.toString(),
		})) ?? [],
);

const value = computed(() => {
	if (widgetStore.form.slot_ids.length < 2 && widgetStore.form.slot_ids[0] === null) {
		return widgetStore.gameTranslations['choose-slot'];
	}

	if (widgetStore.form.slot_ids.length > 3) {
		return wTrans('widget.many-slot-selected-label').value;
	}

	return widgetStore.form.slot_ids[0].toString();
});

const toggleSlotId = (idd: string) => {
	const slotId = Number(idd);

	if (widgetStore.form.slot_ids.length === 1 && widgetStore.form.slot_ids[0] === null) {
		widgetStore.form.slot_ids = [];
	}

	if (widgetStore.form.slot_ids.includes(slotId)) {
		widgetStore.form.slot_ids = widgetStore.form.slot_ids.filter((id: number) => id !== slotId);
	} else if (widgetStore.form.slots_count === widgetStore.form.slot_ids.length) {
		if (widgetStore.form.slots_count === 1) {
			widgetStore.form.slot_ids = [slotId];
		} else if (widgetStore.form.slots_count > 1) {
			widgetStore.alertContent = widgetStore.gameTranslations['all-slots-selected-alert'];
		}
	} else {
		widgetStore.form.slot_ids.push(slotId);
	}
};
</script>
