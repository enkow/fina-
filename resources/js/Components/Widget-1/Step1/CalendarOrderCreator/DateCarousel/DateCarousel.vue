<template>
    <div
        class="relative z-10 h-35 rounded-t-10 bg-white px-13 shadow-[0px_0px_64px_0px_rgba(104,150,82,0.4)] shadow-ui-green/20 md:h-50 md:px-25 lg:px-32"
    >
        <button
            type="button"
            id="date-carousel-prev-button"
            aria-label="Previous slide"
            class="left-4.5 md:left-8 lg:left-14"
            :class="BUTTON_STYLES"
        >
            <PrevIcon />
        </button>
        <button
            type="button"
            id="date-carousel-next-button"
            aria-label="Next slide"
            class="right-4.5 md:right-8 lg:right-14"
            :class="BUTTON_STYLES"
        >
            <NextIcon />
        </button>
        <Swiper
            :slides-per-view="7"
            :slides-per-group="7"
            :space-between="12"
            :navigation="{
                prevEl: '#date-carousel-prev-button',
                nextEl: '#date-carousel-next-button',
            }"
            :allow-touch-move="false"
            :breakpoints="breakpoints"
            :modules="[Navigation]"
            class="relative h-full"
        >
            <SwiperSlide v-for="date in dates">
                <DateCarouselItem
                    :date="date"
                    :active="
                        formatCalendarDate(date) ===
                        formatCalendarDate(widgetStore.cachedFormStartAt)
                    "
                    :on-item-click="
                        (date) => {
                            widgetStore.date = formatCalendarDate(date);
                            widgetStore.resetSlots();
                        }
                    "
                    :disabled="
                        !widgetStore.club.widget_enabled ||
                        !widgetStore.availableDates.includes(
                            formatCalendarDate(date),
                        )
                    "
                />
            </SwiperSlide>
        </Swiper>
    </div>
</template>

<script setup lang="ts">
import DateCarouselItem from './DateCarouselItem.vue';
import PrevIcon from '@/Components/Widget-1/Icons/PrevIcon.vue';
import NextIcon from '@/Components/Widget-1/Icons/NextIcon.vue';
import { formatCalendarDate, generateCarouselDates, hasTime } from '@/Utils';
import { useWidgetStore } from '@/Stores/widget';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation } from 'swiper/modules';
import { SwiperOptions } from 'swiper/types';
import { watch } from 'vue';

import 'swiper/css';
import 'swiper/css/navigation';

const widgetStore = useWidgetStore();
const dates = generateCarouselDates({
    startAt: widgetStore.availableDates[0],
    limit: widgetStore.settings.reservation_max_advance_time.value as number,
});

const breakpoints: SwiperOptions['breakpoints'] = {
    0: {
        slidesPerView: 2,
        slidesPerGroup: 2,
    },
    375: {
        slidesPerView: 3,
        slidesPerGroup: 3,
    },
    640: {
        slidesPerView: 4,
        slidesPerGroup: 4,
    },
    768: {
        slidesPerView: 5,
        slidesPerGroup: 5,
    },
    1024: {
        slidesPerView: 7,
        slidesPerGroup: 7,
    },
};
const BUTTON_STYLES =
    'w-8 h-8 absolute top-1/2 -translate-y-1/2 flex items-center justify-center text-ui-green disabled:opacity-30 disabled:text-ui-black';

watch(
    () => widgetStore.form.duration,
    (value, prev) => {
        if (!hasTime(widgetStore.form.start_at) && value !== prev) {
            widgetStore.form.duration = 1;
        }
    },
    { immediate: true },
);
</script>

<style>
.swiper-button-prev {
    @apply -translate-x-4;
}

.swiper-button-next {
    @apply translate-x-4;
}

.swiper-button-prev::after,
.swiper-button-next::after {
    @apply block h-7 w-3.5 bg-ui-green content-[''];

    mask-size: contain;
    mask-repeat: no-repeat;
    mask-position: center;
}

.swiper-button-disabled::after {
    @apply bg-ui-black;
}
</style>
