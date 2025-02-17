<template>
    <div class="space-y-8">
        <div class="flex flex-col lg:grid lg:grid-cols-11">
            <!-- 'after:absolute after:bottom-0 after:left-0 after:h-0.75 after:w-full after:bg-order-summary-border-horizontal after:bg-clip-content after:px-10 lg:after:hidden', -->
            <div
                :class="[
                    CONTAINER_STYLES,
                    'relative lg:col-span-8 lg:px-15 text-ui-green shadow-calendar-box shadow-ui-green/20',
                ]"
            >
                <CalendarHorizontalLine
                    class="w-full h-[3px] absolute bottom-0 left-1/2 -translate-x-1/2 px-9.5 translate-y-1/2 lg:hidden opacity-60"
                />
                <CalendarVerticalLine
                    class="absolute h-full w-[3px] right-0 top-1/2 -translate-y-1/2 py-9.5 translate-x-1/2 hidden lg:block opacity-60"
                />

                <h2 :class="HEADING_STYLES">{{ $t('calendar.summary') }}</h2>
                <CalendarOrderSummaryList />
            </div>
            <div
                class="flex flex-col md:flex-row md:justify-between lg:col-span-3 lg:aspect-square lg:w-full lg:flex-col lg:px-7 shadow-calendar-box shadow-ui-green/20"
                :class="CONTAINER_STYLES"
            >
                <h2 :class="HEADING_STYLES">
                    {{ $t('calendar.total-cost') }}:
                </h2>
                <div>
                    <p class="text-4xl font-bold text-ui-green xxs:text-5xl">
                        {{ price }}
                    </p>
                    <p
                        v-if="specialOffer && widgetStore.finalPrice > 0"
                        class="mt-2 break-words text-ui-black/60"
                    >
                        {{
                            $t('calendar.saved-money', {
                                percentages: specialOffer.value.toString(),
                                discount: specialOffer.name,
                            })
                        }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue';
import CalendarOrderSummaryList from './CalendarOrderSummaryList/CalendarOrderSummaryList.vue';
import { useWidgetStore } from '@/Stores/widget';
import { formatPrice } from '@/Utils';
import CalendarHorizontalLine from './CalendarHorizontalLine.vue';
import CalendarVerticalLine from './CalendarVerticalLine.vue';

const CONTAINER_STYLES =
    'rounded-10 bg-white px-2.5 py-6 xxs:p-6 md:px-13 md:py-12';
const HEADING_STYLES = 'text-2xl font-bold text-ui-black mb-8 mt-0';

const widgetStore = useWidgetStore();

const specialOffer = computed(() =>
    widgetStore.specialOffers?.find(
        ({ id }) => widgetStore.form.special_offer_id === id,
    ),
);

const price = computed(() => {
    const price = formatPrice(widgetStore.finalPrice);

    if (price) {
        return `${price} ${widgetStore.getCurrencySymbol()}`;
    }

    return '-';
});
</script>
