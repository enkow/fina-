import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

export const useWidgetSlotHasSubTypeSetting = () => {
	const widgetStore = useWidgetStore();

	const active = computed(
		() => widgetStore.showingStatuses.slotHasSubType && widgetStore.slotHasSubtypeOptions.length > 1,
	);

	const items = computed(() =>
		widgetStore.slotHasSubtypeOptions.map(({ subtype }) => ({
			value: subtype,
			label: widgetStore.gameFeatures.slot_has_subtype[0].translations[`type-${subtype}`],
		})),
	);

	const value = computed(() =>
		widgetStore.form.features[widgetStore.gameFeatures.slot_has_subtype[0]?.id].name === null
			? widgetStore.gameFeatures.slot_has_subtype[0].translations['widget-select-name']
			: widgetStore.form.features[widgetStore.gameFeatures['slot_has_subtype'][0]?.id].name,
	);

	const onChange = (value: string) =>
		(widgetStore.form.features[widgetStore.gameFeatures.slot_has_subtype[0]?.id].name = value);

	return { active, items, value, onChange };
};
