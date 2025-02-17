import { useWidgetStore } from '@/Stores/widget';
import { computed, onMounted } from 'vue';

export const useWidgetSlotHasTypeSetting = () => {
	const widgetStore = useWidgetStore();

	const active = computed(() => widgetStore.showingStatuses.slotHasType);
	const items = computed(() =>
		widgetStore.gameSlotTypes.map((value) => ({
			value,
			label: widgetStore.gameFeatures.slot_has_type[0].translations[`type-${value}`],
		})),
	);
	const value = computed(() =>
		widgetStore.form.features[widgetStore.gameFeatures.slot_has_type[0].id].name === null
			? widgetStore.gameFeatures.slot_has_type[0].translations['widget-select-name']
			: widgetStore.gameFeatures.slot_has_type[0].translations[
					`type-${widgetStore.form.features[widgetStore.gameFeatures.slot_has_type[0].id].name}`
			  ],
	);
	const onChange = widgetStore.selectType;

	onMounted(() => {
		if (widgetStore.gameSlotTypes?.length === 1) {
			widgetStore.selectType(widgetStore.gameSlotTypes[0]);
		}
	});

	return { active, items, value, onChange };
};
