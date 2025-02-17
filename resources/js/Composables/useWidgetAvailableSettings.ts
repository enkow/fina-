import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

type Setting = keyof ReturnType<typeof useWidgetStore>['showingStatuses'];

export const useWidgetAvailableSettings = (settings?: Setting[]) => {
	const widgetStore = useWidgetStore();

	const isIncluded = (setting: Setting) => !settings || settings.includes(setting);

	const isDurationSettingAvailable = computed(
		() => isIncluded('duration') && widgetStore.showingStatuses.duration,
	);
	const isPricePerPersonSettingAvailable = computed(
		() => isIncluded('pricePerPerson') && widgetStore.showingStatuses.pricePerPerson,
	);
	const isSlotsCountSettingAvailable = computed(
		() => isIncluded('slotsCount') && widgetStore.showingStatuses.slotsCount,
	);
	const isPersonAsSlotSettingAvailable = computed(
		() => isIncluded('personAsSlot') && widgetStore.showingStatuses.personAsSlot,
	);
	const isSlotHasTypeSettingAvailable = computed(
		() => isIncluded('slotHasType') && widgetStore.showingStatuses.slotHasType,
	);
	const isSlotConveniencesSettingAvailable = computed(
		() => isIncluded('slotConveniences') && widgetStore.showingStatuses.slotConveniences,
	);
	const isSlotHasSubTypeSettingAvailable = computed(
		() =>
			isIncluded('slotHasType') &&
			widgetStore.showingStatuses.slotHasSubType &&
			widgetStore.slotHasSubtypeOptions.length > 1,
	);
	const isSlotHasSubslotsSettingAvailable = computed(
		() =>
			isIncluded('slotHasSubslots') &&
			widgetStore.showingStatuses.slotHasSubslots &&
			widgetStore.gameParentSlots.length > 1,
	);
	const isBookSingularSlotByCapacitySettingAvailable = computed(
		() =>
			isIncluded('bookSingularSlotByCapacity') &&
			widgetStore.showingStatuses.bookSingularSlotByCapacity &&
			widgetStore.form.features[widgetStore.gameFeatures.slot_has_parent?.[0].id].parent_slot_id,
	);

	const number = computed(
		() =>
			[
				isDurationSettingAvailable.value,
				isPricePerPersonSettingAvailable.value,
				isSlotsCountSettingAvailable.value,
				isPersonAsSlotSettingAvailable.value,
				isSlotHasTypeSettingAvailable.value,
				isSlotConveniencesSettingAvailable.value,
				isSlotHasSubTypeSettingAvailable.value,
				isSlotHasSubslotsSettingAvailable.value,
				isBookSingularSlotByCapacitySettingAvailable.value,
			].filter(Boolean).length,
	);

	return number;
};
