<template>
	<li class="border-b border-ui-black/30 pb-2.5">
		<h3 class="break-words text-lg font-bold text-ui-black/60">{{ set.name }}</h3>
		<div class="mt-2 flex items-center justify-between">
			<CalendarOrderSetsSummaryInput
				v-if="typeof set.selected === 'number'"
				:min="0"
				:max="set.available || 0"
				v-model="set.selected" />
			<p class="text-lg font-semibold text-ui-black">{{ formatPrice(set.price) }} {{ currencySymbol }}</p>
		</div>
	</li>
</template>

<script lang="ts" setup>
import { Set } from '@/Types/models';
import CalendarOrderSetsSummaryInput from './CalendarOrderSetsSummaryInput.vue';
import { formatPrice } from '@/Utils';

defineProps<{
	currencySymbol: string;
	set: Set;
}>();
</script>
