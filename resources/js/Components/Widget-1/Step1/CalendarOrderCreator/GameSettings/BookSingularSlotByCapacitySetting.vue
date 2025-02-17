<template>
	<WidgetSelect v-if="active" :items="items" :value="value" @change="onChange" />
</template>

<script lang="ts" setup>
import WidgetSelect from '@/Components/Widget/Ui/WidgetSelect.vue';
import { useWidgetStore } from '@/Stores/widget';
import { useWidgetBookSingularSlotByCapacitySetting } from '@/Composables/useWidgetBookSingularSlotByCapacitySetting';
import { watch } from 'vue';

const widgetStore = useWidgetStore();
const { active, value, items, onChange } = useWidgetBookSingularSlotByCapacitySetting();

watch(value, () => {
	widgetStore.reloadPriceTriggerer++;
	widgetStore.resetSlots();
});
</script>
