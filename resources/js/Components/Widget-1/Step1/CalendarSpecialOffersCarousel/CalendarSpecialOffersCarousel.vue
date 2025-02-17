<template>
	<section v-if="widgetStore.specialOffers?.length">
		<CalendarCarouselWrapper :navigation="{ prevButtonId: PREV_BUTTON_ID, nextButtonId: NEXT_BUTTON_ID }">
			<Swiper
				@swiper="(instance) => (swiper = instance)"
				:breakpoints="breakpoints"
				:navigation="navigation"
				:pagination="pagination"
				:modules="[Navigation, Pagination]">
				<SwiperSlide v-for="specialOffer in specialOffers" class="!h-auto">
					<CalendarSpecialOffer
						:specialOffer="specialOffer"
						:active="widgetStore.form.special_offer_id === specialOffer.id"
						:on-select="(specialOffer) => widgetStore.selectSpecialOffer(specialOffer)" />
				</SwiperSlide>
			</Swiper>
		</CalendarCarouselWrapper>
	</section>
</template>

<script lang="ts" setup>
import CalendarSpecialOffer from './CalendarSpecialOffer.vue';
import CalendarCarouselWrapper from '@/Components/Widget-1/Shared/CalendarCarouselWrapper.vue';
import { createSwiperPagination } from '@/Utils';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination } from 'swiper/modules';
import { NavigationOptions, Swiper as SwiperType, SwiperOptions } from 'swiper/types';
import { useWidgetStore } from '@/Stores/widget';
import { computed, ref, watch } from 'vue';

const widgetStore = useWidgetStore();
const pagination = createSwiperPagination();

const specialOffers = computed(() => widgetStore.specialOffers || []);
const slidesPerView = computed(() => (specialOffers.value.length > 1 ? 2 : 1));

const PREV_BUTTON_ID = 'special-offer-carousel-prev-button';
const NEXT_BUTTON_ID = 'special-offer-carousel-next-button';

const navigation: NavigationOptions = { prevEl: `#${PREV_BUTTON_ID}`, nextEl: `#${NEXT_BUTTON_ID}` };
const breakpoints: SwiperOptions['breakpoints'] = {
	0: {
		slidesPerView: 1,
		spaceBetween: 18,
	},
	750: {
		slidesPerView: slidesPerView.value,
		spaceBetween: 18,
	},
	1024: {
		slidesPerView: slidesPerView.value,
		spaceBetween: 25,
	},
	1280: {
		slidesPerView: slidesPerView.value,
		spaceBetween: 40,
	},
};

const swiper = ref<SwiperType | null>(null);

watch([specialOffers, swiper], () => {
	if (swiper.value && window.screen.width >= 750) {
		swiper.value.params.slidesPerView = slidesPerView.value;
	}
});
</script>
