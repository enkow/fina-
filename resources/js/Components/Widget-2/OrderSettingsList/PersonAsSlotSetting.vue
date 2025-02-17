<template>
	<OrderSettingsListItem v-if="active">
		<SectionLabel :for="INPUT_ID">{{ label }}</SectionLabel>
		<WidgetNumberInput :id="INPUT_ID" :min="min" :max="max" v-model="model.slots_count" compact />
	</OrderSettingsListItem>
</template>

<script lang="ts" setup>
import OrderSettingsListItem from './OrderSettingsListItem.vue';
import SectionLabel from '../SectionLabel.vue';
import WidgetNumberInput from '@/Components/Widget/Ui/WidgetNumberInput.vue';
import { useWidgetPersonAsSlotSetting } from '@/Composables/widget/useWidgetPersonAsSlotSetting';

const { active, label, min, max, model } = useWidgetPersonAsSlotSetting();

const INPUT_ID = 'person-as-slot-setting-input';
</script>
