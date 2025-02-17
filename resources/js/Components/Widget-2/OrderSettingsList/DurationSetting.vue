<template>
	<OrderSettingsListItem v-if="active">
		<SectionLabel>{{ label }}</SectionLabel>
		<WidgetFakeNumberInput
			:min="min"
			:max="max"
			:step="step"
			:label="durationLabel"
			v-model="model.duration"
			compact />
	</OrderSettingsListItem>
</template>

<script lang="ts" setup>
import WidgetFakeNumberInput from '@/Components/Widget/Ui/WidgetFakeNumberInput.vue';
import OrderSettingsListItem from './OrderSettingsListItem.vue';
import SectionLabel from '../SectionLabel.vue';
import { useWidgetDurationSetting } from '@/Composables/widget/useWidgetDurationSetting';

const { active, label, durationLabel, min, max, step, model } = useWidgetDurationSetting();
</script>
