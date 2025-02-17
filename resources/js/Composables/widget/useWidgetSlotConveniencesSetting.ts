import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

export const useWidgetSlotConveniencesSetting = () => {
	const widgetStore = useWidgetStore();

	const active = computed(() => widgetStore.showingStatuses.slotConveniences);
	const data = computed(() =>
		widgetStore.gameFeatures.slot_has_convenience.map(({ id, translations }: any) => ({
			model: widgetStore.form.features[id],
			label: translations['reservation-checkbox-label'],
		})),
	);

	return { active, data };
};
