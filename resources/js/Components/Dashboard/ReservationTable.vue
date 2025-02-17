<template>
	<Table
		:centered="['slots_count', 'person_count', 'occupied_status']"
		:disabled="[
			'reservation_id',
			'reservation_number_id',
			'reservation_slot_id',
			'parent_slot_id',
			'status',
			'slot_id',
			'end_datetime',
			'club_reservation',
			'club_note',
			'cancelation_status',
			'customer_note',
			'show_customer_note_on_calendar',
			'show_club_note_on_calendar',
			'payment_method_online',
			'display_name',
			'custom_display_name',
			'payment_method_id',
			'created_at',
			'calendar_name',
			'calendar_color',
			'customer_email',
			'cancelation_type',
			'timer_status',
			disabledActions ? 'occupied_status' : '',
		]"
		:header="
			tableHeadings ?? {
				number: capitalize($t('main.number')),
				reservation_time_range: capitalize($t('main.hour')),
				duration: capitalize($t('main.time')),
				slots_count: currentGameFilter
					? usePage().props.gameTranslations[currentGameFilter]['slots-quantity']
					: '',
				customer_name: $t('customer.singular'),
				customer_phone: capitalize($t('main.phone')),
				created_datetime: capitalize($t('main.created-female')),
				sets: $t('set.plural'),
				club_note: $t('reservation.club-comments'),
				customer_note: $t('reservation.customer-comments'),
				club: capitalize($t('main.club')),
				customer: capitalize($t('customer.singular')),
				status: capitalize($t('main.status')),
				email: capitalize($t('main.email-address')),
				phone: capitalize($t('main.phone')),
				start_datetime: capitalize($t('main.date')),
				price: capitalize($t('main.price')),
			}
		"
		:items="reservations"
		:narrow="['number', 'start_datetime', 'slots_count']"
		:sortable="[
			'created_datetime',
			'start_datetime',
			'number',
			'slot_name',
			'slots_count',
			'end_datetime',
			'customer_name',
			'person_count',
			'final_price',
			'status_locale',
			'reservation_time_range',
		]"
		:table-name-preference-postfix="currentGameFilter"
		:table-name="tableName ?? 'reservations'"
		:custom-table-preference="
			customTablePreference ?? panelStore.customTablePreferences?.['reservations_' + currentGameFilter] ?? undefined
		">
		<template #header_person_count="tableProps">
			<PeopleIcon class="ml-0.5 w-5" />
		</template>
		<template #header_slots_count="tableProps">
			<PeopleIcon class="w-5" />
		</template>
		<template #cell_duration="tableProps">
			{{
				Math.floor(tableProps.item.duration / 60) + ':' + pad((tableProps.item.duration % 60)?.toString() ?? '0', 2)
			}}
		</template>
		<template #cell_customer_name="tableProps">
      <template v-if="tableProps.item.custom_display_name && tableProps.item.custom_display_name.length">
        {{ tableProps.item.custom_display_name }}
      </template>
      <template v-else-if="tableProps.item.customer_name?.length">
        {{ tableProps.item?.customer_name }}
      </template>
      <template v-else>
        ---
      </template>
		</template>

		<template #cell_customer_phone="tableProps">
			{{ tableProps.item.customer_phone?.length ? tableProps.item?.customer_phone : '---' }}
		</template>

		<template #cell_club="tableProps">
			{{ tableProps.item }}
		</template>

		<template #cell_reservation_time_range="tableProps">
      <div v-if="fixedReservationDurationFeature && usePage().props.clubSettings['fixed_reservation_duration_status_' + fixedReservationDurationFeature.id].value && usePage().props.clubSettings['fixed_reservation_duration_value_' + fixedReservationDurationFeature.id].value === 24">
        {{
          dayjs(tableProps.item.start_datetime).format('HH:mm')
        }}
      </div>
      <div v-else>
        {{
          dayjs(tableProps.item.start_datetime).format('HH:mm') +
          ' - ' +
          (tableProps.item.end_datetime !== null ? dayjs(tableProps.item.end_datetime).format('HH:mm') : '---')
        }}
      </div>
		</template>

		<template #header_end_datetime="tableProps">
			<div style="min-width: 200px; max-width: 300px">
				{{ tableHeadings['end_datetime'] ?? capitalize($t('main.date')) }}
			</div>
		</template>

		<template #cell_end_datetime="tableProps">
			<div style="min-width: 200px; max-width: 300px">
				{{ formatDate(tableProps.item.end_datetime) }}
			</div>
		</template>

		<template #header_start_datetime="tableProps">
			<div style="min-width: 60px; max-width: 60px" class="whitespace-break-spaces">
				{{ capitalize($t('main.date')) }}
			</div>
		</template>

		<template #cell_start_datetime="tableProps">
			<div>
				{{ formatDate(tableProps.item.start_datetime) }}
			</div>
		</template>

		<template #cell_final_price="tableProps">
			{{ formatAmount(tableProps.item.final_price) }}
		</template>

		<template #cell_status_locale="tableProps">
			{{ capitalize(tableProps.item.status_locale) }}
		</template>

		<template #cell_number="tableProps">
			<div
				class="flex cursor-pointer items-center space-x-2"
				@click="openReservationShowModal(tableProps.item.reservation_number_id)">
				<StatusSquare
					:left="{
						background:
							tableProps.item.reservation_type_color === 'transparent'
								? tableProps.item.calendar_color
								: tableProps.item.reservation_type_color,
					}"
					:right="{
						background:
							tableProps.item.calendar_color === 'transparent'
								? tableProps.item.reservation_type_color
								: tableProps.item.calendar_color,
					}" />
				<p>{{ tableProps.item.visible_number ?? tableProps.item.number }}</p>
			</div>
		</template>

		<template #cell_sets="tableProps">
			<div v-if="!Object.keys(tableProps.item.sets ?? {}).length">---</div>
			<div v-else>
				<div v-for="reducedSet in tableProps.item.sets">
					{{ reducedSet['count'] }}x {{ reducedSet['name'] }}
				</div>
			</div>
		</template>

		<template #cell_club_note="tableProps">
			<div class="max-w-53">
				<LimitedString
					:limit="80"
					:status="tableProps.item.club_note_expanded ?? false"
					:string="tableProps.item.club_note"
					@toggle="toggleClubNoteContentExpandStatus(tableProps.item.number)" />
			</div>
		</template>

		<template #cell_customer_note="tableProps">
			<div class="w-36">
				<LimitedString
					:limit="80"
					:status="tableProps.item.customer_note_expanded ?? false"
					:string="tableProps.item.customer_note"
					@toggle="toggleCustomerNoteContentExpandStatus(tableProps.item.number)" />
			</div>
		</template>

		<template #cell_source="tableProps">
			{{ capitalize($t('reservation.status.' + (!tableProps.item.source ? 'online' : 'offline'))) }}
		</template>

		<template #cell_occupied_status="tableProps">
			<Button
				type="link"
				preserve-scroll
				:href="
					route('club.reservations.toggle-occupied-status', {
						reservationNumber: tableProps.item.number,
					})
				"
				class="sm brand w-full uppercase"
				:class="{
					danger: !tableProps.item.occupied_status,
					brand: tableProps.item.occupied_status,
				}">
				{{
					!tableProps.item.occupied_status
						? props.game.features.find(
								(feature: Feature) => feature.type === 'reservation_slot_has_occupied_status',
						  ).translations['occupied-status-turn-on']
						: props.game.features.find(
								(feature: Feature) => feature.type === 'reservation_slot_has_occupied_status',
						  ).translations['occupied-status-turn-off']
				}}
			</Button>
		</template>
	</Table>
	<ReservationShowModal
		v-if="withReservationModals === true"
    :game="game"
		:readonly="previewMode"
		:reservation-number="reservationShowModalNumber"
		:showing="reservationShowModalShowing"
		@close-modal="closeReservationShowModal"
		@show-reservation-by-reservation-number="openReservationShowModal"
		@edit-reservation="showReservationEditModal"
		@show-reservation-history="showReservationHistoryModal"
		@cancel-reservation="reservationCanceled"
		@reload="$emit('reload')" />
	<ReservationEditModal
		v-if="withReservationModals === true && ['manager', 'employee'].includes(usePage().props.user.type)"
		:game="game ?? currentGameFilter"
		:reservation-number="reservationShowModalNumber"
		:showing="reservationUpdateModalShowing"
		@update-reservation="reservationUpdated"
		@close-modal="closeReservationEditModal" />
	<ReservationHistoryModal
		v-if="withReservationModals === true && ['manager', 'employee'].includes(usePage().props.user.type)"
		:game="game ?? currentGameFilter"
		:reservation-number="reservationShowModalNumber"
		:showing="reservationHistoryModalShowing"
		@close="closeReservationHistoryModal" />
	<StatusModal :showing="statusModalShowing" type="success" @close="statusModalShowing = false">
		<template #header>
			{{ capitalize($t('main.after-action.ready')) }}
		</template>
		{{ statusModalContent }}
	</StatusModal>
