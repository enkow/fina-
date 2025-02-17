<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';

const props = withDefaults(
	defineProps<{
		value?: string;
		required?: boolean;
	}>(),
	{
		required: false,
	},
);
const widgetStore = useWidgetStore();
const widgetColor = widgetStore.widgetColor;
</script>
<template>
	<label :style="{ color: widgetColor }" class="block flex text-xs font-bold uppercase">
		<span v-if="value">{{ value }}</span>
		<span v-else><slot /></span>

		<span v-if="required" class="ml-0.25 font-semibold text-danger-base">*</span>
	</label>
</template>
