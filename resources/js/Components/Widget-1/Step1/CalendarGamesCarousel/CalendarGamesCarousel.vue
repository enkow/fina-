<template>
	<section v-if="games">
		<CalendarCarouselWrapper :navigation="{ prevButtonId: PREV_BUTTON_ID, nextButtonId: NEXT_BUTTON_ID }">
			<Swiper
				:key="key"
				:breakpoints="breakpoints"
				:navigation="navigation"
				:modules="[Navigation, Pagination]"
				v-bind="{ ...(games.length > 1 && { pagination }) }"
				@slide-change="handleSlideChange">
				<SwiperSlide v-for="game in games">
					<CalendarGamesCarouselItem
					  	:class="{ '!cursor-default': games.length === 1 }"
						:game="game"
						:active="widgetStore.form.game_id === game.id"
						@click="(game) => widgetStore.selectGame(game)" />
				</SwiperSlide>
			</Swiper>
		</CalendarCarouselWrapper>
	</section>
</template>

<script lang="ts" setup>
import { computed, watch, ref } from "vue";
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Swiper as SwiperClass } from 'swiper';
import CalendarCarouselWrapper from '@/Components/Widget-1/Shared/CalendarCarouselWrapper.vue';
import { Navigation, Pagination } from 'swiper/modules';
import { createSwiperPagination } from '@/Utils';
import { NavigationOptions, SwiperOptions } from 'swiper/types';
import CalendarGamesCarouselItem from './CalendarGamesCarouselItem.vue';
import { useWidgetStore } from '@/Stores/widget';
import { Game } from "@/Types/models";

const widgetStore = useWidgetStore();

const key = ref(0)
const games = computed<Game[]>(() => {
  return (
    widgetStore.club.games?.filter(({ pivot }) =>
      Boolean(pivot.enabled_on_widget)
    ) || []
  );
});

const PREV_BUTTON_ID = 'games-carousel-prev-button';
const NEXT_BUTTON_ID = 'games-carousel-next-button';

watch(
	() => widgetStore.form.game_id,
	(gameId) => {
		if (!gameId && gameId !== 0) {
			widgetStore.selectGame(games.value[0]);
			key.value = Math.random()
		}
	},
	{ immediate: true },
);

const pagination = createSwiperPagination();
const navigation: NavigationOptions = { prevEl: `#${PREV_BUTTON_ID}`, nextEl: `#${NEXT_BUTTON_ID}` };
const breakpoints: SwiperOptions['breakpoints'] = {
	0: {
		slidesPerView: 1,
	},
	768: {
		slidesPerView: games.value.length > 1 ? 2 : games.value.length,
		spaceBetween: 23,
	},
	1024: {
		slidesPerView: games.value.length > 2 ? 3 : games.value.length,
		spaceBetween: 40,
	},
};

const handleSlideChange = ({ params, activeIndex }: SwiperClass) => {
	if (params.slidesPerView === 1) {
		widgetStore.selectGame(games.value[activeIndex]);
	}
};


</script>
