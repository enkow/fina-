<template>
	<div class="col-span-12 space-y-2 md:col-span-3">
		<InputLabel class="capitalize" required>
			{{ usePage().props.gameTranslations[game.id]['slot-name'] }}
		</InputLabel>
		<SimpleSelect v-model="singleSlotId" :options="slotOptions" class="w-full" />
		<div v-if="form.errors.slot_ids" class="error">
			{{ form.errors.slot_ids }}
		</div>
	</div>
	<div class="col-span-12 space-y-2 md:col-span-3">
		<InputLabel class="capitalize" required>
			{{ usePage().props.gameTranslations[game.id]['slots-quantity'] }}
		</InputLabel>
		<SimpleSelect v-model="form.slots_count" :options="slotsCountOptions" />
		<div v-if="form.errors && form.errors.slots_count" class="error">
			{{ form.errors.slots_count }}
		</div>
	</div>
	<div class="col-span-12 space-y-2 md:col-span-3">
		<InputLabel class="capitalize" required>{{ $t('main.date') }}</InputLabel>
		<Datetimepicker
			v-model="form.start_at"
			:minutes-increment="clubSettings['calendarTimeScale']"
			placeholder=" " />
		<div v-if="form.errors && form.errors.start_at" class="error">
			{{ form.errors.start_at }}
		</div>
	</div>

	<div
		v-if="!clubSettings['fullDayReservationStatus'] && !clubSettings['fixedReservationDurationStatus']"
		class="col-span-12 space-y-2 md:col-span-3">
		<InputLabel class="capitalize" required>{{ $t('reservation.duration-time') }}</InputLabel>
		<SimpleSelect v-model="form.duration" :options="durationOptions" class="w-full" />
		<div v-if="form.errors && form.errors.duration" class="error">
			{{ form.errors.duration }}
		</div>
	</div>

	<div class="col-span-12 grid grid-cols-12 gap-x-5 gap-y-3">
		<div class="col-span-12 space-y-2 md:col-span-4">
			<InputLabel class="capitalize">{{ $t('reservation.reservation-type') }}</InputLabel>
			<SimpleSelect v-model="form.reservation_type_id" :options="reservationTypeOptions" class="w-full" />
			<div v-if="form.errors && form.errors.reservation_type_id" class="error">
				{{ form.errors.reservation_type_id }}
			</div>
		</div>

		<div class="col-span-12 space-y-2 md:col-span-4">
			<InputLabel class="capitalize">{{ $t('reservation.discount-code') }}</InputLabel>
			<SimpleSelect
				v-model="form.discount_code_id"
				:disabled="form.club_reservation"
				:options="discountCodeOptions"
				class="w-full" />
			<div v-if="form.errors && form.errors.discount_code_id" class="error">
				{{ form.errors.discount_code_id }}
			</div>
		</div>

		<div class="col-span-12 space-y-2 md:col-span-4">
			<InputLabel class="capitalize">{{ $t('reservation.special-offer') }}</InputLabel>
			<SimpleSelect
				v-model="form.special_offer_id"
				:disabled="form.club_reservation"
				:options="specialOfferOptions"
				class="w-full" />
			<div v-if="form.errors && form.errors.special_offer_id" class="error">
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
			class="dropdown-fixed-height w-full" />
		<div
			v-if="form.errors && form.errors[`features.${features.price_per_person[0].id}.person_count`]"
			class="error">
			{{ form.errors[`features.${features.price_per_person[0].id}.person_count`] }}
		</div>
	</div>

	<div
		class="col-span-12 space-y-2 md:col-span-4"
		:class="{ 'md:!col-span-3': features.price_per_person.length && clubSettings['pricePerPersonType'] }">
		<InputLabel class="capitalize" required>
			{{ $t('main.price') }} [{{ currencySymbols[usePage().props.club.country.currency] }}]
		</InputLabel>
		<TextInput v-model="form.price" :disabled="form.club_reservation" @input="priceChangedManually" />
		<div v-if="form.errors && form.errors.price" class="error">
			{{ form.errors.price }}
		</div>
	</div>

	<div
		class="col-span-12 space-y-2 md:col-span-4"
		:class="{ 'md:!col-span-3': features.price_per_person.length && clubSettings['pricePerPersonType'] }">
		<InputLabel>{{ capitalize($t('main.final-amount')) }}</InputLabel>
		<TextContainer class="max-w-full">
			{{ formatAmount(form.club_reservation ? 0 : finalPrice) }}
		</TextContainer>
	</div>
	<div
		class="col-span-12 space-y-2 md:col-span-4"
		:class="{ 'md:!col-span-3': features.price_per_person.length && clubSettings['pricePerPersonType'] }">
		<InputLabel class="capitalize" required>{{ $t('reservation.payment-status') }}</InputLabel>
		<SimpleSelect
			v-model="form.status"
			:disabled="form.club_reservation"
			:options="statusOptions"
			:placeholder="$t('reservation.payment-status')"
			class="selected-pr-0 w-full" />
	</div>
	<div
		v-if="clubSettings.bulbStatus && !clubSettings['timersStatus'] && !!slotFeatures.slot_has_bulb.length"
		class="col-span-6 space-y-2">
		<InputLabel class="capitalize" required>{{ $t('reservation.bulbs.label') }}</InputLabel>
		<SimpleSelect
			v-model="form.features[slotFeatures.slot_has_bulb[0].id].type"
			:options="bulbsOptions"
			class="w-full" />
	</div>
	<div
		class="col-span-12 flex flex-wrap items-center space-x-5"
		:class="{
			'justify-end': !timerInitAllowed,
			'justify-between': timerInitAllowed,
		}">
		<div v-if="timerInitAllowed" class="flex items-center pt-4">
			<Checkbox id="timerInit" v-model="form.timer_init" :checked="form.timer_init" />
			<InputLabel :value="$t('reservation.timer-init')" class="-mt-0.5 ml-3 cursor-pointer" for="timerInit" />
			<div v-if="form.errors && form.errors.timer_init" class="error">
				{{ form.errors.timer_init }}
			</div>
		</div>

		<div class="flex items-center pt-4">
			<Checkbox id="clubReservation" v-model="form.club_reservation" :checked="form.club_reservation" />
			<InputLabel
				:value="$t('reservation.club-reservation')"
				class="-mt-0.5 ml-2 cursor-pointer"
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
import { Game, SelectOption } from '@/Types/models';
import { useReservations } from '@/Composables/useReservations';
import dayjs from 'dayjs';

const props = defineProps<{
	game: Game;
	form: Object;
	features: Object;
	timerInitAllowed: boolean;
	slotOptions: SelectOption[];
	slotsCountOptions: SelectOption[];
	statusOptions: SelectOption[];
	clubSettings: Object;
	reservationTypeOptions: SelectOption[];
	specialOfferOptions: SelectOption[];
	discountCodeOptions: SelectOption[];
	durationOptions: SelectOption[];
	pricePerPersonOptions: SelectOption[];
	slotFeatures?: {
		[featureType: string]: Feature[];
	};
	finalPrice: string;
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
