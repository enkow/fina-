<template>
    <ul class="space-y-3.5">
        <li :class="LIST_ITEM_STYLES">
            <MapPinIcon />
            {{ widgetStore.club.name }}, {{ widgetStore.address }}
        </li>
        <li :class="LIST_ITEM_STYLES">
            <ClockIcon />
            {{ date
            }}{{
                widgetStore.showingStatuses.fullDayReservations
                    ? ''
                    : ', ' + time.from + ' - ' + time.to
            }}
        </li>
        <li v-if="widgetStore.specialOffer" :class="LIST_ITEM_STYLES">
            <BookmarkIcon />
            {{ $t('calendar.discount') }}: {{ widgetStore.specialOffer.name }}
        </li>
    </ul>
</template>

<script lang="ts" setup>
import MapPinIcon from '@/Components/Widget-1/Icons/MapPinIcon.vue';
import ClockIcon from '@/Components/Widget-1/Icons/ClockIcon.vue';
import BookmarkIcon from '@/Components/Widget-1/Icons/BookmarkIcon.vue';
import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';
import { formatCalendarOrderDate, formatCalendarOrderTime } from '@/Utils';

const widgetStore = useWidgetStore();

const date = computed(() =>
    formatCalendarOrderDate(
        widgetStore.form.start_at,
        widgetStore.currentLocale,
    ),
);
const time = computed(() =>
    formatCalendarOrderTime(
        widgetStore.form.start_at,
        widgetStore.form.duration,
    ),
);

const LIST_ITEM_STYLES =
    'flex gap-x-3.5 [&>svg]:mt-0.75 [&>svg]:text-ui-black [&>svg]:flex-shrink-0';
</script>
