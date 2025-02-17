<template>
	<WidgetUnstyledSlotHasSubslots v-slot="{ parentName }">
		<li>
			<OrderSummarySectionDetailsListContent>{{ parentName }}</OrderSummarySectionDetailsListContent>
		</li>
	</WidgetUnstyledSlotHasSubslots>
</template>

<script lang="ts" setup>
import WidgetUnstyledSlotHasSubslots from '@/Components/Widget/Unstyled/WidgetUnstyledSlotHasSubslots.vue';
import OrderSummarySectionDetailsListContent from './OrderSummarySectionDetailsListContent.vue';
</script>
