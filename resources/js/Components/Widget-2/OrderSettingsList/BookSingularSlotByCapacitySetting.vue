<template>
	<OrderSettingsListItem v-if="active">
		<WidgetSelect :value="value" :items="items" @change="onChange" class="col-span-3" compact />
	</OrderSettingsListItem>
</template>

<script lang="ts" setup>
import WidgetSelect from '@/Components/Widget/Ui/WidgetSelect.vue';
import OrderSettingsListItem from './OrderSettingsListItem.vue';
import { useWidgetBookSingularSlotByCapacitySetting } from '@/Composables/useWidgetBookSingularSlotByCapacitySetting';

const { active, value, items, onChange } = useWidgetBookSingularSlotByCapacitySetting();
</script>
