<template>
	<WidgetUnstyledPricePerPerson v-slot="{ pricePerPersonCount }">
		<li>
			<PeopleIcon />
			<OrderSummarySectionDetailsListContent>
				<span class="text-xs font-normal text-ui-black/60">x</span>
				{{ pricePerPersonCount }}
			</OrderSummarySectionDetailsListContent>
		</li>
	</WidgetUnstyledPricePerPerson>
</template>

<script lang="ts" setup>
import WidgetUnstyledPricePerPerson from '@/Components/Widget/Unstyled/WidgetUnstyledPricePerPerson.vue';
import OrderSummarySectionDetailsListContent from './OrderSummarySectionDetailsListContent.vue';
import PeopleIcon from '@/Components/Widget-2/Icons/PeopleIcon.vue';
</script>
