import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

export const useWidgetPersonAsSlotSetting = () => {
	const widgetStore = useWidgetStore();

	const active = computed(() => widgetStore.showingStatuses.personAsSlot);
	const label = computed(() => widgetStore.gameFeatures.person_as_slot[0].translations['slots-quantity']);
	const min = computed(() =>
		widgetStore.gameFeatures.slot_has_parent.length &&
		!widgetStore.form.features[widgetStore.gameFeatures.slot_has_parent[0].id].parent_slot_id
			? widgetStore.form.slots_count
			: 0,
	);
	const max = computed(() =>
		widgetStore.gameFeatures.slot_has_parent.length &&
		widgetStore.form.features[widgetStore.gameFeatures.slot_has_parent[0].id].parent_slot_id
			? widgetStore.gameDateWidgetSlotsMaxLimit
			: 1000,
	);
	const model = computed(() => widgetStore.form);

	return { active, label, min, max, model };
};
