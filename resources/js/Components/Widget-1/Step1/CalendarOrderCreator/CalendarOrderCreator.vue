<template>
    <div>
        <DateCarousel />
        <div
            class="space-y-15 rounded-b-10 bg-white p-6 shadow-calendar-box shadow-ui-green/20 md:p-12.25 lg:p-15"
        >
            <DisabledWidgetAlert v-if="!widgetStore.club.widget_enabled" />
            <template v-else>
                <CalendarAnnouncementAlerts source="top" />
                <WidgetDynamicAlert />
                <PriceAnnouncementAlert />
                <FixedReservationDurationAlert />
                <TimeSelector />
                <template
                    v-if="
                        widgetStore.showingStatuses.fullDayReservations ||
                        hasTime(widgetStore.form.start_at)
                    "
                >
                    <AlleySelector />
                    <GameSettings />
                </template>
            </template>
        </div>
    </div>
</template>

<script lang="ts" setup>
import DateCarousel from './DateCarousel/DateCarousel.vue';
import TimeSelector from './TimeSelector/TimeSelector.vue';
import AlleySelector from './AlleySelector/AlleySelector.vue';
import WidgetDynamicAlert from './WidgetDynamicAlert.vue';
import GameSettings from './GameSettings/GameSettings.vue';
import DisabledWidgetAlert from './DisabledWidgetAlert.vue';
import PriceAnnouncementAlert from './PriceAnnouncementAlert.vue';
import CalendarAnnouncementAlerts from '../CalendarAnnouncementAlerts.vue';
import FixedReservationDurationAlert from './FixedReservationDurationAlert.vue';
import { useWidgetStore } from '@/Stores/widget';
import { hasTime } from '@/Utils';

const widgetStore = useWidgetStore();
</script>
