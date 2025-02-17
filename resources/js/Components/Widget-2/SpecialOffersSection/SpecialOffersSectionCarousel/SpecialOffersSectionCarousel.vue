<template>
	<div class="relative">
		<button
			:id="PREV_BUTTON_ID"
			:class="[BUTTON_CLASS, 'left-0 -translate-x-3.5']"
			aria-label="Previous slide">
			<PrevIcon />
		</button>
		<WidgetUnstyledSpecialOffersCarousel
			:slides-per-view="2"
			:space-between="20"
			:breakpoints="breakpoints"
			:navigation="navigation">
			<template #item="{ specialOffer, active, onClick }">
				<SpecialOffersSectionCarouselItem :specialOffer="specialOffer" :active="active" @click="onClick" />
			</template>
		</WidgetUnstyledSpecialOffersCarousel>
		<button :id="NEXT_BUTTON_ID" :class="[BUTTON_CLASS, 'right-0 translate-x-3.5']" aria-label="Next slide">
			<PrevIcon class="rotate-180" />
		</button>
	</div>
</template>

<script lang="ts" setup>
import WidgetUnstyledSpecialOffersCarousel from '@/Components/Widget/Unstyled/WidgetUnstyledSpecialOffersCarousel.vue';
import SpecialOffersSectionCarouselItem from './SpecialOffersSectionCarouselItem.vue';
import PrevIcon from '@/Components/Widget-2/Icons/PrevIcon.vue';
import { NavigationOptions, SwiperOptions } from 'swiper/types';

const PREV_BUTTON_ID = 'special-offers-carousel-prev-button';
const NEXT_BUTTON_ID = 'special-offers-carousel-next-button';

const BUTTON_CLASS =
	'absolute top-1/2 -translate-y-1/2 text-ui-green-950 transition-opacity duration-200 disabled:opacity-50 disabled:cursor-not-allowed';

const navigation: NavigationOptions = {
	prevEl: `#${PREV_BUTTON_ID}`,
	nextEl: `#${NEXT_BUTTON_ID}`,
};
const breakpoints: SwiperOptions['breakpoints'] = {
	0: {
		slidesPerView: 1,
	},
	768: {
		slidesPerView: 2,
	},
};
</script>

<style>
.swiper-slide {
	@apply !h-auto;
}
</style>
