import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

export const useWidgetSlotsCountSetting = () => {
	const widgetStore = useWidgetStore();

	const active = computed(() => widgetStore.showingStatuses.slotsCount);
	const label = computed(() => widgetStore.gameTranslations['slots-quantity']);
	const min = computed(() => widgetStore.specialOfferSlotsCount ?? widgetStore.gameDateWidgetSlotsMinLimit);
	const max = computed(() => widgetStore.specialOfferSlotsCount ?? widgetStore.gameDateWidgetSlotsMaxLimit);
	const model = computed(() => widgetStore.form);

	return { active, label, min, max, model };
};
