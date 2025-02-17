<template>
    <CalendarStepper />
    <CalendarGamesCarousel />
    <CalendarSpecialOffersCarousel />

    <WidgetAlert v-if="widgetStore?.settings['widget_message']?.value">
        <template #title>{{
            widgetStore.settings['widget_message'].value
        }}</template>
    </WidgetAlert>

    <CalendarOrderCreator />
    <template v-if="widgetStore.club.widget_enabled">
        <CalendarAnnouncementAlerts color="white" source="bottom" />
        <CalendarOrderSummary />
        <CalendarStep1Navigation />
    </template>
</template>

<script lang="ts" setup>
import CalendarStepper from '@/Components/Widget-1/Shared/CalendarStepper/CalendarStepper.vue';
import CalendarOrderCreator from '@/Components/Widget-1/Step1/CalendarOrderCreator/CalendarOrderCreator.vue';
import CalendarOrderSummary from '@/Components/Widget-1/Shared/CalendarOrderSummary/CalendarOrderSummary.vue';
import CalendarGamesCarousel from '@/Components/Widget-1/Step1/CalendarGamesCarousel/CalendarGamesCarousel.vue';
import CalendarSpecialOffersCarousel from '@/Components/Widget-1/Step1/CalendarSpecialOffersCarousel/CalendarSpecialOffersCarousel.vue';
import CalendarStep1Navigation from '@/Components/Widget-1/Step1/CalendarStep1Navigation.vue';
import CalendarAnnouncementAlerts from '@/Components/Widget-1/Step1/CalendarAnnouncementAlerts.vue';
import WidgetAlert from '@/Components/Widget/Ui/WidgetAlert.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
