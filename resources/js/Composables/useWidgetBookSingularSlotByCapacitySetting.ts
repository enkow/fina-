import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

export const useWidgetBookSingularSlotByCapacitySetting = () => {
	const widgetStore = useWidgetStore();

	const active = computed(
		() =>
			widgetStore.showingStatuses.bookSingularSlotByCapacity &&
			widgetStore.form.features[widgetStore.gameFeatures.slot_has_parent?.[0]?.id]?.parent_slot_id,
	);

	const value = computed(() => {
		const capacity =
			widgetStore.form.features[widgetStore.gameFeatures.book_singular_slot_by_capacity[0]?.id]?.capacity;

		return `${widgetStore.gameFeatures.book_singular_slot_by_capacity[0]?.translations[
			'widget-capacity-label'
		]}${capacity ? `: ${capacity}` : ''}`;
	});

	const items = computed(() =>
		widgetStore.availableGameSlotsCapacities.map(String).map((capacity) => ({
			label: capacity,
			value: capacity,
		})),
	);

	const onChange = (capacity: string) => {
		widgetStore.form.features[widgetStore.gameFeatures.book_singular_slot_by_capacity[0].id].capacity =
			Number(capacity);
	};

	return { active, value, items, onChange };
};
