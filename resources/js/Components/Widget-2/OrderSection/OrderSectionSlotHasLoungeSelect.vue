<template>
	<Widget2DeepSelect
		v-if="widgetStore.showingStatuses.slotHasLounge"
		:placeholder="value"
		:value="value"
		:rows="rows"
		@item-click="onItemClick" />
</template>

<script lang="ts" setup>
import Widget2DeepSelect from '../Widget2DeepSelect/Widget2DeepSelect.vue';
import axios from 'axios';
import { computed, reactive } from 'vue';
import { useWidgetStore } from '@/Stores/widget';
import { wTrans } from 'laravel-vue-i18n';
import { computedAsync } from '@vueuse/core';
import type { Slot } from '@/Types/models';
import type { Row } from '../Widget2DeepSelect/Widget2DeepSelect.types';

const widgetStore = useWidgetStore();

const slotsGroupedByLoungeCapacity = reactive<Record<string, Slot[]>>({});

const slots = computedAsync<Slot[]>(async () => {
	const { data } = await axios.get<Slot[]>(
		route('widget.slots', {
			club: widgetStore.club.slug,
			game_id: widgetStore.form.game_id,
			start_at: widgetStore.form.start_at,
			duration: widgetStore.form.duration,
		}),
	);

	data.forEach((slot) => {
		const feature = JSON.parse(
			slot.features.find(({ type }) => type === 'slot_has_lounge')?.pivot?.data || '{}',
		) as { min?: number; max?: number };

		const key = `${feature.min || '0'}-${feature.max || '0'}`;

		if (slotsGroupedByLoungeCapacity.hasOwnProperty(key)) {
			slotsGroupedByLoungeCapacity[key].push(slot);
		} else {
			slotsGroupedByLoungeCapacity[key] = [slot];
		}
	});

	return data;
});

const value = computed(() => {
	if (
		widgetStore.form.slot_ids.length === 0 ||
		(widgetStore.form.slot_ids.length === 1 && widgetStore.form.slot_ids[0] === null)
	) {
		return widgetStore.gameTranslations['choose-slot'];
	}

	if (widgetStore.form.slot_ids.length > 3) {
		return wTrans('widget.many-slot-selected-label').value;
	}

	const names = widgetStore.form.slot_ids
		.filter(Boolean)
		.map(
			(slotId: number) =>
				`${widgetStore.gameTranslations['slot-singular-short']} ${
					slots.value?.find(({ id }) => slotId === id)?.name ?? 'x'
				}`,
		);

	return names.join(', ');
});

const rows = computed<Row[]>(() =>
	Object.entries(slotsGroupedByLoungeCapacity)
		.sort()
		.map(([key, value]) => ({
			label: `${widgetStore.gameFeatures.slot_has_lounge[0].translations['slots-capacity']} ${key}`,
			items: value.map(({ id, name }) => ({
				label: `${widgetStore.gameTranslations['slot-singular-short']} ${name}`,
				value: id.toString(),
			})),
		})),
);

const onItemClick = (value: string) => {
	const slotId = Number(value);

	if (widgetStore.form.slot_ids.length === 1 && widgetStore.form.slot_ids[0] === null) {
		widgetStore.form.slot_ids = [];
	}

	if (
		!widgetStore.form.slot_ids.includes(slotId) &&
		widgetStore.form.slots_count === widgetStore.form.slot_ids.length
	) {
		if (widgetStore.form.slots_count === 1) {
			widgetStore.form.slot_ids = [slotId];
		} else if (widgetStore.form.slots_count > 1) {
			widgetStore.alertContent = widgetStore.gameTranslations['all-slots-selected-alert'];
		}
	} else if (widgetStore.form.slot_ids.includes(slotId)) {
		widgetStore.form.slot_ids = widgetStore.form.slot_ids.filter((id: number) => id !== slotId);
	} else {
		widgetStore.form.slot_ids.push(slotId);
	}
};
</script>