</template>

<script lang="ts" setup>
import LimitedString from '@/Components/Dashboard/LimitedString.vue';
import { Game, Reservation, Set, Feature } from '@/Types/models';
import { PaginatedResource } from '@/Types/responses';
import Table from '@/Components/Dashboard/Table.vue';
import { useString } from '@/Composables/useString';
import { useQueryString } from '@/Composables/useQueryString';
import StatusSquare from '@/Components/Dashboard/StatusSquare.vue';
import PeopleIcon from '@/Components/Dashboard/Icons/PeopleIcon.vue';
import ReservationShowModal from '@/Components/Dashboard/Modals/ReservationShowModal.vue';
import {ref, watch, reactive, computed} from 'vue';
import { useModal } from '@/Composables/useModal';
import { wTrans } from 'laravel-vue-i18n';
import StatusModal from '@/Components/Dashboard/Modals/StatusModal.vue';
import ReservationEditModal from '@/Components/Dashboard/Modals/ReservationEditModal.vue';
import { useNumber } from '@/Composables/useNumber';
import ReservationHistoryModal from '@/Components/Dashboard/Modals/ReservationHistoryModal.vue';
import { usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { usePanelStore } from '@/Stores/panel';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { provide, onBeforeMount } from 'vue';

const props = withDefaults(
	defineProps<{
    id?: number,
		game: Game;
		reservations: PaginatedResource<Reservation | any>;
		tableName?: string;
		tableHeadings?: Record<string, string>;
		withReservationModals?: boolean;
		tableNamePreferencePostfix?: string;
		reservationShowModalControl?: string | null | number; // handling reservation view open from parent component. Contains reservation_table_id.
		previewMode?: boolean;
		disabledActions?: boolean;
    customTablePreference?: Object[];
	}>(),
	{
		disabledActions: false,
		withReservationModals: false,
    previewMode: false,
		tableName: 'reservations',
    disabledActions: false,
	},
);

const emit = defineEmits<{
	(e: 'open', reservationNumber: string): void;
	(e: 'update', reservationNumber: string): void;
	(e: 'cancel', reservationNumber: string): void;
	(e: 'close'): void;
	(e: 'reload'): void;
}>();

const panelStore = usePanelStore();

const { formatAmount } = useNumber();
const { pad, capitalize } = useString();
const { queryValue } = useQueryString();
const { saveScrollTop, passScrollTop } = useModal();

const fixedReservationDurationFeature = ref<Feature | undefined>(props.game.features?.find((feature) => feature.type === 'fixed_reservation_duration'));

// get current game id
const currentGameFilter: string =
	props.tableNamePreferencePostfix ?? queryValue(window.location.search, 'filters[reservations][game]');

// text expansion statuses initialize
props.reservations.data?.forEach((reservation, key) => {
	props.reservations.data[key]['club_note_expanded'] = false;
	props.reservations.data[key]['customer_note_expanded'] = false;
});

// text expansion actions
function toggleClubNoteContentExpandStatus(number: number) {
	props.reservations.data?.forEach((reservation, key) => {
		if (reservation.number === number) {
			props.reservations.data[key]['club_note_expanded'] =
				!props.reservations.data[key]['club_note_expanded'];
		}
	});
}

function toggleCustomerNoteContentExpandStatus(number: number) {
	props.reservations.data?.forEach((reservation, key) => {
		if (reservation.number === number) {
			props.reservations.data[key]['customer_note_expanded'] =
				!props.reservations.data[key]['customer_note_expanded'];
		}
	});
}

// modal showing refs
const reservationHistoryModalShowing = ref<boolean>(false);
const reservationUpdateModalShowing = ref<boolean>(false);
const reservationShowModalShowing = ref<boolean>(false);
const statusModalShowing = ref<boolean>(false);
const reservationShowModalNumber = ref<string | undefined>('0');

// reservation history modal actions
function showReservationHistoryModal(): void {
	reservationShowModalShowing.value = false;
	reservationHistoryModalShowing.value = true;
	// saveScrollTop();
}

function closeReservationHistoryModal(): void {
	reservationShowModalShowing.value = true;
	reservationHistoryModalShowing.value = false;
	//emit("close");

	// passScrollTop();
}

// reservation view modal actions
function openReservationShowModal(reservationNumberId: string): void {
	emit('open', reservationNumberId);
	reservationShowModalNumber.value = reservationNumberId.toString();
	reservationShowModalShowing.value = true;
	saveScrollTop();
}

function closeReservationShowModal(): void {
	reservationShowModalNumber.value = '0';
	reservationShowModalShowing.value = false;
	emit('close');

	passScrollTop();
}

// reservation edit modal actions
function showReservationEditModal(): void {
	reservationUpdateModalShowing.value = true;
	reservationShowModalShowing.value = false;
}

function closeReservationEditModal(): void {
	reservationUpdateModalShowing.value = false;
	reservationShowModalNumber.value = '0';
	emit('close');

	passScrollTop();
}

// reservation event listeners
const statusModalContent = ref<string>('');

function reservationCanceled(related: boolean): void {
	reservationShowModalShowing.value = false;
	statusModalShowing.value = true;
	if (related) {
		statusModalContent.value = wTrans('reservation.successfully-canceled-content-plural').value;
	} else {
		statusModalContent.value = wTrans('reservation.successfully-canceled-content').value;
	}
	passScrollTop();
	emit('cancel', reservationShowModalNumber.value);
}

function reservationUpdated(reservationNumber: string): void {
	reservationUpdateModalShowing.value = false;
	statusModalShowing.value = true;
	if (reservationNumber === '0') {
		statusModalContent.value = wTrans('reservation.successfully-stored-content').value;
	} else {
		statusModalContent.value = wTrans('reservation.successfully-updated-content').value;
	}
  reservationShowModalNumber.value = '0';
	emit('update', reservationNumber);
	passScrollTop();
}

// prepare sets array for print
function reduceSets(sets: Set[]): string[] {
	const setsDataForReduce: Record<number, { name: string; count: number }> = sets.reduce<
		Record<number, { name: string; count: number }>
	>((acc, set) => {
		acc[set.id] = acc[set.id] || { name: set.name, count: 0 };
		acc[set.id].count++;
		return acc;
	}, {});

	return Object.entries(setsDataForReduce).map(([, data]) => `${data.count} x ${data.name}`);
}

function formatDate(input: string): string {
	return dayjs(input).format('DD.MM.YYYY');
}

// watchers

watch(
	() => props.reservationShowModalControl,
	async () => {
		if (props.reservationShowModalControl) {
			reservationShowModalNumber.value = props.reservationShowModalControl;
			reservationShowModalShowing.value = true;
		}
	},
);
</script>
