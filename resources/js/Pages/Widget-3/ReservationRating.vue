<template>
	<Widget3Layout :without-navigation="true" :without-logout="true">
		<div v-if="ratedStatus === false">
			<p class="widget-header w-full text-center">
				{{ $t('widget.rate-reservation') }}
			</p>

			<form class="mx-auto mt-6 w-100 max-w-full space-y-3" @submit.prevent="submitReservationRate">
				<div class="flex justify-between">
					<div class="mt-1 w-1/2">{{ $t('rate.service') }}</div>
					<div class="flex w-1/2 justify-end">
						<star-rating
							v-model:rating="form.rate_service"
							:active-color="widgetColor"
							star-size="30"
							text-class="rating-text"></star-rating>
					</div>
				</div>

				<div class="flex">
					<div class="mt-1 w-1/2">{{ $t('rate.atmosphere') }}</div>
					<div class="flex w-1/2 justify-end">
						<star-rating
							v-model:rating="form.rate_atmosphere"
							:active-color="widgetColor"
							star-size="30"
							text-class="rating-text"></star-rating>
					</div>
				</div>

				<div class="flex">
					<div class="mt-1 w-1/2">{{ $t('rate.staff') }}</div>
					<div class="flex w-1/2 justify-end">
						<star-rating
							v-model:rating="form.rate_staff"
							:active-color="widgetColor"
							star-size="30"
							text-class="rating-text"></star-rating>
					</div>
				</div>

				<div class="space-y-1">
					<InputLabel :value="$t('main.comment')" class="text-widget capitalize" />
					<TextareaInput v-model="form.rate_content" class="rating-content transition" maxlength="400" />
				</div>

				<button class="widget-button !block !w-full text-center" type="submit">
					{{ capitalize($t('main.action.send')) }}
				</button>
			</form>
		</div>
		<div v-else class="mx-auto mt-30 w-[430px] max-w-full">
			<p class="widget-header w-full text-center">
				{{ $t('widget.reservation-rated-header') }}
			</p>
			<p class="text-center" v-html="$t('widget.reservation-rated-content')"></p>
		</div>
	</Widget3Layout>
</template>

<style>
.rating-text {
	@apply mt-1;
	color: v-bind(widgetColor);
}

.rating-content {
	border-color: v-bind(widgetColor);
}

.rating-content:focus {
	border-color: v-bind(widgetColor);
}
</style>

<script lang="ts" setup>
import Widget3Layout from '@/Layouts/Widget3Layout.vue';
import { Customer, Reservation } from '@/Types/models';
import StarRating from 'vue-star-rating';
import { useWidgetStore } from '@/Stores/widget';
import { router, useForm } from '@inertiajs/vue3';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import { capitalize } from 'vue';

const props = defineProps<{
	reservationEntity: Reservation;
	ratedStatus: boolean;
	customer: Customer;
	reservationNumberId: number;
}>();

const form = useForm({
	rate_service: 5,
	rate_atmosphere: 5,
	rate_staff: 5,
	rate_content: null,
});

function submitReservationRate() {
	form.post(
		route('widget.customers.reservations.rate', {
			club: widgetStore.club,
			encryptedCustomerId: props.customer.encryptedId,
			reservationNumber: props.reservationNumberId,
		}),
		{
			preserveState: true,
			preserveScroll: true,
			onSuccess: () => {
				router.reload();
			},
		},
	);
}

const widgetStore = useWidgetStore();
const widgetColor = widgetStore.widgetColor;
</script>
