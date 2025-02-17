<template>
	<CalendarStepper />
	<div class="mx-auto w-full max-w-190 text-center text-ui-black/60 md:text-lg">
		<h2 class="my-0 text-lg font-bold text-ui-black md:text-xl lg:text-2xl">
			{{ statusHeading }}
		</h2>
		<p class="mt-2 text-lg font-bold text-ui-green md:text-xl lg:text-2xl">
			{{
				$t('widget.reservation-number', {
					reservationId: reservationNumber,
				})
			}}
		</p>
		<CalendarBookingStatus
			:status-message="statusBadge"
			:payment-message="paymentBadge"
			:success="isSuccess" />
		<div class="mt-9 space-y-1.5 md:mt-12">
			<p>{{ $t('calendar.payment-ready-info') }}</p>
			<button type="button" class="font-medium text-ui-green underline" @click="widgetStore.fullFormReset">
				{{ $t('calendar.start-over') }}
			</button>
		</div>
	</div>
</template>

<script lang="ts" setup>
import CalendarStepper from '@/Components/Widget-1/Shared/CalendarStepper/CalendarStepper.vue';
import CalendarBookingStatus from '@/Components/Widget-1/Step4/CalendarBookingStatus.vue';
import { useWidgetPayment } from '@/Composables/useWidgetPayment';
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';
import Pusher from 'pusher-js';
import { onMounted, ref } from 'vue';

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();
const { statusHeading, reservationNumber, statusBadge, paymentBadge, isSuccess } = useWidgetPayment();
</script>
