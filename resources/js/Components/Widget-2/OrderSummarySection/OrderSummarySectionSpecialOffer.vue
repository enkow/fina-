<template>
	<div
		v-if="widgetStore.specialOffer || selectedSets.length > 0"
		:class="
			twMerge(
				'mt-2.5 flex items-center gap-x-4 border-t-2 border-white/20 pt-2.5 md:mt-0 md:rounded-2xl md:border-t-0 md:bg-ui-green md:p-4',
				!widgetStore.specialOffer && 'md:hidden',
			)
		">
		<div v-if="widgetStore.specialOffer">
			<h3 class="text-sm font-bold text-ui-black">{{ widgetStore.specialOffer.name }}</h3>
			<p class="text-xs text-ui-black/60">{{ widgetStore.specialOffer.description }}</p>
		</div>
		<div class="md:hidden">
			<OrderSummarySectionSelectedSetList />
		</div>
	</div>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { useWidgetSelectedSets } from '@/Composables/useWidgetSelectedSets';
import { twMerge } from 'tailwind-merge';
import OrderSummarySectionSelectedSetList from './OrderSummarySectionSelectedSetList.vue';

const widgetStore = useWidgetStore();
const { selectedSets } = useWidgetSelectedSets();
</script>
