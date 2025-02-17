<template>
	<div class="step-indicator">
		<div :style="{ background: widgetStore.widgetColor }" class="step-indicator__label">
			{{ widgetStore.currentStepLabel }}
		</div>
		<div v-for="step in Object.keys(widgetStore.steps).map(Number)">
			<StepCircle :class="{ active: isStepFinished(step) }" :widget-color="widgetStore.widgetColor">
				<CheckmarkIcon v-if="step < widgetStore.currentStepIndex" />
				<p v-else>
					{{ step + 1 }}
				</p>
			</StepCircle>
		</div>
	</div>
</template>
<script lang="ts" setup>
import StepCircle from '@/Components/Widget-3/StepCircle.vue';
import CheckmarkIcon from '@/Components/Widget-3/Icons/CheckmarkIcon.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();

function isStepFinished(step: number): boolean {
	return step === widgetStore.currentStepIndex || widgetStore.reservationFinished;
}
</script>

<style scoped>
.step-indicator {
	@apply absolute top-0 flex space-x-7.5 pl-7.5;

	.step-indicator__label {
		@apply flex h-12 w-52 items-center justify-center rounded-md font-bold uppercase text-white;
	}
}
</style>
