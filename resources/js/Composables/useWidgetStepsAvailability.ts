import { useWidgetStore } from '@/Stores/widget';
import { useWidgetEnabledGames } from './widget/useWidgetEnabledGames';
import { useRoutingStore } from '@/Stores/routing';
import { computed, ref, watch } from 'vue';

export const useWidgetStepsAvailability = () => {
	const widgetStore = useWidgetStore();
	const games = useWidgetEnabledGames();
	const routingStore = useRoutingStore();

	const gamesHidden = computed(() => games.length === 1);

	const details = computed(
		() =>
			!!widgetStore.date &&
			Boolean(widgetStore.form.duration) &&
			((widgetStore.gameFeatures?.slot_has_parent && widgetStore.gameFeatures.slot_has_parent.length === 0) ||
				Boolean(
					widgetStore.form.features[widgetStore.gameFeatures.slot_has_parent?.[0].id]?.parent_slot_id,
				)) &&
			(widgetStore.gameFeatures.book_singular_slot_by_capacity?.length === 0 ||
				Boolean(
					widgetStore.form.features[widgetStore.gameFeatures.book_singular_slot_by_capacity[0].id].capacity,
				)) &&
			(widgetStore.gameFeatures.slot_has_type?.length === 0 ||
				widgetStore.form.features[widgetStore.gameFeatures.slot_has_type?.[0].id].name !== null) &&
			(widgetStore.gameFeatures.slot_has_subtype?.length === 0 ||
				widgetStore.form.features[widgetStore.gameFeatures.slot_has_subtype?.[0].id].name !== null),
	);

	const sets = computed(
		() =>
			(widgetStore.datetimeBlocks.length && widgetStore.datetimeBlocks.includes(widgetStore.form.start_at)) ||
			(widgetStore.showingStatuses.fullDayReservations &&
				!widgetStore.isStartAtRequired &&
				widgetStore.date !== null),
	);

	const setsHidden = ref<boolean>(true);

	const refreshSetsHidden = () => {
		setsHidden.value = !!(
			widgetStore.sets.length === 0 ||
			(routingStore.stepIndex !== 0 && widgetStore.availableSets.length === 0)
		);
	};

	watch(
		() => widgetStore.setsLoaded,
		() => {
			if (widgetStore.setsLoaded) {
				refreshSetsHidden();
			}
		},
	);

	refreshSetsHidden();

	const time = computed(
		() =>
			!widgetStore.startAtDatesLoadingStatus &&
			((!widgetStore.startAtDatesLoadingStatus &&
				widgetStore.datetimeBlocks.length &&
				widgetStore.datetimeBlocks.includes(widgetStore.form.start_at)) ||
				(widgetStore.showingStatuses.fullDayReservations &&
					!widgetStore.isStartAtRequired &&
					widgetStore.date !== null)),
	);

	const timeHidden = computed(() => false);

	return { gamesHidden, details, sets, setsHidden, time, timeHidden };
};
