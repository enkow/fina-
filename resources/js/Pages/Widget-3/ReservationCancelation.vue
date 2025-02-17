<template>
	<Widget3Layout :without-navigation="true" :without-logout="true">
		<div
			v-if="cancelationRejectedByCustomerStatus === true"
			class="text-widget mx-auto mt-30 w-full text-center">
			<p class="widget-header w-full text-center">
				{{
					$t('widget.your-reservation-is-still-active', {
						reservation_number: props.reservationNumberFormatted,
					})
				}}
			</p>
			{{ $t('widget.you-can-close-this-tab') }}
		</div>
		<div
			v-else-if="cancelationConfirmedByCustomerStatus === true"
			class="text-widget mx-auto mt-30 w-full text-center">
			<p class="widget-header w-full text-center">
				{{ $t('widget.reservation-cancelation') }}
			</p>
			<p class="text-center" v-html="$t('widget.reservation-canceled')"></p>
		</div>
		<div v-else-if="!isCanceled" class="text-widget mx-auto mt-30 w-full text-center">
			<p class="widget-header w-full text-center">
				{{ $t('reservation.cancelation-confirmation') }}
			</p>
			<div v-if="paymentMethod.online === true">
				<p v-if="canBeRefunded">
					{{ $t('widget.refund-possible') }}
				</p>
				<p v-else>
					{{ $t('widget.refund-not-possible', { club_name: props.club.name }) }}
				</p>
			</div>

			<form class="mx-auto flex w-100 space-x-3" @submit.prevent="submitReservationCancelation">
				<button
					class="widget-button text-widget mx-auto mt-4 !block !w-full max-w-53 text-center"
					type="submit">
					{{ capitalize($t('main.yes')) }}
				</button>
				<button
					class="widget-button text-widget mx-auto mt-4 !block !w-full max-w-53 text-center"
					@click.prevent="cancelationRejectedByCustomerStatus = true">
					{{ capitalize($t('main.no')) }}
				</button>
			</form>
		</div>
		<div v-else class="w-120 mx-auto mt-30">
			<p class="widget-header w-full text-center">
				{{ $t('widget.reservation-cancelation') }}
			</p>
			<p class="text-center" v-html="$t('widget.reservation-already-canceled')"></p>
		</div>
	</Widget3Layout>
</template>

<style>
.rating-text {
	@apply mt-1;
	color: v-bind(widgetColor);
}

.text-widget {
	color: v-bind(widgetColor);
}
</style>

<script lang="ts" setup>
import Widget3Layout from '@/Layouts/Widget3Layout.vue';
import { useWidgetStore } from '@/Stores/widget';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { capitalize, ref } from 'vue';
import { Club, PaymentMethod } from '@/Types/models';

const props = defineProps<{
	club: Club;
	encryptedCustomerId: string;
	reservationNumberId: number;
	reservationNumberFormatted: string;
	isCanceled: boolean;
	refundTimeLimit: string;
	canBeRefunded: boolean;
	paymentMethod: PaymentMethod;
}>();

const form = useForm({});

function submitReservationCancelation() {
	form.post(
		route('widget.customers.reservations.cancel-action', {
			club: widgetStore.club,
			encryptedCustomerId: props.encryptedCustomerId,
			reservationNumber: props.reservationNumberId,
		}),
		{
			preserveState: true,
			preserveScroll: true,
			onSuccess: () => {
				cancelationConfirmedByCustomerStatus.value = true;
			},
		},
	);
}

const cancelationRejectedByCustomerStatus = ref<boolean>(false);
const cancelationConfirmedByCustomerStatus = ref<boolean>(false);

const widgetStore = useWidgetStore();
const widgetColor = widgetStore.widgetColor;
</script>
