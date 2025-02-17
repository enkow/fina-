import { useWidgetStore } from '@/Stores/widget';
import { computed, watch } from 'vue';

export const useWidgetAvailableDetails = () => {
	const widgetStore = useWidgetStore();

	const isSlotsCountAvailable = computed(() => widgetStore.showingStatuses.slotsCount);
	const isPricePerPersonAvailable = computed(
		() => Boolean(widgetStore.showingStatuses.pricePerPerson) && pricePerPerson.value > 0,
	);
	const isSlotTypeAvailable = computed(() => widgetStore.showingStatuses.slotHasType);
	const isSlotHasSubslotsAvailable = computed(() => widgetStore.showingStatuses.slotHasSubslots);
	const isBookSingularSlotByCapacityAvailable = computed(
		() => widgetStore.showingStatuses.bookSingularSlotByCapacity,
	);
	const isPersonAsSlotAvailable = computed(() => widgetStore.showingStatuses.personAsSlot);

	const pricePerPerson = computed(() => {
		if (
			widgetStore.settings[widgetStore.getSettingKey('price_per_person', 'price_per_person_type') || '']
				?.value === 2
		) {
			return 0;
		}

		return (
			widgetStore.form.features[widgetStore.gameFeatures.price_per_person[0].id].person_count *
			(widgetStore.settings[widgetStore.getSettingKey('price_per_person', 'price_per_person') || '']
				.value as number)
		);
	});

	const number = computed(
		() =>
			[
				isSlotsCountAvailable.value,
				isPricePerPersonAvailable.value,
				isSlotTypeAvailable.value,
				isSlotHasSubslotsAvailable.value,
				isBookSingularSlotByCapacityAvailable.value,
				isPersonAsSlotAvailable.value,
			].filter(Boolean).length,
	);

	return number;
};
