<template>
	<Transition>
		<div v-if="showing && !hidden" class="fixed left-0 top-0 z-10 h-screen w-full bg-black/50">
			<div
				class="relative top-1/2 m-auto w-228 -translate-y-1/2 transform rounded-md bg-white px-11 pb-8 pt-10"
				v-click-away="hide">
				<div class="mb-12 mb-12 text-2xl font-medium leading-9 text-black">
					{{ $t('reservation.update-reservation-confirm') }}
					<div
						class="text-grey-3 hover:text-grey-7 absolute right-7 top-6 cursor-pointer transition"
						@click="hide">
						<XIcon />
					</div>
				</div>
				<div class="modal-content">
					<div>
						<div class="mb-6.5 mt-5 flex w-full">
							<div class="w-1/2">
								{{ $t('reservation.reservation-hours') }}
							</div>
							<div class="w-1/2">
								<div class="flex items-center">
									<div>
										{{ dayjs(reservation.start_datetime).format('HH:mm') }} -
										{{
											reservation.end_datetime
												? dayjs(reservation.end_datetime).format('HH:mm')
												: $t('main.none')
										}}
									</div>
									<div class="ml-3 mr-4">
										<ArrowRightIcon class="text-brand-base" />
									</div>
									<div class="font-semibold text-brand-base">
										{{ dayjs(datetimeRange['from']).format('HH:mm') }} -
										{{
											reservation.end_datetime ? dayjs(datetimeRange['to']).format('HH:mm') : $t('main.none')
										}}
									</div>
								</div>
							</div>
						</div>
						<hr />
						<div class="mb-6.5 mt-5 flex w-full" v-if="reservation.end_datetime">
							<div class="w-1/2">
								{{ $t('reservation.duration-time') }}
							</div>
							<div class="w-1/2">
								<div class="flex items-center">
									<div>
										{{
											dayjs()
												.startOf('day')
												.add(
													dayjs(reservation.end_datetime).diff(dayjs(reservation.start_datetime), 'minute'),
													'minute',
												)
												.format('HH:mm')
										}}
									</div>
									<div class="ml-3 mr-4">
										<ArrowRightIcon class="text-brand-base" />
									</div>
									<div class="font-semibold text-brand-base">
										{{ dayjs().startOf('day').add(duration, 'minute').format('HH:mm') }}
									</div>
								</div>
							</div>
						</div>
						<hr />
						<div class="mb-6.5 mt-5 flex w-full">
							<div class="w-1/2">
								{{ usePage().props.gameTranslations[game.id]['slot-name'] }}
							</div>
							<div class="w-1/2">
								<div class="flex items-center">
									<div>
										{{ reservation.slot_name }}
									</div>
									<div class="ml-3 mr-4">
										<ArrowRightIcon class="text-brand-base" />
									</div>
									<div class="font-semibold text-brand-base">
										{{ slots.find((slot) => slot.id === (slotId ?? reservation.slot_id))?.name }}
									</div>
								</div>
							</div>
						</div>
						<hr v-if="reservation.extended.specialOffer" />
						<div class="mb-6.5 mt-5 flex w-full" v-if="reservation.extended.specialOffer">
							<div class="w-1/2">
								{{ $t('special-offer.singular') }}
							</div>
							<div class="w-1/2">
								<div class="flex items-center">
									<div>
										{{ reservation.extended.specialOffer.name }} ({{
											reservation.extended.specialOffer.value
										}}%)
									</div>
								</div>
							</div>
						</div>
						<hr />
						<div class="mb-6.5 mt-5 flex w-full">
							<div class="w-1/2">
								{{ capitalize($t('main.price')) }}
							</div>
							<div class="w-1/2">
								<div class="flex items-center">
									<div>
										{{ formatAmount(reservation.final_price) }}
									</div>
									<div class="ml-3 mr-4">
										<ArrowRightIcon class="text-brand-base" />
									</div>
									<div class="font-semibold text-brand-base">
										{{ formatAmount(finalPrice) }}
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="mt-5 flex w-full items-end justify-end">
						<Button @click="updateReservation" class="capitalize">{{ $t('main.action.approve') }}</Button>
					</div>
				</div>
			</div>
		</div>
	</Transition>
</template>

