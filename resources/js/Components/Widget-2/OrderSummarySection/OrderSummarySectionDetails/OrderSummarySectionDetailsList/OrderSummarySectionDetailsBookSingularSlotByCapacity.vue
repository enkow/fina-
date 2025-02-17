<template>
	<WidgetUnstyledBookSingularSlotByCapacity v-slot="{ capacity }">
		<li>
			<PeopleIcon />
			<OrderSummarySectionDetailsListContent>
				<span class="text-xs font-normal text-ui-black/60">x</span>
				{{ capacity }}
			</OrderSummarySectionDetailsListContent>
		</li>
	</WidgetUnstyledBookSingularSlotByCapacity>
</template>

<script lang="ts" setup>
import WidgetUnstyledBookSingularSlotByCapacity from '@/Components/Widget/Unstyled/WidgetUnstyledBookSingularSlotByCapacity.vue';
import OrderSummarySectionDetailsListContent from './OrderSummarySectionDetailsListContent.vue';
import PeopleIcon from '@/Components/Widget-2/Icons/PeopleIcon.vue';
</script>
