<template>
	<Swiper
		:slides-per-view="slidesPerView"
		:space-between="spaceBetween"
		:breakpoints="breakpoints"
		:navigation="navigation"
		:modules="[Navigation]">
		<SwiperSlide v-for="specialOffer in widgetStore.specialOffers || []">
			<slot
				name="item"
				:special-offer="specialOffer"
				:active="widgetStore.form.special_offer_id === specialOffer.id"
				:on-click="() => widgetStore.selectSpecialOffer(specialOffer)" />
		</SwiperSlide>
	</Swiper>
</template>

<script lang="ts" setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { SwiperOptions, NavigationOptions } from 'swiper/types';
import { Navigation } from 'swiper/modules';
import { useWidgetStore } from '@/Stores/widget';

import 'swiper/css';

defineProps<{
	slidesPerView?: number;
	spaceBetween?: number;
	breakpoints?: SwiperOptions['breakpoints'];
	navigation?: NavigationOptions;
}>();

const widgetStore = useWidgetStore();
</script>
