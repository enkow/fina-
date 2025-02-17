<template>
    <CalendarBookingContainer class="mt-auto">
        <div class="mb-2.5 flex items-center justify-between">
            <p class="text-xl font-bold text-ui-black md:text-2xl">
                {{ $t('calendar.total') }}
            </p>
            <p class="text-3xl font-medium text-ui-green md:text-4xl">
                {{ formatPrice(widgetStore.finalPrice)}}
                {{ widgetStore.getCurrencySymbol() }}
            </p>
        </div>
        <WidgetInputWrapper
            v-bind="{
                ...(widgetStore.discountCodeStatus === true && {
                    success: $t('calendar.discount-code-valid'),
                }),
                ...(widgetStore.discountCodeStatus === false && {
                    error: $t('calendar.discount-code-invalid'),
                }),
            }"
        >
            <WidgetInput
                v-model="widgetStore.form.discount_code"
                :placeholder="$t('calendar.coupon-code')"
            />
        </WidgetInputWrapper>
    </CalendarBookingContainer>
</template>

<script lang="ts" setup>
import WidgetInput from '@/Components/Widget/Ui/WidgetInput.vue';
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import CalendarBookingContainer from './CalendarBookingContainer.vue';
import { useWidgetStore } from '@/Stores/widget';
import { formatPrice } from '@/Utils';

const widgetStore = useWidgetStore();
</script>
