<template>
	<WidgetUnstyledOrderTime v-slot="{ label, time }">
		<li>
			<ClockIcon />
			<OrderSummarySectionDetailsListContent>
				{{ label }}
				<span class="text-xs font-normal text-ui-black/60">({{ time }})</span>
			</OrderSummarySectionDetailsListContent>
		</li>
	</WidgetUnstyledOrderTime>
</template>

<script lang="ts" setup>
import WidgetUnstyledOrderTime from '@/Components/Widget/Unstyled/WidgetUnstyledOrderTime.vue';
import OrderSummarySectionDetailsListContent from './OrderSummarySectionDetailsListContent.vue';
import ClockIcon from '@/Components/Widget-2/Icons/ClockIcon.vue';
</script>
