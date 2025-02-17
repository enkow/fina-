<template>
	<slot :step="steps[routingStore.stepIndex].component" />
</template>

<script lang="ts" setup>
import { useRoutingStore } from '@/Stores/routing';
import { onMounted, watch } from 'vue';
import { Step } from '@/Types/routing';

const props = defineProps<{
	steps: Step[];
}>();

const routingStore = useRoutingStore();

const calculateFirstStepIndex = () =>
	props.steps.reduce((prev, { hidden }, index) => (hidden && prev >= index ? prev + 1 : prev), 0);

watch(
	() => [props.steps, routingStore.stepIndex],
	() => {
		const currentStep = props.steps[routingStore.stepIndex];
		const nextStep = props.steps[routingStore.stepIndex + 1];
		const previousStep = props.steps[routingStore.stepIndex - 1];

		const hiddenIndexes = props.steps.reduce<number[]>(
			(prev, curr, index) =>
				curr.hidden && (prev.length === 0 || prev.at(-1) === index - 1) ? [...prev, index] : prev,
			[],
		);

		routingStore.stepsNumber = props.steps.length;
		routingStore.nextOffset = Number(!!nextStep?.hidden) && hiddenIndexes.length;
		routingStore.previousOffset = Number(!!previousStep?.hidden) && hiddenIndexes.length;
		routingStore.nextStepAvailable =
			routingStore.stepIndex < props.steps.length && typeof currentStep.available === 'boolean'
				? currentStep.available
				: true;
		routingStore.hiddenSteps = props.steps.reduce<number[]>(
			(prev, { hidden }, index) => [...prev, ...(hidden ? [index] : [])],
			[],
		);
		routingStore.firstStepIndex = calculateFirstStepIndex();
	},
	{ immediate: true },
);

onMounted(() => {
	routingStore.stepIndex = calculateFirstStepIndex();
});

watch(
	() => routingStore.stepIndex,
	() => {
		window.scrollTo(0, 0);
	},
);
</script>
