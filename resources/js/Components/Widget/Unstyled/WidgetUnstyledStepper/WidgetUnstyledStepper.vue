<template>
	<ol :class="class">
		<WidgetUnstyledStepperStep
			v-for="(step, index) in steps"
			:step="index + 1"
			:complete="routingStore.stepIndex > step"
			:active="step === routingStore.stepIndex"
			:class="stepClass"
			:active-class="activeStepClass"
			:complete-class="completeStepClass">
			>
			<template #complete-icon>
				<slot name="complete-icon" />
			</template>
		</WidgetUnstyledStepperStep>
	</ol>
</template>

<script lang="ts" setup>
import WidgetUnstyledStepperStep from './WidgetUnstyledStepperStep.vue';
import { useRoutingStore } from '@/Stores/routing';
import { computed } from 'vue';

const routingStore = useRoutingStore();

const steps = computed(() =>
	Array.from({ length: routingStore.stepsNumber })
		.map((_, i) => i)
		.filter((i) => !routingStore.hiddenSteps.includes(i))
		.slice(0, -1),
);

defineProps<{
	class?: string;
	stepClass?: string;
	activeStepClass?: string;
	completeStepClass?: string;
}>();
</script>
