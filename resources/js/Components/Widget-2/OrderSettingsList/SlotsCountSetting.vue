<template>
	<OrderSettingsListItem v-if="active">
		<SectionLabel>{{ label }}</SectionLabel>
		<WidgetNumberInput :min="min" :max="max" v-model="model.slots_count" compact />
	</OrderSettingsListItem>
</template>

<script lang="ts" setup>
import OrderSettingsListItem from './OrderSettingsListItem.vue';
import SectionLabel from '../SectionLabel.vue';
import WidgetNumberInput from '@/Components/Widget/Ui/WidgetNumberInput.vue';
import { useWidgetSlotsCountSetting } from '@/Composables/widget/useWidgetSlotsCountSetting';

const { active, label, min, max, model } = useWidgetSlotsCountSetting();
</script>
