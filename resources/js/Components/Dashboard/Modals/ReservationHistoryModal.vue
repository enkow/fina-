<template>
	<Transition>
		<div v-if="showing && !hidden" class="fixed left-0 top-0 z-10 h-screen w-full bg-black/50">
			<div
				v-click-away="hide"
				class="relative top-1/2 m-auto max-h-screen min-h-150 w-175 -translate-y-1/2 transform overflow-y-auto rounded-md bg-white pb-7 pt-10">
				<div class="mb-3.5 px-10 text-2xl font-medium leading-9 text-black">
					<div
						class="absolute right-7 top-6 cursor-pointer text-gray-3 transition hover:text-gray-7"
						@click="hide">
						<XIcon />
					</div>
					<span class="mb-1 block w-full text-lg leading-7">{{ $t('reservation.reservation-history') }}</span>
					<span class="mb-2 block w-full text-base font-semibold leading-6">
						{{
							$t('reservation.reservation-number-value', {
								value: reservationNumber,
							})
						}}
					</span>
				</div>
				<div class="modal-content space-y-6 border-t-gray-7/10 px-11">
					<div v-for="reservationHistoryBlock in reservationHistory">
						<InputLabel :value="reservationHistoryBlock.date" class="mb-2 font-semibold" />
						<div class="relative flex w-full justify-center">
							<svg
								class="absolute left-1/2 top-[calc(50%-0.75rem)] h-6"
								fill="none"
								stroke="currentColor"
								stroke-width="1.5"
								viewBox="0 0 24 24"
								xmlns="http://www.w3.org/2000/svg">
								<path
									d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"
									stroke-linecap="round"
									stroke-linejoin="round" />
							</svg>
							<div class="w-full space-y-2">
								<div v-for="reservationHistoryItem in reservationHistoryBlock['entries']">
									<div class="grid w-full grid-cols-2 gap-x-20" v-if="reservationHistoryItem['name']">
										<div class="pl-2">
											{{ reservationHistoryItem['name'] }}: {{ reservationHistoryItem['old'] }}
										</div>
										<div>{{ reservationHistoryItem['name'] }}: {{ reservationHistoryItem['new'] }}</div>
									</div>
								</div>
							</div>
						</div>
						<p class="mt-3 italic">
							{{
								$t('reservation.changed-by-value', {
									value:
										reservationHistoryBlock['entries'][0]['triggerer']?.email ??
										capitalize($t('main.system')),
								})
							}}
						</p>
					</div>
					<p class="italic">
						{{
							$t('reservation.created-by-value', {
								value: reservation.extended?.creator_email,
								datetime: reservation.created_at,
							})
						}}
					</p>
				</div>
			</div>
		</div>
	</Transition>
</template>

<script lang="ts" setup>
import InputLabel from '@/Components/Auth/InputLabel.vue';
import { capitalize, computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import { Reservation, User } from '@/Types/models';
import { emptyReservation } from '@/Utils';
import { usePanelStore } from '@/Stores/panel';

const props = withDefaults(
	defineProps<{
		showing?: boolean;
		reservationNumber: string | number;
		reservation?: Reservation;
	}>(),
	{
		showing: false,
	},
);
const panel = usePanelStore();

interface ReservationHistoryEntry {
	name: string;
	new: any;
	old: any;
	date: any;
	triggerer: User;
}

interface ReservationHistoryBlock {
	date: string;
	entries: ReservationHistoryEntry[];
}

const hidden = ref<boolean | undefined>(undefined);
const reservation = computed<Reservation>(() => {
	return panel.currentShowingReservation ?? emptyReservation;
});
const reservationHistory = ref<Record<number, ReservationHistoryBlock>>({});

function loadReservation(): void {
	if (props.reservationNumber === '0') {
		hidden.value = true;
		return;
	}
	axios
		.get(
			route('club.reservations.history', {
				reservationNumber: props.reservationNumber,
			}),
		)
		.then(function (response: { data: Record<number, ReservationHistoryBlock> }) {
			hidden.value = hidden.value === undefined;
			reservationHistory.value = response.data;
		})
		.catch(function (error) {});
}

const emit = defineEmits<{
	(e: 'close'): void;
}>();
const hide = () => {
	emit('close');
};

onMounted(() => {
	loadReservation();
});
watch(
	() => props.reservationNumber,
	async () => {
		loadReservation();
	},
);
</script>
