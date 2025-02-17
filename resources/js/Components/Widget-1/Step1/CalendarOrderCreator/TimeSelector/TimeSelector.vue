<template>
	<section>
		<SectionTitleContainer>
			<SectionTitle>{{ $t('calendar.choose-time') }}</SectionTitle>
			<ServiceState>
				<ServiceStateItem variant="available" :title="$t('calendar.available')" />
				<ServiceStateItem variant="unavailable" :title="$t('calendar.unavailable')" />
				<ServiceStateItem variant="selected" :title="$t('calendar.selected')" />
			</ServiceState>
		</SectionTitleContainer>
		<div class="relative">
			<TimeSelectorList :class="{ invisible: isLoading }" />
			<CalendarSpinner v-if="isLoading" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2" />
		</div>
	</section>
</template>

<script lang="ts" setup>
import SectionTitle from '../SectionTitle.vue';
import SectionTitleContainer from '../SectionTitleContainer.vue';
import ServiceState from '../ServiceState/ServiceState.vue';
import ServiceStateItem from '../ServiceState/ServiceStateItem.vue';
import CalendarSpinner from '@/Components/Widget-1/Shared/CalendarSpinner.vue';
import TimeSelectorList from './TimeSelectorList/TimeSelectorList.vue';
import { useWidgetStore } from '@/Stores/widget';
import { computed, ref } from 'vue';
import { watchDebounced } from '@vueuse/core';

const isLoadingDebounced = ref(false);

const widgetStore = useWidgetStore();
const isLoading = computed(
	() =>
		widgetStore.startAtDatesLoadingStatus ||
		widgetStore.openingHoursLoadingStatus ||
		isLoadingDebounced.value,
);

watchDebounced(
	() => [widgetStore.startAtDatesLoadingStatus, widgetStore.openingHoursLoadingStatus],
	(value) => {
		isLoadingDebounced.value = value.some(Boolean);
	},
	{ debounce: 150 },
);
</script>
