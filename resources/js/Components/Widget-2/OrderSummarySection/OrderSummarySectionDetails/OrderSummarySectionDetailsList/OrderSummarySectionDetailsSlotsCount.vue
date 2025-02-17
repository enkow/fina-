<template>
	<WidgetUnstyledSlotsCount v-slot="{ label, slotsCount }">
		<li>
			<OrderSummarySectionDetailsListContent>
				{{ label }}
				<span class="text-xs font-normal text-ui-black/60">x</span>
				{{ slotsCount }}
			</OrderSummarySectionDetailsListContent>
		</li>
	</WidgetUnstyledSlotsCount>
</template>

<script lang="ts" setup>
import WidgetUnstyledSlotsCount from '@/Components/Widget/Unstyled/WidgetUnstyledSlotsCount.vue';
import OrderSummarySectionDetailsListContent from './OrderSummarySectionDetailsListContent.vue';
</script>
