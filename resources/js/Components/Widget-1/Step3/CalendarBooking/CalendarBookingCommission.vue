<template>
    <CalendarBookingContainer
        v-if="widgetStore?.priceObject?.reservationFee?.commisionFixed"
        class="mt-auto"
    >
        <CalendarBookingSectionHeader
            :title="title"
            :price="widgetStore.priceObject.reservationFee.commisionFixed"
        />
    </CalendarBookingContainer>
</template>

<script lang="ts" setup>
import CalendarBookingContainer from './CalendarBookingContainer.vue';
import CalendarBookingSectionHeader from './CalendarBookingSectionHeader.vue';
import { wTrans } from 'laravel-vue-i18n';

import { useWidgetStore } from '@/Stores/widget';

const title = wTrans('widget.calendarCommission').value;

const widgetStore = useWidgetStore();
</script>
