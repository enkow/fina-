<template>
	<div class="space-y-5">
		<ol
			:class="
				twJoin(
					'flex gap-x-2.5 md:grid md:justify-around lg:flex',
					steps.length === 3 && 'md:grid-cols-3',
					steps.length === 4 && 'md:grid-cols-4',
				)
			">
			<StepperItem
				v-for="({ name, disabled }, index) in steps"
				:step="index + 1"
				:current-step="currentStep"
				:name="name.value"
				:is-active="index + 1 === currentStep"
				:is-complete="currentStep > index + 1"
				:disabled="disabled" />
		</ol>
		<progress max="100" :value="percents">{{ percents }}%</progress>
	</div>
</template>

<script lang="ts" setup>
import StepperItem from './StepperItem.vue';
import { wTrans } from 'laravel-vue-i18n';
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';
import { ComputedRef, computed, watch } from 'vue';
import { twJoin } from 'tailwind-merge';

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();

const currentStep = computed(() =>
	routingStore.hiddenSteps.length
		? (routingStore.stepIndex || routingStore.stepIndex + 1) - routingStore.hiddenSteps.length + 1
		: routingStore.stepIndex + 1,
);

const steps = computed<Step[]>(() => [
	{ name: wTrans('calendar.date') },
	...(widgetStore.availableSets.length > 0 ? [{ name: wTrans('calendar.addons') }] : []),
	{ name: wTrans('calendar.payment') },
	{ name: wTrans('calendar.ready') },
]);

const percents = (currentStep.value / steps.value.length) * 100;

interface Step {
	readonly name: ComputedRef<string>;
	readonly disabled?: boolean;
}
</script>

<style scoped>
progress,
progress::-webkit-progress-bar {
	@apply h-4 w-full rounded-full bg-ui-green/30;
}

progress::-webkit-progress-value {
	@apply rounded-full bg-ui-green;
}

progress::-moz-progress-bar {
	@apply rounded-full bg-ui-green;
}
</style>
