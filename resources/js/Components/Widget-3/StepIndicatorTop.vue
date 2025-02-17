<template>
	<div class="step-indicator">
		<div :style="{ background: widgetStore.widgetColor }" class="step-indicator__label">
			{{ widgetStore.currentStepLabel }}
		</div>
		<BaseStepIndicator class="hidden space-x-7.5 md:flex" />
	</div>
</template>
<script lang="ts" setup>
import BaseStepIndicator from './BaseStepIndicator.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>

<style scoped>
.step-indicator {
	@apply absolute -top-4 flex space-x-7.5 pl-4.75 sm:pl-7.5 md:top-0;

	.step-indicator__label {
		@apply flex h-12 w-52 items-center justify-center rounded-md font-bold uppercase text-white;
	}
}
</style>
