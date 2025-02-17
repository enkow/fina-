<template>
	<div
		:class="
			twMerge(
				'relative flex-1 md:col-span-2 md:col-start-2 md:rounded-2xl md:bg-ui-green md:p-4',
				selectedSets.length > 0 && 'md:rounded-b-none',
			)
		">
		<OrderSummarySectionDetailsList />
		<OrderSummarySectionDetailsSets />
	</div>
</template>

<script lang="ts" setup>
import OrderSummarySectionDetailsList from './OrderSummarySectionDetailsList/OrderSummarySectionDetailsList.vue';
import OrderSummarySectionDetailsSets from './OrderSummarySectionDetailsSets.vue';
import { twMerge } from 'tailwind-merge';
import { useWidgetSelectedSets } from '@/Composables/useWidgetSelectedSets';

const { selectedSets } = useWidgetSelectedSets();
</script>
