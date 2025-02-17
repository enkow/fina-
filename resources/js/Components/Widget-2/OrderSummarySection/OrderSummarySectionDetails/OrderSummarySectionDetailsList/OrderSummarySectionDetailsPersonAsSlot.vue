<template>
	<WidgetUnstyledPersonAsSlot v-slot="{ slotsCount }">
		<li>
			<PeopeIcon />
			<OrderSummarySectionDetailsListContent>
				<span class="text-xs font-normal text-ui-black/60">x</span>
				{{ slotsCount }}
			</OrderSummarySectionDetailsListContent>
		</li>
	</WidgetUnstyledPersonAsSlot>
</template>

<script lang="ts" setup>
import WidgetUnstyledPersonAsSlot from '@/Components/Widget/Unstyled/WidgetUnstyledPersonAsSlot.vue';
import OrderSummarySectionDetailsListContent from './OrderSummarySectionDetailsListContent.vue';
import PeopeIcon from '@/Components/Widget-2/Icons/PeopleIcon.vue';
</script>
