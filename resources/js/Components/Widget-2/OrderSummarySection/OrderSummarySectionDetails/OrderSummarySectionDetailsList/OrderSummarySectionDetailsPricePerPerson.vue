<template>
	<WidgetUnstyledPricePerPerson v-slot="{ pricePerPerson, currency }">
		<li>
			<ShoesIcon />
			<OrderSummarySectionDetailsListContent>
				{{ pricePerPerson }} {{ currency }}
			</OrderSummarySectionDetailsListContent>
		</li>
	</WidgetUnstyledPricePerPerson>
</template>

<script lang="ts" setup>
import WidgetUnstyledPricePerPerson from '@/Components/Widget/Unstyled/WidgetUnstyledPricePerPerson.vue';
import OrderSummarySectionDetailsListContent from './OrderSummarySectionDetailsListContent.vue';
import ShoesIcon from '@/Components/Widget-2/Icons/ShoesIcon.vue';
</script>
