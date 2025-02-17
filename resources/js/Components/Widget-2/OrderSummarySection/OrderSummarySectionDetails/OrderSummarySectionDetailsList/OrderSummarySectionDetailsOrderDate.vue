<template>
	<WidgetUnstyledOrderDate v-slot="{ date }">
		<li>
			<CalendarIcon />
			<OrderSummarySectionDetailsListContent>{{ date }}</OrderSummarySectionDetailsListContent>
		</li>
	</WidgetUnstyledOrderDate>
</template>

<script lang="ts" setup>
import WidgetUnstyledOrderDate from '@/Components/Widget/Unstyled/WidgetUnstyledOrderDate.vue';
import OrderSummarySectionDetailsListContent from './OrderSummarySectionDetailsListContent.vue';
import CalendarIcon from '@/Components/Widget-2/Icons/CalendarIcon.vue';
</script>
