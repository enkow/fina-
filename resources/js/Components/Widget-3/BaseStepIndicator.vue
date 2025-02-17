<template>
	<div :style="{ '--widget-color': widgetStore.widgetColor }">
		<WidgetUnstyledStepper
			:class="$props.class"
			step-class="flex h-12 w-12 items-center justify-center rounded-full border-4 border-solid bg-white text-xl font-bold text-black border-[--widget-color]"
			active-step-class="text-white bg-[--widget-color]">
			<template #complete-icon>
				<CheckmarkIcon />
			</template>
		</WidgetUnstyledStepper>
	</div>
</template>

<script lang="ts" setup>
import WidgetUnstyledStepper from '@/Components/Widget/Unstyled/WidgetUnstyledStepper/WidgetUnstyledStepper.vue';
import CheckmarkIcon from '@/Components/Widget-3/Icons/CheckmarkIcon.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();

defineProps<{
	class?: string;
}>();
</script>