<script lang="ts" setup>
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import { useString } from '@/Composables/useString';
import { computed, ref, watch } from 'vue';
import { useNumber } from '@/Composables/useNumber';
import { Game, Reservation, Slot } from '@/Types/models';
import dayjs from 'dayjs';
import { useForm, usePage } from '@inertiajs/vue3';
import { emptyReservation } from '@/Utils';
import axios from 'axios';
import { useCalendar } from '@/Composables/useCalendar';
import ArrowRightIcon from '@/Components/Dashboard/Icons/ArrowRightIcon.vue';

const { capitalize } = useString();

const props = withDefaults(
	defineProps<{
		showing?: boolean;
		reservationNumber: null;
		game: Game;
		slots: Slot[];
		slotId: number | null;
		datetimeRange: {
			from: number;
			to: number;
		};
	}>(),
	{
		showing: false,
		reservationNumber: null,
	},
);

// composables
let {
	getFormDictionary,
	features,
	clubSettings,
	passFeatureKeysToFormDictionary,
	fillReservationForm,
	calculatePrice,
} = useCalendar(props.game);
const { formatAmount } = useNumber();

const duration = computed<number>(() => {
	let result: number = dayjs(props.datetimeRange['to']).diff(dayjs(props.datetimeRange['from']), 'minute');
	if (features.fixed_reservation_duration.length) {
		result = clubSettings['fullDayReservationStatus'] ? 60 : result;
		result = result - (result % clubSettings['calendarTimeScale']);
	}
	if (result < 0) {
		result += 1440;
	}
	return result;
});

const emit = defineEmits<{
	(e: 'close'): void;
	(e: 'update', reservationNumber: string): void;
	(e: 'paidReservationMovedToDifferentPrice'): void;
}>();
const hide = () => {
	reservation.value = emptyReservation;
	hidden.value = true;
	emit('close');
};

// main variables
const hidden = ref<boolean | undefined>(true);
const reservation = ref<Reservation>(emptyReservation);

const price = ref<number>(0);
const finalPrice = ref<number>(0);

let formDictionary: Record<string, any> = getFormDictionary({
	slot_ids: [props.slotId],
	game_id: props.game.id,
});

// put feature keys to form dictionary
formDictionary = passFeatureKeysToFormDictionary(formDictionary);

// form initialize
let form = useForm(formDictionary);

function loadReservation(): void {
	axios
		.get(
			route('reservations.show', {
				reservationNumber: props.reservationNumber,
			}),
		)
		.then(function (response: { data: Reservation }) {
			reservation.value = response.data;
			form = fillReservationForm(form, reservation.value, {
				start_at: props.datetimeRange['from'],
				duration: duration.value,
				slot_ids: [props.slotId ?? reservation.value.slot_id],
			});
      if(features.slot_has_parent.length) {
        let parentSlotId = props.slots.find((slot) => slot.id === props.slotId)?.slot_id ?? null;
        form.parent_slot_id = form.features[features.slot_has_parent[0].id]["parent_slot_id"] = parentSlotId;
      }
			loadPrice();
		});
}

let lastPriceLoadingUnix: number | null = null;

async function loadPrice(customPrice: boolean = false): Promise<void> {
	let price = 0;
	let result = 0;
	let setsPrice = 0;
	({ price, form, result, setsPrice } = await calculatePrice(form, customPrice, {
		reservation_number_id: props.reservationNumber,
	}));
  form.price = price;
  finalPrice.value = (result ?? 0) + (setsPrice ?? 0) + reservation.value.club_commission_partial;
  if(finalPrice.value !== reservation.value.final_price && reservation.value.status === 1) {
    emit('paidReservationMovedToDifferentPrice');
    emit('close');
    return;
  }
	hidden.value = false;

	lastPriceLoadingUnix = dayjs().unix();
}

function updateReservation() {
	if (!props.reservationNumber) {
		return;
	}
	form.post(
		route('club.reservations.update', {
			reservationNumber: props.reservationNumber,
		}),
		{
			preserveScroll: true,
			preserveState: true,
      onFinish: () => {
        setTimeout(() => {
          hidden.value = true;
          emit('update', props.reservationNumber);
        }, 500);
      }
		},
	);
}

watch(
	() => props.showing,
	() => {
		if (props.reservationNumber) {
			loadReservation();
		}
	},
);
</script>
