import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

export const useWidgetSlotHasSubslotsSetting = () => {
	const widgetStore = useWidgetStore();

	const active = computed(
		() => widgetStore.showingStatuses.slotHasSubslots && widgetStore.gameParentSlots.length > 1,
	);

	const items = computed(() =>
		widgetStore.gameParentSlots.map(({ id }) => ({
			label: widgetStore.gameParentSlots.find((slot) => slot.id === id)?.name || '?',
			value: id.toString(),
		})),
	);

	const value = computed(() => {
		const parentSlotId =
			widgetStore.form.features[widgetStore.gameFeatures.slot_has_parent[0]?.id]?.parent_slot_id;

		return parentSlotId
			? widgetStore.gameParentSlots.find(({ id }) => parentSlotId === id)?.name
			: widgetStore.gameFeatures.slot_has_parent[0]?.translations['choose-parent-slot'];
	});

	const onChange = (id: string | null) => {
		widgetStore.form.features[widgetStore.gameFeatures.slot_has_parent[0].id].parent_slot_id =
			id && Number(id);
	};

	return { active, items, value, onChange };
};
