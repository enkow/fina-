import { computed } from 'vue';
import { useWidgetStore } from '@/Stores/widget';

export const useWidgetSelectedSets = () => {
	const widgetStore = useWidgetStore();

	const selectedSets = computed(() => widgetStore.availableSets.filter(({ selected }) => Boolean(selected)));

	return { selectedSets };
};
