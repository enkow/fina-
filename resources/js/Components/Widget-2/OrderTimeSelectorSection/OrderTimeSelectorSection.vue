<template>
	<WidgetUnstyledTimeSelector v-slot="{ blocks, back }">
		<section class="widget-2-scrollbar flex flex-col overflow-auto">
			<OrderTimeSelectorSectionEmptyDataView v-if="blocks.length === 0" @button-click="back" />
			<OrderTimeSelectorSectionDefaultView v-else :blocks="blocks" />
		</section>
	</WidgetUnstyledTimeSelector>
</template>

<script lang="ts" setup>
import WidgetUnstyledTimeSelector from '@/Components/Widget/Unstyled/WidgetUnstyledTimeSelector/WidgetUnstyledTimeSelector.vue';
import OrderTimeSelectorSectionDefaultView from './OrderTimeSelectorSectionDefaultView.vue';
import OrderTimeSelectorSectionEmptyDataView from './OrderTimeSelectorSectionEmptyDataView.vue';
</script>
