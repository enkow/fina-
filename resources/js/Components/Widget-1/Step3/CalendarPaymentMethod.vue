<template>
	<CalendarSectionTitle>
		{{ $t('calendar.choose-a-payment-method') }}
	</CalendarSectionTitle>
	<p v-if="options.length === 0" class="text-center text-ui-red md:text-lg">
		{{ $t('calendar.no-payment-method-has-been-set-up') }}
	</p>
	<div class="space-y-5" v-else>
		<CalendarRadioGroup
			v-model="paymentMethod"
			:title="$t('calendar.choose-a-payment-method')"
			:options="options" />

		<TippyWrapper v-bind="!paymentMethod 
                    ? { content: $t('calendar.choose-a-payment-method') }
                    : {}">
			<WidgetButton
				:disabled="!paymentMethod || widgetStore.paymentSubmitted"
				:loading="isLoading"
				@click="handlePayment"
				fill>
				{{ $t('calendar.book-now') }}
				<CardTickIcon />
			</WidgetButton>
		</TippyWrapper>

		<template v-if="paymentMethod === 'online'" >
			<p class="text-center text-sm text-ui-black/60 md:text-lg">
				{{ $t('calendar.payment-warning', { minutes: '5' }) }}
			</p>
			<p v-if="paymentMethod === 'online' && isCommision" class="text-center text-sm text-ui-black/60 md:text-lg">
				{{
					$t('widget.handling-fee-info-fixed', {
						fixed: commisionPrice,
					})
				}}
			</p>
		</template>
</div>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import CardTickIcon from '@/Components/Widget-1/Icons/CardTickIcon.vue';
import CalendarRadioGroup from '@/Components/Widget-1/Shared/CalendarRadioGroup.vue';
import CalendarSectionTitle from './CalendarSectionTitle.vue';
import { computed, ref, watch } from 'vue';
import { wTrans } from 'laravel-vue-i18n';
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';
import { useWidgetPaymentMethodStatuses } from '@/Composables/useWidgetPaymentMethodStatuses';
import TippyWrapper from '@/Components/TippyWrapper.vue';
import { useWidgetAdditionalComission } from '@/Composables/widget/useWidgetAdditionalComission';
import { useWidgetOfflinePaymentAvaliable } from '@/Composables/widget/useWidgetOfflinePaymentAvaliable';

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();
const { offlineMethodStatus, onlineMethodStatus, isOnlinePaymentsDisabled } =
	useWidgetPaymentMethodStatuses();
const { commisionPrice, isCommision } = useWidgetAdditionalComission();
const { offlinePaymentAvailable, offlineDisabledMessage } = useWidgetOfflinePaymentAvaliable();


const options = computed(() => [
	...(onlineMethodStatus.value !== undefined
		? [
				{
					title: wTrans('calendar.online-payment').value,
					label: wTrans('widget.online-reservation-limit-exceeded', {
						count: widgetStore.customerOnlineActiveReservationsCount.toString(),
					}).value,
					value: 'online',
					// disabled: isOnlinePaymentsDisabled.value,
				},
		  ]
		: []),
	...(offlineMethodStatus.value ?
		[
			{ 
				title: wTrans('calendar.offline-payment').value,
				value: 'offline',
				disabled: !offlinePaymentAvailable.value,
				message: offlineDisabledMessage.value,
			}
		] 
		: []),
]);

const paymentMethod = ref(options.value.length === 1 ? options.value[0].value : '');

watch(
	() => widgetStore.reservationData,
	(value) => {
		if (value) {
			routingStore.nextStep();
		}
	},
);

const isLoading = ref(false)
async function handlePayment() {
	isLoading.value = true

	await widgetStore.store(paymentMethod.value)

	isLoading.value = false
}
</script>
