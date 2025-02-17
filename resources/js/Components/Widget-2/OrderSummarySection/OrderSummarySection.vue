<template>
	<section>
		<SectionTitle>{{ $t('widget-2.booking-details') }}</SectionTitle>
		<div
			class="flex w-[calc(100%+3.25rem)] -translate-x-6.5 flex-col-reverse gap-x-5 bg-ui-green p-1 text-xs xxs:p-2.5 xs:w-full xs:translate-x-0 xs:rounded-2xl md:grid md:grid-cols-3 md:flex-row md:bg-transparent md:p-0 md:text-sm">
			<OrderSummarySectionSpecialOffer />
			<OrderSummarySectionDetails />
		</div>
	</section>
</template>

<script lang="ts" setup>
import SectionTitle from '@/Components/Widget-2/SectionTitle.vue';
import OrderSummarySectionSpecialOffer from './OrderSummarySectionSpecialOffer.vue';
import OrderSummarySectionDetails from './OrderSummarySectionDetails/OrderSummarySectionDetails.vue';
</script>
