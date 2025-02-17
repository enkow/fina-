<template>
	<WidgetUnstyledSlotType v-slot="{ slotTypeName, slotSubTypeName }">
		<li>
			<OrderSummarySectionDetailsListContent>
				{{ slotTypeName }} {{ slotSubTypeName }}
			</OrderSummarySectionDetailsListContent>
		</li>
	</WidgetUnstyledSlotType>
</template>

<script lang="ts" setup>
import WidgetUnstyledSlotType from '@/Components/Widget/Unstyled/WidgetUnstyledSlotType.vue';
import OrderSummarySectionDetailsListContent from './OrderSummarySectionDetailsListContent.vue';
</script>
