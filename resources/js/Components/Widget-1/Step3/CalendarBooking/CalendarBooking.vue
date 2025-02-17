<template>
    <article
        class="flex w-full flex-col break-words rounded-10 bg-white px-6 py-9 text-base text-ui-black/60 md:p-10 md:text-lg"
    >
        <h2 class="mb-5.5 mt-0 text-xl font-bold text-ui-black md:text-2xl">
            {{ $t('calendar.your-booking') }}
        </h2>
        <div class="flex flex-1 flex-col gap-y-8 divide-y divide-ui-black/10">
            <CalendarBookingInfo />
            <CalendarBookingDetails />
            <CalendarBookingSets />
            <CalendarBookingTotalPrice />
        </div>
    </article>
</template>

<script lang="ts" setup>
import CalendarBookingSets from './CalendarBookingSets.vue';
import CalendarBookingInfo from './CalendarBookingInfo.vue';
import CalendarBookingTotalPrice from './CalendarBookingTotalPrice.vue';
import CalendarBookingDetails from './CalendarBookingDetails.vue';
</script>
