<template>
	<WidgetSelect v-if="active" :items="selectItems" :value="value" @change="handleSelectChange" />
</template>

<script lang="ts" setup>
import WidgetSelect from '@/Components/Widget/Ui/WidgetSelect.vue';
import { useWidgetSlotHasSubslotsSetting } from '@/Composables/useWidgetSlotHasSubslotsSetting';
import { useWidgetStore } from '@/Stores/widget';
import { wTrans } from 'laravel-vue-i18n';
import { computed, watch } from 'vue';

const { active, items, value, onChange } = useWidgetSlotHasSubslotsSetting();
const widgetStore = useWidgetStore();

const ANY_VALUE = 'any';

const selectItems = computed<string[]>(() => {
	return [
		...(widgetStore.isSlotSelectionEnabled
			? [{ value: ANY_VALUE, label: wTrans('calendar.any').value }]
			: []),
		...items.value,
	];
});

const handleSelectChange = (value: string) => {
	const newValue = value === ANY_VALUE ? null : value;

	if (value === ANY_VALUE) {
		widgetStore.form.features[widgetStore.gameFeatures.book_singular_slot_by_capacity[0].id].capacity = null;
	}

	onChange(newValue);
};

watch(value, () => {
	widgetStore.resetSlots();
});
</script>
