<template>
	<div class="flex divide-x-2 divide-white/20">
		<ul :class="LIST_STYLES">
			<OrderSummarySectionDetailsFinalPrice />
			<OrderSummarySectionDetailsPricePerPerson />
		</ul>
		<ul :class="LIST_STYLES">
			<OrderSummarySectionDetailsOrderDate />
			<OrderSummarySectionDetailsOrderTime />
		</ul>
		<ul :class="twMerge(LIST_STYLES, availableDetails > 2 && 'gap-y-0.5')">
			<OrderSummarySectionDetailsSlotsCount />
			<OrderSummarySectionDetailsPersonCount />
			<OrderSummarySectionDetailsSlotType />
			<OrderSummarySectionDetailsSlotHasSubslots />
			<OrderSummarySectionDetailsBookSingularSlotByCapacity />
			<OrderSummarySectionDetailsPersonAsSlot />
		</ul>
	</div>
</template>

<script lang="ts" setup>
import OrderSummarySectionDetailsFinalPrice from './OrderSummarySectionDetailsFinalPrice.vue';
import OrderSummarySectionDetailsPricePerPerson from './OrderSummarySectionDetailsPricePerPerson.vue';
import OrderSummarySectionDetailsOrderDate from './OrderSummarySectionDetailsOrderDate.vue';
import OrderSummarySectionDetailsOrderTime from './OrderSummarySectionDetailsOrderTime.vue';
import OrderSummarySectionDetailsSlotsCount from './OrderSummarySectionDetailsSlotsCount.vue';
import OrderSummarySectionDetailsPersonCount from './OrderSummarySectionDetailsPersonCount.vue';
import OrderSummarySectionDetailsSlotType from './OrderSummarySectionDetailsSlotType.vue';
import OrderSummarySectionDetailsSlotHasSubslots from './OrderSummarySectionDetailsSlotHasSubslots.vue';
import OrderSummarySectionDetailsBookSingularSlotByCapacity from './OrderSummarySectionDetailsBookSingularSlotByCapacity.vue';
import OrderSummarySectionDetailsPersonAsSlot from './OrderSummarySectionDetailsPersonAsSlot.vue';
import { twMerge } from 'tailwind-merge';
import { useWidgetAvailableDetails } from '@/Composables/useWidgetAvailableDetails';

const availableDetails = useWidgetAvailableDetails();

const LIST_STYLES =
	'flex h-13 flex-1 flex-shrink-0 flex-col justify-center gap-y-2 px-1.5 py-0.5 first:pl-0 last:pr-0 xxs:px-2.5 md:px-5 [&>li>svg]:flex-shrink-0 [&>li>svg]:text-white [&>li]:flex [&>li]:items-center [&>li]:gap-x-2 [&>li]:font-bold';
</script>
