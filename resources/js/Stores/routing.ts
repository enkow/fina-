import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { useWidgetStore } from '@/Stores/widget';

export const useRoutingStore = defineStore('routing', () => {
	const stepsNumber = ref(0);
	const firstStepIndex = ref(0);
	const stepIndex = ref(0);
	const nextOffset = ref(0);
	const previousOffset = ref(0);
	const nextStepAvailable = ref(true);
	const hiddenSteps = ref<number[]>([]);

	const widgetStore = useWidgetStore();

	const isFirstStep = computed(() => stepIndex.value === firstStepIndex.value);

	const firstStep = () => {
		widgetStore.fullLoader = false;
		stepIndex.value = firstStepIndex.value;
		logoutUserIfNotVerified();
	};

	const nextStep = () => {
		if (nextStepAvailable.value) {
			stepIndex.value += nextOffset.value + 1;
		}
	};

	const previousStep = () => {
		widgetStore.fullLoader = false;
		if (!isFirstStep.value) {
			stepIndex.value -= 1 - -previousOffset.value;
		}
		logoutUserIfNotVerified();
	};

	const logoutUserIfNotVerified = () => {
		if (widgetStore.customer && widgetStore.customer.verified === false) {
			widgetStore.customer = null;
		}
	};

	return {
		stepsNumber,
		firstStepIndex,
		stepIndex,
		nextOffset,
		previousOffset,
		nextStepAvailable,
		hiddenSteps,
		isFirstStep,
		firstStep,
		nextStep,
		previousStep,
	};
});
