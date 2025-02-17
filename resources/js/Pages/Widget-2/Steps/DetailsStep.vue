<template>
	<SpecialOffersSection />
	<section class="flex-1 overflow-auto md:h-fit md:flex-none md:overflow-visible">
		<SectionTitle class="hidden md:block">{{ $t('widget-2.choose-date') }}</SectionTitle>
		<div
			class="widget-2-scrollbar flex h-full flex-col gap-x-5 overflow-auto md:h-fit md:flex-row md:overflow-visible">
			<FullOrderDatePicker />
			<CompactOrderDatePicker />
			<OrderSettingsList />
		</div>
	</section>
</template>

<script lang="ts" setup>
import SpecialOffersSection from '@/Components/Widget-2/SpecialOffersSection/SpecialOffersSection.vue';
import SectionTitle from '@/Components/Widget-2/SectionTitle.vue';
import FullOrderDatePicker from '@/Components/Widget-2/FullOrderDatePicker.vue';
import CompactOrderDatePicker from '@/Components/Widget-2/CompactOrderDatePicker.vue';
import OrderSettingsList from '@/Components/Widget-2/OrderSettingsList/OrderSettingsList.vue';
import { onMounted } from 'vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
onMounted(() => {
	widgetStore.reloadSets();
});
</script>
