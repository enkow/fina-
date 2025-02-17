<template>
	<div class="col-span-4 space-y-2">
		<InputLabel class="capitalize" required>{{ $t('main.date') }}</InputLabel>
		<Datetimepicker
			v-model="form.start_at"
			:minutes-increment="clubSettings['calendarTimeScale']"
			placeholder=" "
			:disabled="isTimerEnabled" />
		<div v-if="form.errors.start_at" class="error">
			{{ form.errors.start_at }}
		</div>
		<div v-if="form.errors.slot_id" class="error">
			{{ form.errors.slot_id }}
		</div>
	</div>

	<div class="col-span-4 space-y-2">
		<InputLabel class="capitalize" required>
			{{ usePage().props.gameTranslations[game.id]['slot-name'] }}
		</InputLabel>
		<SimpleSelect v-model="singleSlotId" :options="slotOptions" class="w-full" />
		<div v-if="form.errors.slot_ids" class="error">
			{{ form.errors.slot_id }}
		</div>
	</div>

	<div
		v-if="!clubSettings['fullDayReservationStatus'] && !clubSettings['fixedReservationDurationStatus']"
		class="col-span-4 space-y-2">
		<InputLabel class="capitalize" required>{{ $t('reservation.duration-time') }}</InputLabel>
		<SimpleSelect
			v-model="form.duration"
			:disabled="isTimerEnabled"
			:readonly="isPaid"
			:options="durationOptions"
			class="w-full" />
		<div v-if="form.errors.duration" class="error">
			{{ form.errors.duration }}
		</div>
	</div>
	<div class="col-span-12 grid grid-cols-12 gap-x-5">
		<div class="col-span-4 space-y-2">
			<InputLabel class="capitalize">{{ $t('reservation.reservation-type') }}</InputLabel>
			<SimpleSelect
				:placeholder="$t('reservation-type.no-reservation-type')"
				v-model="form.reservation_type_id"
				:options="reservationTypeOptions"
				class="w-full" />
			<div v-if="form.errors.reservation_type_id" class="error">
				{{ form.errors.reservation_type_id }}
			</div>
		</div>
		<div class="col-span-4 space-y-2">
			<InputLabel class="capitalize">{{ $t('reservation.discount-code') }}</InputLabel>
			<SimpleSelect
				:placeholder="$t('discount-code.no-discount-code')"
				v-model="form.discount_code_id"
				:disabled="form.club_reservation"
				:readonly="isPaid"
				:options="discountCodeOptions"
				class="w-full" />
			<div v-if="form.errors.discount_code_id" class="error">
				{{ form.errors.discount_code_id }}
			</div>
		</div>
		<div class="col-span-4 space-y-2">
			<InputLabel class="capitalize">{{ $t('reservation.special-offer') }}</InputLabel>
			<SimpleSelect
				:placeholder="$t('special-offer.no-special-offer')"
				v-model="form.special_offer_id"
				:disabled="form.club_reservation"
				:readonly="isPaid"
				:options="specialOfferOptions"
				class="w-full" />
			<div v-if="form.errors.special_offer_id" class="error">
				{{ form.errors.special_offer_id }}
			</div>
		</div>
	</div>

	<div
		v-if="features.price_per_person.length && clubSettings['pricePerPersonType']"
		class="col-span-12 space-y-2 md:col-span-3">
		<InputLabel required>
			{{
				usePage().props.clubSettings['price_per_person_type_' + features.price_per_person[0].id].value === 1
					? features.price_per_person[0].translations['reservation-form-person-count-name'] +
					  ' ' +
					  formatAmount(
							usePage().props.clubSettings['price_per_person_' + features.price_per_person[0].id].value,
					  ) +
					  '/' +
					  features.price_per_person[0].translations['person-short']
					: features.price_per_person[0].translations['person-count']
			}}
		</InputLabel>
		<SimpleSelect
			:searchable="true"
			:filterable="true"
			v-model="form.features[features.price_per_person[0].id].person_count"
			:options="pricePerPersonOptions"
			:readonly="isPaid"
			class="dropdown-fixed-height w-full" />
		<div
			v-if="form.errors && form.errors[`features.${features.price_per_person[0].id}.person_count`]"
			class="error">
			{{ form.errors[`features.${features.price_per_person[0].id}.person_count`] }}
		</div>
	</div>

	<div
		class="col-span-4 space-y-2"
		:class="{ 'md:!col-span-3': features.price_per_person.length && clubSettings['pricePerPersonType'] }">
		<InputLabel class="capitalize" required>
			{{ $t('main.price') }} [{{ currencySymbols[usePage().props.club.country.currency] }}]
		</InputLabel>
		<TextInput
			v-model="form.price"
			:disabled="form.club_reservation || isTimerEnabled"
			:readonly="isPaid"
			@input="priceChangedManually" />
		<div v-if="form.errors.price" class="error">
			{{ form.errors.price }}
		</div>
	</div>

	<div
		class="col-span-4 space-y-2"
		:class="{ 'md:!col-span-3': features.price_per_person.length && clubSettings['pricePerPersonType'] }">
		<InputLabel>{{ capitalize($t('main.final-amount')) }}</InputLabel>
		<TextContainer class="col-span-2" :class="{ disabled: isTimerEnabled }">
			{{ formatAmount(form.club_reservation ? 0 : finalPrice) }}
		</TextContainer>
	</div>
	<div
		class="col-span-4 space-y-2"
		v-if="form.status === 0 || reservation.payment_method_online == false"
		:class="{ 'md:!col-span-3': features.price_per_person.length && clubSettings['pricePerPersonType'] }">
		<InputLabel class="capitalize" required>{{ $t('reservation.payment-status') }}</InputLabel>
		<SimpleSelect
			v-model="form.status"
			:disabled="form.club_reservation || isTimerEnabled"
			:readonly="isPaid"
			:options="statusOptions"
			:placeholder="$t('reservation.payment-status')"
			class="w-full" />
	</div>
	<div
		v-if="clubSettings.bulbStatus && !clubSettings['timersStatus'] && !!slotFeatures.slot_has_bulb.length && dayjs().diff(reservation.start_datetime, 'minutes') > -30"
		class="col-span-6 space-y-2">
		<InputLabel class="capitalize" required>{{ $t('reservation.bulbs.label') }}</InputLabel>
		<SimpleSelect
			v-model="form.features[slotFeatures.slot_has_bulb[0].id].type"
			:readonly="isPaid"
			:options="bulbsOptions"
			class="w-full" />
	</div>
	<div class="col-span-12 flex flex-wrap items-center justify-end space-x-5 space-y-4">
		<div class="flex w-1/3 items-center justify-end pl-3.5 pt-4">
			<Checkbox
				id="clubReservation"
				v-model="form.club_reservation"
				:checked="form.club_reservation"
				:readonly="isPaid" />
			<InputLabel
				:value="$t('reservation.club-reservation')"
				class="-mt-0.5 ml-3 cursor-pointer"
				for="clubReservation" />
			<div v-if="form.errors && form.errors.club_reservation" class="error">
				{{ form.errors.club_reservation }}
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { capitalize, toRaw, watch } from 'vue';
import TextContainer from '@/Components/Dashboard/TextContainer.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useNumber } from '@/Composables/useNumber';
import Datetimepicker from '@/Components/Dashboard/Datetimepicker.vue';
import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Feature, Game, Reservation, SelectOption, Slot } from '@/Types/models';
import { useReservations } from '@/Composables/useReservations';
import dayjs from 'dayjs';

const props = defineProps<{
	game: Game;
	reservation: Reservation;
	form: Object;
	features: Object;
	slotOptions: SelectOption[];
	statusOptions: SelectOption[];
	clubSettings: Object;
	reservationTypeOptions: SelectOption[];
	specialOfferOptions: SelectOption[];
	discountCodeOptions: SelectOption[];
	durationOptions: SelectOption[];
	pricePerPersonOptions: SelectOption[];
	bulbsOptions?: SelectOption[];
	slotFeatures?: {
		[featureType: string]: Feature[];
	};
	finalPrice: string;
	isTimerEnabled: boolean;
	isPaid: boolean;
}>();

const emit = defineEmits<{
	(e: 'update', value: Object): void;
	(e: 'priceChangedManually'): void;
}>();

const { formatAmount } = useNumber();
const { currencySymbols } = useReservations();
const localFormCopy = ref<Object>(toRaw(props.form));

const singleSlotId = ref<null | number>(localFormCopy.value['slot_ids'][0]);

function priceChangedManually() {
	emit('priceChangedManually');
}

watch(singleSlotId, () => {
	localFormCopy.value.slot_ids = [singleSlotId.value];
});

watch(localFormCopy.value, () => {
	emit('update', localFormCopy.value);
});
</script>
