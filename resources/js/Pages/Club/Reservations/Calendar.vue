<template>
	<PanelLayout
		@toggle-sidebar-reduction-status="updateSlotWidth"
		:title="$t('reservation.plural') + ' ' + usePage().props.gameNames[game.id]">
		<div class="flex w-full flex-wrap space-y-9 px-3 py-5 lg:py-8 xl:px-10">
			<div class="flex w-full flex-wrap justify-between lg:flex-nowrap">
				<div class="flex w-full flex-wrap md:flex-nowrap md:space-x-5">
					<SimpleDatepicker
						v-model="date"
						:inputClasses="['opacity-60']"
						class="!w-full md:mr-5 md:!w-1/2 lg:!w-50"
						input-with-icon
						position="left" />
					<Popper
						:show="reservationSearchResultsShowing"
						class="!mb-0 !w-[calc(100vw)] md:!w-1/2 lg:!w-64"
						@click="reservationSearchResultsShowing = !reservationSearchResultsShowing"
						@focusout="delayedHideReservationSearch">
						<SearchInput v-model="reservationSearch" class="max-h-[48px] !w-full" />
						<template #content>
							<div
								v-if="reservationSearchResultsShowing"
								class="w-[calc(100vw-1.5rem)] space-y-2 rounded-md border border-brand-base bg-gray-10 py-2 text-sm md:w-[calc(50vw-2.125rem)] lg:w-[14.5rem]">
								<div>
									<h3 class="px-2 pb-1 font-medium">
										{{ $t('reservation.plural') }}
									</h3>
									<ReservationsSearchResult
										:reservations="reservationSearchResults['confirmed']"
										@open="openReservationShowModal" />
								</div>

								<div>
									<h3 class="px-2 pb-1 font-medium">
										{{ $t('reservation.canceled-reservations') }}
									</h3>
									<ReservationsSearchResult
										:reservations="reservationSearchResults['canceled']"
										@open="openReservationShowModal" />
								</div>
							</div>
						</template>
					</Popper>
				</div>
				<div class="flex space-x-3">
					<GameSquareButton
						v-for="buttonGame in usePage().props.club.games"
						:class="{
							active: buttonGame.name === game.name,
						}"
						:href="
							route('club.games.reservations.calendar', {
								game: buttonGame.name,
								date: date,
							})
						"
						class="flex items-center justify-center"
						type="link"
						v-html="buttonGame.icon"></GameSquareButton>
				</div>
			</div>
			<div
				:class="{
					test: true,
					'opacity-0': reservationStoredNotification.length === 0,
					'opacity-100': reservationStoredNotification.length,
				}"
				class="!mt-0 flex w-full space-x-3 rounded-md bg-white px-6 py-3 shadow">
				<div class="text-brand-base">
					<SuccessCircleIcon />
				</div>
				<div class="font-semibold text-brand-base">
					{{ reservationStoredNotification }}
				</div>
			</div>
			<div class="mx-auto !mt-2 flex w-full flex-wrap overflow-hidden bg-white pt-4 shadow sm:rounded-lg">
				<div class="w-full">
					<div class="mb-5 flex w-full flex-wrap space-y-2 px-6 lg:hidden">
						<Button
							v-if="openingHours['club_closed'] === false && !preview_mode"
							class="brand sm w-full !text-sm"
							@click="showReservationCreateModal">
							{{ $t('reservation.new-reservation') }}
						</Button>
						<Button :href="route('club.announcements.index')" class="info sm w-full !text-sm" type="link">
							{{ $t('announcement.add-announcement') }}
						</Button>
						<div class="flex w-full justify-center">
							<Button
								:href="previousDayLink"
								class="info sm mr-0.25 !rounded-r-none !pl-3 !pr-3 !text-sm hover:!shadow-none"
								preserve-scroll
								type="link">
								<svg fill="none" height="15" viewBox="0 0 9 15" width="9" xmlns="http://www.w3.org/2000/svg">
									<path d="M8.4 2.1L3.15 7.35L8.4 12.6L7.35 14.7L0 7.35L7.35 0L8.4 2.1Z" fill="#F0F1F4" />
								</svg>
							</Button>
							<div
								v-if="dayjs.tz().format('YYYY-MM-DD') === date"
								class="flex grow items-center justify-center !rounded-none bg-info-base px-5 !text-sm font-semibold capitalize tracking-normal text-white">
								<p>{{ $t('main.today') }}</p>
							</div>
							<Button
								v-else
								:href="currentDayLink"
								class="info sm grow !rounded-none !px-5 !text-sm capitalize tracking-normal hover:!shadow-none"
								preserve-scroll
								type="link">
								{{ $t('main.today') }}
							</Button>
							<Button
								:href="nextDayLink"
								class="info sm ml-0.25 !rounded-l-none !pl-3 !pr-3 !text-sm hover:!shadow-none"
								preserve-scroll
								type="link">
								<svg
									fill="none"
									height="16"
									viewBox="0 0 10 16"
									width="10"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M0.662207 13.3096L6.24549 8.129L1.35141 2.2189L2.53699 0.0737183L9.38869 8.34786L1.5721 15.6006L0.662207 13.3096Z"
										fill="#F0F1F4" />
								</svg>
							</Button>
						</div>
					</div>
					<div class="mb-6 flex items-center justify-between px-6">
						<h3 class="mt-3 text-3xl font-medium">
							{{ capitalize(dateLocale) }}
							{{
								openingHours['club_closed'] === true ? ' - ' + $t('opening-hours.closed').toUpperCase() : ''
							}}
						</h3>
						<div class="hidden space-x-2.5 lg:flex">
							<Button
								v-if="openingHours['club_closed'] === false && !preview_mode"
								class="brand sm whitespace-nowrap px-3 !text-sm xl:px-7"
								@click="showReservationCreateModal">
								{{ $t('reservation.new-reservation') }}
							</Button>
							<Button
								:href="route('club.announcements.index')"
								class="info sm whitespace-nowrap px-3 !text-sm xl:px-7"
								preserve-scroll
								type="link">
								{{ $t('announcement.add-announcement') }}
							</Button>
							<div class="flex">
								<Button
									:href="previousDayLink"
									class="info sm mr-0.25 !rounded-r-none !pl-3 !pr-3 !text-sm"
									preserve-scroll
									type="link">
									<svg
										fill="none"
										height="15"
										viewBox="0 0 9 15"
										width="9"
										xmlns="http://www.w3.org/2000/svg">
										<path d="M8.4 2.1L3.15 7.35L8.4 12.6L7.35 14.7L0 7.35L7.35 0L8.4 2.1Z" fill="#F0F1F4" />
									</svg>
								</Button>
								<div
									v-if="dayjs.tz().format('YYYY-MM-DD') === date"
									class="flex items-center !rounded-none bg-info-base px-5 !text-sm font-semibold capitalize tracking-wider text-white">
									{{ $t('main.today') }}
								</div>
								<Button
									v-else
									:href="currentDayLink"
									class="info sm !rounded-none !px-5 !text-sm capitalize"
									preserve-scroll
									type="link">
									{{ $t('main.today') }}
								</Button>
								<Button
									:href="nextDayLink"
									class="info sm ml-0.25 !rounded-l-none !pl-3 !pr-3 !text-sm"
									preserve-scroll
									type="link">
									<svg
										fill="none"
										height="16"
										viewBox="0 0 10 16"
										width="10"
										xmlns="http://www.w3.org/2000/svg">
										<path
											d="M0.662207 13.3096L6.24549 8.129L1.35141 2.2189L2.53699 0.0737183L9.38869 8.34786L1.5721 15.6006L0.662207 13.3096Z"
											fill="#F0F1F4" />
									</svg>
								</Button>
							</div>
						</div>
					</div>
					<div
						v-if="announcement"
						class="flex w-full items-center space-x-3 pl-6"
						:class="{ 'mb-4': openingHours['club_closed'] }">
						<div class="text-danger-base">
							<DangerCircleIcon />
						</div>
						<div class="max-w-[calc(100%-60px)] break-words font-semibold text-danger-base">
							{{ announcement.content }}
						</div>
					</div>
					<div v-if="openingHours['club_closed'] === false">
						<div
							v-if="
								game.features.find((feature) => feature.type === 'has_custom_views')?.data?.custom_views?.[
									'reservations.calendar'
								] ?? false
							">
							<UnnumberedTablesCalendar
								v-if="
									game.features.find((feature) => feature.type === 'has_custom_views').data.custom_views[
										'reservations.calendar'
									] === 'Club/Reservations/Custom/UnnumberedTables/Calendar'
								"
								:game="game"
								:parent-slots-fill-data="parentSlotsFillData" />
							<NumberedTablesCalendar
								v-if="
									game.features.find((feature) => feature.type === 'has_custom_views').data.custom_views[
										'reservations.calendar'
									] === 'Club/Reservations/Custom/NumberedTables/Calendar'
								"
								@select-slot="numberedTablesSlotSelected"
								:game="game"
								:parent-slots-fill-data="parentSlotsFillData" />
						</div>
						<FullCalendar
							v-else
							ref="calendarRef"
							:options="calendarOptions"
							class="fullCalendar h-[calc(100vh-240px)] md:h-[calc(100vh-220px)] 2xl:h-[calc(100vh-250px)]"
							:class="{
								'!max-h-[100px]': todayMinutes / clubSettings['calendarTimeScale'] === 1,
								'!max-h-[200px]': todayMinutes / clubSettings['calendarTimeScale'] === 2,
								'!max-h-[300px]': todayMinutes / clubSettings['calendarTimeScale'] === 3,
								'!max-h-[400px]': todayMinutes / clubSettings['calendarTimeScale'] === 4,
								'!max-h-[500px]': todayMinutes / clubSettings['calendarTimeScale'] === 5,
								'!max-h-[600px]': todayMinutes / clubSettings['calendarTimeScale'] === 6,
							}">
							<template v-slot:resourceLabelContent="arg">
								<BulbButton
									:slot="slots.find((slot) => slot.id === parseInt(arg.resource.id))"
									:game="game" />
								{{ arg.resource.title }}
							</template>
						</FullCalendar>
					</div>
				</div>
			</div>
			<div class="mt-12 w-full space-y-5">
				<Card>
					<div class="flex w-full">
						<TableSearch class="mb-5 block w-full lg:hidden" table-name="reservations" />
						<ReservationTableColumnEditor
							:game="game"
							:reservation-table-headings="reservationTableHeadings"
							:reservations="reservations"
							class="block md:!hidden" />
					</div>
					<div class="flex justify-between">
						<div class="flex w-full items-center space-x-8 md:w-1/2">
							<h1 class="text-[28px] font-medium">
								{{ $t('reservation.reservations-list') }}
							</h1>
						</div>
						<div class="flex space-x-4">
							<TableSearch class="hidden !h-12 !w-80 lg:flex" table-name="reservations" />
							<ReservationTableColumnEditor
								:game="game"
								:reservation-table-headings="reservationTableHeadings"
								:reservations="reservations"
								class="!hidden !h-12 md:!block" />
							<ExportDropdown v-model="notCanceledExport" />
							<button
								class="ml-3 flex !h-12 w-12 items-center justify-center rounded-md border border-gray-2 p-3 shadow"
								@click="reservationTableExpandedStatus = !reservationTableExpandedStatus">
								<ChevronDownIcon v-if="!reservationTableExpandedStatus" />
								<ChevronUpIcon v-else />
							</button>
						</div>
					</div>
					<div v-if="reservationTableExpandedStatus" class="pt-10">
						<ReservationTable
							:game="game"
							:reservation-show-modal-control="reservationShowModalControl"
							:reservations="reservationsRendered"
							:table-headings="reservationTableHeadings"
							:table-name-preference-postfix="game.id.toString()"
							with-reservation-modals
							@cancel="cancelReservation"
							@close="reservationShowModalControl = '0'"
							@open="openReservationShowModalWithoutScrollSaving"
							@update="updateReservation"
							@reload="reloadTriggerer++" />
					</div>
				</Card>
				<Card>
					<div class="flex w-full">
						<TableSearch class="mb-5 block w-full lg:hidden" table-name="reservations" />
						<ReservationTableColumnEditor
							:game="game"
							:reservation-table-headings="reservationTableHeadings"
							:reservations="reservations"
							class="block md:!hidden" />
					</div>
					<div class="flex justify-between">
						<div class="flex w-full items-center space-x-8 md:w-1/2">
							<h1 class="text-[28px] font-medium">
								{{ $t('reservation.canceled-reservations') }}
							</h1>
						</div>
						<div class="flex space-x-4">
							<TableSearch class="hidden !h-12 !w-80 lg:flex" table-name="reservations" />
							<ReservationTableColumnEditor
								:game="game"
								:reservation-table-headings="reservationTableHeadings"
								:reservations="reservations"
                disabled-actions
								class="!hidden !h-12 md:!block" />
							<ExportDropdown v-model="canceledExport" />
							<button
								class="ml-3 flex !h-12 w-12 items-center justify-center rounded-md border border-gray-2 p-3 shadow"
								@click="canceledReservationTableExpandedStatus = !canceledReservationTableExpandedStatus">
								<ChevronDownIcon v-if="!canceledReservationTableExpandedStatus" />
								<ChevronUpIcon v-else />
							</button>
						</div>
					</div>
					<div v-if="canceledReservationTableExpandedStatus" class="pt-10">
						<ReservationTable
							:game="game"
							:reservations="canceledReservationsRendered"
							tableName="canceledReservations"
							:id="2"
							:table-headings="reservationTableHeadings"
							:table-name-preference-postfix="game.id.toString()"
              disabled-actions
							@cancel="cancelReservation"
							@open="openReservationShowModalWithoutScrollSaving" />
					</div>
				</Card>
			</div>
		</div>
		<ReservationCreateModal
			:key="reservationCreateModalResetFieldsControl"
			:duration="reservationCreateModalDuration"
			:game="game"
			:showing="reservationCreateModalShowing"
			:parent-slot-id="reservationCreateModalParentSlotId"
			:slot-id="reservationCreateModalSlotId"
			:slots="slots"
			:start-datetime="reservationCreateModalStartDatetime"
			@close-modal="closeReservationCreateModal"
			@store-reservation="reservationStored" />
		<StatusModal :showing="statusModalShowing" :type="statusModalType" @close="closeStatusModal">
			<template #header>
				{{ statusModalHeading }}
			</template>
			<div class="text-lg font-medium" v-html="statusModalContent"></div>
		</StatusModal>
		<NewPriceModal
			:datetime-range="newPriceHourRange"
			:game="game"
			:reservation-number="newPriceModalReservationNumber"
			:reservations="calendarReservations"
			:showing="newPriceModalShowing"
			:slots="slots"
			:slot-id="newPriceModalSlotId"
			@close="closeNewPriceModal"
			@paid-reservation-moved-to-different-price="paidReservationMovedToDifferentPrice"
			@update="updateReservation" />
	</PanelLayout>
</template>
<style>
.fc-scrollgrid-section-body .fc-timegrid-slot {
	height: v-bind(timeGridSlotHeightRendered);
}

.fc-timegrid-axis-chunk {
	@apply pb-6;
}

.fc-scrollgrid-section-header .fc-scroller-harness .fc-scroller {
	@apply pr-2.5;
}

.fc-scrollgrid-section-header .fc-scroller {
	overflow: hidden !important;
}

.fc-scroller {
	overflow-x: v-bind(calendarScrollX) !important;
	overflow-y: v-bind(calendarScrollY) !important;
}

.fc .fc-timegrid-slot-label-cushion {
	@apply font-calendarTime;
}

.timers,
.event-wrapper:hover .data {
	display: none;
	/* domyślnie ukrywamy .timers oraz .data kiedy .event-wrapper jest w stanie hover */
}

.event-wrapper:hover .timers {
	display: flex;
	/* pokazujemy .timers kiedy .event-wrapper jest w stanie hover */
}

.event-wrapper .data {
	display: block;
	/* domyślnie pokazujemy .data */
}
</style>

<script lang="tsx" setup>
import FullCalendar from '@fullcalendar/vue3';
import resourceTimeGridPlugin from '@fullcalendar/resource-timegrid';
import scrollGrid from '@fullcalendar/scrollgrid';
import interactionPlugin from '@fullcalendar/interaction';
import momentTimezonePlugin from '@fullcalendar/moment-timezone';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import GameSquareButton from '@/Components/Dashboard/Buttons/GameSquareButton.vue';
import SearchInput from '@/Components/Dashboard/SearchInput.vue';
import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';
import plLocale from '@fullcalendar/core/locales/pl';
import { Announcement, Feature, Game, Reservation, Slot, Tag } from '@/Types/models';
import { router, usePage } from '@inertiajs/vue3';
import dayjs, { Dayjs } from 'dayjs';
import { CalendarOptions, EventContentArg } from '@fullcalendar/core';
import { ResourceInput } from '@fullcalendar/resource';
import { useQueryString } from '@/Composables/useQueryString';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Card from '@/Components/Dashboard/Card.vue';
import ExportDropdown from '@/Components/Dashboard/ExportDropdown.vue';
import ReservationTable from '@/Components/Dashboard/ReservationTable.vue';
import {
	DateOpeningHours,
	PaginatedResource,
	ReservationSearchResults,
	ReservationStoredNotification,
} from '@/Types/responses';
import { useExport } from '@/Composables/useExport';
import TableSearch from '@/Components/Dashboard/TableSearch.vue';
import Popper from 'vue3-popper';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';
import ReservationsSearchResult from '@/Pages/Club/Reservations/Partials/ReservationsSearchResult.vue';
import BulbButton from '@/Pages/Club/Reservations/Partials/BulbButton.vue';
import DangerCircleIcon from '@/Components/Dashboard/Icons/DangerCircleIcon.vue';
import UnnumberedTablesCalendar from '@/Pages/Club/Reservations/Custom/UnnumberedTables/Calendar.vue';
import NumberedTablesCalendar from '@/Pages/Club/Reservations/Custom/NumberedTables/Calendar.vue';
import ReservationCreateModal from '@/Components/Dashboard/Modals/ReservationCreateModal.vue';
import { useModal } from '@/Composables/useModal';
import StatusModal from '@/Components/Dashboard/Modals/StatusModal.vue';
import ReservationTableColumnEditor from '@/Components/Dashboard/ReservationTableColumnEditor.vue';
import NewPriceModal from '@/Components/Dashboard/Modals/NewPriceModal.vue';
import { useCalendar } from '@/Composables/useCalendar';
import { wTrans } from 'laravel-vue-i18n';
import { useNumber } from '@/Composables/useNumber';
import ChevronDownIcon from '@/Components/Dashboard/Icons/ChevronDownIcon.vue';
import ChevronUpIcon from '@/Components/Dashboard/Icons/ChevronUpIcon.vue';
import SuccessCircleIcon from '@/Components/Dashboard/Icons/SuccessCircleIcon.vue';
import { useString } from '@/Composables/useString';
import { usePanelStore } from '@/Stores/panel';

const props = defineProps<{
	dateLocale: string;
	game: Game;
	slots: Slot[];
	announcement?: Announcement;
	calendarReservations: Reservation[];
	reservations: PaginatedResource<Reservation>;
	canceledReservations: PaginatedResource<Reservation>;
	reservationTableHeadings: Record<string, string>;
	availableTags?: Tag[];
	preview_mode: boolean;
	parentSlotsFillData?: {
		id: number;
		name: string;
		capacity: number;
		occupied: number;
	};
	openingHours: DateOpeningHours;
	calendarHourRange: {
		from: string;
		to: string;
	};
}>();
const { formatAmount } = useNumber();
const { capitalize } = useString();
const { queryValue, queryArray, queryUrl } = useQueryString();
const { clubSettings } = useCalendar(props.game);
const { saveScrollTop, passScrollTop } = useModal();
// date filter
const date = ref<string>(queryValue(window.location.search, 'filters[reservations][startRange][from]'));
watch(date, async () => {
  let currentQueryArray: Record<string, string> = queryArray(window.location.search);
  router.visit(queryUrl(setValueToDateFilters(currentQueryArray, date.value)), {
    preserveScroll: true,
  });
});

let panelStore = usePanelStore();
const canceledReservationsRendered = ref(null);
const reservationsRendered = ref(null);
let calendarReservationsHelper = null;
const calendarReservationsRendered = ref<Reservation[] | null>(null);

function renderReservationsVariables() {
	canceledReservationsRendered.value = { ...props.canceledReservations };
	canceledReservationsRendered.value.data = Object.values(canceledReservationsRendered.value.data);

	reservationsRendered.value = { ...props.reservations };
	reservationsRendered.value.data = Object.values(reservationsRendered.value.data);

	calendarReservationsHelper = { ...props.calendarReservations };
	calendarReservationsRendered.value = Object.values(calendarReservationsHelper);
}

renderReservationsVariables();

const reservationStoredNotification = ref<string>('');
let reservationStoredNotificationHideTimeout: any = null;

const getDurationInMinutes = (start, end) => {
	const startDate = dayjs(start);
	const endDate = dayjs(end);
	return endDate.diff(startDate, 'minute'); // Zwraca różnicę w minutach.
};

const timeGridSlotHeight = computed<string>(() => {
	// let additionalCalendarEntryLinesCount = 0;
	// if (clubSettings['reservationNumberOnCalendarStatus']) {
	// 	additionalCalendarEntryLinesCount += 1;
	// }
	// if (clubSettings['reservationNotesOnCalendarStatus']) {
	// 	let reservationsWithCustomerNoteToShow: (string | number)[] | undefined =
	// 		calendarReservationsRendered.value
	// 			?.filter(
	// 				(reservation: Reservation) =>
	// 					reservation.customer_note &&
	// 					reservation.show_customer_note_on_calendar &&
	// 					reservation.customer_note.length > 0,
	// 			)
	// 			.map((obj) => obj.number);
	// 	let reservationsWithClubNoteToShow: (string | number)[] | undefined = calendarReservationsRendered.value
	// 		?.filter(
	// 			(reservation: Reservation) =>
	// 				reservation.club_note && reservation.show_club_note_on_calendar && reservation.club_note.length > 0,
	// 		)
	// 		.map((obj) => obj.number);
	// 	let addExtraLineForCreatedAtField: boolean = false;
	// 	if (
	// 		(reservationsWithCustomerNoteToShow?.length ?? 0) > 0 ||
	// 		(reservationsWithClubNoteToShow?.length ?? 0) > 0
	// 	) {
	// 		additionalCalendarEntryLinesCount += 1;
	// 		addExtraLineForCreatedAtField = true;
	// 	}
	// 	if (
	// 		reservationsWithCustomerNoteToShow?.some(
	// 			(reservationNumber) => reservationsWithClubNoteToShow?.includes(reservationNumber),
	// 		)
	// 	) {
	// 		additionalCalendarEntryLinesCount += 1;
	// 		addExtraLineForCreatedAtField = true;
	// 	}
	// 	if (addExtraLineForCreatedAtField) {
	// 		additionalCalendarEntryLinesCount += 1;
	// 	}
	// }
	// let shortestDuration: number = 300;
	// for (let reservation of calendarReservationsRendered.value ?? []) {
	// 	const duration = getDurationInMinutes(reservation.start_datetime, reservation.end_datetime);
	// 	if (duration < shortestDuration) {
	// 		shortestDuration = duration;
	// 	}
	// }
	return '35px';
});
const timeGridSlotHeightRendered = ref<string>(timeGridSlotHeight.value);
watch(timeGridSlotHeight, () => {
	let scrollContainers = calendarRef.value.$el.querySelectorAll('.fc-scroller');
	for (let i = 0; i < scrollContainers.length; i++) {
		if (scrollContainers[i].clientHeight > 100) {
			let fromParts = props.calendarHourRange['from'].split(':');
			let toParts = props.calendarHourRange['to'].split(':');
			let fromMinutes = parseInt(fromParts[0]) * 60 + parseInt(fromParts[1]);
			let toMinutes = parseInt(toParts[0]) * 60 + parseInt(fromParts[1]);
			let minutesCountToday = toMinutes - fromMinutes;
			let scrollRatio = scrollContainers[i].scrollTop / scrollContainers[i].scrollHeight;
			let minutesToScrollFromClubOpening = minutesCountToday * scrollRatio;
			let minutesToScroll = minutesToScrollFromClubOpening + fromMinutes;
			panelStore.calendarNextTimeToScroll =
				((minutesToScroll - (minutesToScroll % 60)) / 60).toString() + ':' + Math.floor(minutesToScroll % 60);
			panelStore.calendarStatusModalShowing = statusModalShowing.value;
			panelStore.calendarStatusModalHeading = statusModalHeading.value;
			panelStore.calendarStatusModalContent = statusModalContent.value;
			panelStore.calendarStatusModalType = statusModalType.value;
			break;
		}
	}
	router.visit(window.location.href, {
		preserveScroll: true,
	});
});
// modals

function getFirstAvailableDatetime(): string {
  let baseDate = dayjs.tz(date.value, panelStore.timezone);
  let year = parseInt(baseDate.format('YYYY'));
  let month = parseInt(baseDate.format('MM'));
  let day = parseInt(baseDate.format('DD'));
  let currentDate = dayjs().year(year).month(month - 1).date(day);
	let nextBlockStart = currentDate
		.tz(panelStore.timezone, true)
		.minute(
        currentDate.tz(panelStore.timezone, true).minute() -
				(currentDate.tz(panelStore.timezone, true).minute() % clubSettings['calendarTimeScale']),
		)
		.add(clubSettings['calendarTimeScale'], 'minute');
	return nextBlockStart.format('YYYY-MM-DD HH:mm');
}

const reservationCreateModalStartDatetime = ref<string>(getFirstAvailableDatetime());
const reservationCreateModalDuration = ref<number>(0);
const reservationCreateModalResetFieldsControl = ref<number>(0);
const reservationCreateModalParentSlotId = ref<number | null>(null);
const reservationCreateModalSlotId = ref<number | null>(null);

const reservationCreateModalShowing = ref<boolean>(false);
const statusModalShowing = ref<boolean>(panelStore.calendarStatusModalShowing ?? false);
const newPriceModalShowing = ref<boolean>(false);

const statusModalHeading = ref<string>(panelStore.calendarStatusModalHeading ?? '');
const statusModalContent = ref<string>(panelStore.calendarStatusModalContent ?? '');
const statusModalType = ref<string>(panelStore.calendarStatusModalType ?? 'success');
const newPriceModalReservationNumber = ref<string | null>(null);
const newPriceModalSlotId = ref<number | null>(null);
const newPriceHourRange = ref<{ from?: string | null; to?: string | null }>({ from: null, to: null });
const reservationTableExpandedStatus = ref<boolean>(true);
const canceledReservationTableExpandedStatus = ref<boolean>(true);

function closeStatusModal() {
	statusModalShowing.value = false;
	panelStore.clearCalendarMemoryVariables();
}
function showReservationCreateModal() {
	reservationCreateModalResetFieldsControl.value++;
  reservationCreateModalSlotId.value = getFirstAvailableSlot()?.id;
	setTimeout(() => {
		reservationCreateModalShowing.value = true;
		saveScrollTop();
	}, 50);
}

function closeNewPriceModal(): void {
	newPriceModalShowing.value = false;
	newPriceModalSlotId.value = null;
	newPriceModalReservationNumber.value = null;
	newPriceHourRange.value = { from: null, to: null };
	currentModifiedEventInfo.revert();
	passScrollTop();
}

function updateReservation(): void {
	statusModalType.value = 'success';
	statusModalShowing.value = true;
	statusModalHeading.value = capitalize(wTrans('main.after-action.ready').value);
	statusModalContent.value = wTrans('reservation.successfully-updated-content').value;
	newPriceModalShowing.value = false;
	passScrollTop();
	reservationShowModalControl.value = '0';
	newPriceModalReservationNumber.value = null;

	router.reload({
		preserveScroll: true,
		onSuccess: reloadReservations,
	});
}

function generateResourceId(slotId: string) {
	return slotId?.toString().padStart(10, '0');
}

function getFirstAvailableSlot(): Slot {
	let result: Slot | null = null;
	props.slots.forEach((slot: Slot) => {
		let isValid: boolean = true;
		events.value
			.filter((event) => event.resourceId === generateResourceId(slot.id))
			.forEach((event) => {
				let currentEventStart: Dayjs = dayjs(event.startDatetime).add(1, 'minute');
				let currentEventEnd: Dayjs = dayjs(event.endDatetime).subtract(1, 'minute');


				let newEventStart: Dayjs = dayjs(getFirstAvailableDatetime());
				let endEventEnd: Dayjs = newEventStart.add(60, 'minute');

				if (currentEventEnd.isAfter(newEventStart) && currentEventStart.isBefore(endEventEnd)) {
					isValid = false;
				}
			});

		if (isValid && result === null) {
			result = slot;
		}
	});
	return result ?? props.slots[0];
}

function numberedTablesSlotSelected(slotId: number, parentSlotId: number | null = null) {
	reservationCreateModalStartDatetime.value = getFirstAvailableDatetime();
  if (parentSlotId) {
    reservationCreateModalParentSlotId.value = parentSlotId;
  }
  setTimeout(() => {
    reservationCreateModalSlotId.value = slotId;
    setTimeout(() => {
      reservationCreateModalShowing.value = true;
    },100);
  },200);
	saveScrollTop();
}

const fixedReservationDurationFeature = ref<Feature | undefined>(props.game.features?.find((feature) => feature.type === 'fixed_reservation_duration'));

function reservationStored(responseData: any): void {
	reservationCreateModalShowing.value = false;
	passScrollTop();

	reservationCreateModalSlotId.value = null;
	reservationCreateModalParentSlotId.value = null;
	reservationCreateModalDuration.value = 60;
  reservationCreateModalResetFieldsControl.value++;
	reservationCreateModalStartDatetime.value = getFirstAvailableDatetime();
	statusModalType.value = 'success';
	statusModalShowing.value = true;
	statusModalHeading.value = capitalize(wTrans('main.after-action.ready').value);
	let slotNumberValueTranslation = usePage().props.gameTranslations[props.game.id]['slot-number-value'];
	let resultLines = [];
	let realReservationEntities = responseData.reservationSlotEntities.filter(
		(reservationSlot: Reservation) =>
			reservationSlot.additionalReservation === undefined || reservationSlot.additionalReservation === false,
	);
	if (realReservationEntities === undefined || realReservationEntities.length === 0) {
		resultLines.push(wTrans('reservation.successfully-stored-content').value);
	} else if (realReservationEntities.length === 1) {
		resultLines.push(
			"<p class='inline-block mb-2'>" +
				wTrans('reservation.successfully-stored-singular-short').value +
				'</p>',
		);
		resultLines.push(
			slotNumberValueTranslation.replace(':value', realReservationEntities[0].slot_name) +
				' - ' +
				capitalize(wTrans('main.price').value) +
				': ' +
				formatAmount(realReservationEntities[0].final_price),
		);
		let endTime = dayjs(realReservationEntities[0].end_datetime).format('HH:mm');
		endTime = endTime === '23:59' ? '24:00' : endTime;
    let line = wTrans('reservation.reservation-hour').value +
        ' ' +
        dayjs(realReservationEntities[0].start_datetime).format('HH:mm');
    if(!(fixedReservationDurationFeature.value && usePage().props.clubSettings['fixed_reservation_duration_status_' + fixedReservationDurationFeature.value.id].value && usePage().props.clubSettings['fixed_reservation_duration_value_' + fixedReservationDurationFeature.value.id].value === 24)) {
      line += ' - ' + endTime;
    }
		resultLines.push(line);
	} else if (realReservationEntities.length > 1) {
		resultLines.push(
			"<p class='inline-block mb-2'>" +
				wTrans('reservation.successfully-stored-singular-short').value +
				'</p>',
		);
		let sum = 0;
		Object.values(realReservationEntities).forEach((item) => {
			resultLines.push(
				slotNumberValueTranslation.replace(':value', item.slot_name) +
					' - ' +
					wTrans('main.price').value +
					': ' +
					formatAmount(item.final_price),
			);
			sum += item.final_price;
		});

		resultLines.push(wTrans('main.final-amount').value + ': ' + formatAmount(sum));
		let endTime = dayjs(realReservationEntities[0].end_datetime).format('HH:mm');
		endTime = endTime === '23:59' ? '24:00' : endTime;
		resultLines.push(
			wTrans('reservation.reservation-hour').value +
				' ' +
				dayjs(realReservationEntities[0].start_datetime).format('HH:mm') +
				' - ' +
				endTime,
		);
	}
	statusModalContent.value = resultLines.join('<br>');

	if (responseData.reservationSlotEntities.length) {
    lastEvent?.reverse();
		responseData.reservationSlotEntities.forEach((reservation: Reservation) => {
			events.value.push({
				source: reservation.source,
				reservationNumberId: reservation.reservation_number_id,
				reservationNumber: reservation.number,
				reservationVisibleNumber: reservation.visible_number,
				reservationTypeColor: reservation.reservation_type_color,
				createdAt: reservation.created_at,
				resourceId: generateResourceId(reservation.slot_id ?? 0),
				hasSets: reservation.sets.length > 0,
				backgroundColor: reservation.calendar_color,
				hasNote: reservation.customer_note || reservation.club_note,
				borderColor: 'transparent',
				title: 'Lorem ipsum',
				start: getStartBlockDatetime(reservation),
				end: getEndBlockDatetime(reservation),
				startDatetime: reservation.start_datetime,
				endDatetime: reservation.end_datetime ?? dayjs.tz().format('YYYY-MM-DD HH:mm'),
				displayName: reservation.display_name,
			});
		});
    reloadReservations();
		router.reload({
			preserveScroll: true,
			onSuccess: reloadReservations,
		});
	}
}

function closeReservationCreateModal(): void {
  reservationCreateModalParentSlotId.value = null;
	reservationCreateModalSlotId.value = null;
	reservationCreateModalStartDatetime.value = getFirstAvailableDatetime();
	reservationCreateModalDuration.value = null;
	reservationCreateModalShowing.value = false;
	passScrollTop();
	getFirstAvailableSlot();
}

function cancelReservation(): void {
	reservationShowModalControl.value = '0';
	reloadReservations();
}

let notCanceledExport = useExport('club.reservations.export', { canceled: 0 });
let canceledExport = useExport('club.reservations.export', { canceled: 1 });

// calendar links
const createDateLink = (dateModifier: DateModifier, value: dayjs.ConfigType): string => {
	let currentQueryArray: QueryArray = queryArray(window.location.search);
	let newDate: string = dayjs(value)[dateModifier](1, 'day').format('YYYY-MM-DD');
	currentQueryArray['reservations'] = '1';
	return queryUrl(setValueToDateFilters(currentQueryArray, newDate));
};
const currentDayLink = computed<string>(() =>
	createDateLink('subtract', dayjs.tz().add(1, 'day').format('YYYY-MM-DD')),
);
const previousDayLink = computed<string>(() => createDateLink('subtract', date.value));
const nextDayLink = computed<string>(() => createDateLink('add', date.value));

// reservation search
const reservationSearchResults = ref<ReservationSearchResults>({
	confirmed: [],
	canceled: [],
});
const reservationSearch = ref<string>('');
const reservationSearchResultsShowing = ref<boolean>(false);

function delayedHideReservationSearch() {
	setTimeout(() => {
		reservationSearchResultsShowing.value = false;
	}, 200);
}

let lastReservationSearch: number = 0;
watchDebounced(
	reservationSearch,
	async () => {
		const response = await axios.get(
			route('club.games.reservations.search', {
				game: props.game,
				'searcher[reservations]': reservationSearch.value,
			}),
		);
		reservationSearchResults.value = response.data;
		if (reservationSearch.value.length > 0) {
			reservationSearchResultsShowing.value = true;
		}
		lastReservationSearch = dayjs.tz().unix();
	},
	{ debounce: 500, maxWait: 1000 },
);

//reservation show modal
const reservationShowModalControl = ref<string | null>('0'); // handling reservation show open in ReservationTable component. Contains reservationNumber.
function openReservationShowModal(reservationNumber: string, withoutScrollSaving = true) {
	reservationShowModalControl.value = reservationNumber;
	saveScrollTop();
}

function openReservationShowModalWithoutScrollSaving(reservationNumber: string, withoutScrollSaving = true) {
	reservationShowModalControl.value = reservationNumber;
}

function setValueToDateFilters(currentQueryArray: Record<string, string>, value: string) {
	currentQueryArray['filters[reservations][startRange][from]'] = value;
	currentQueryArray['filters[reservations][startRange][to]'] = value;
	currentQueryArray['filters[calendar_reservations][startRange][from]'] = value;
	currentQueryArray['filters[calendar_reservations][startRange][to]'] = value;
	return currentQueryArray;
}

type DateModifier = 'add' | 'subtract';
type QueryArray = Record<string, string>;

const resources: ResourceInput[] = props.slots.map((slot: Slot): ResourceInput => {
	return {
		id: `${slot.id.toString().padStart(10, '0')}`,
		title: `${slot.name}`,
	};
});

let slotDuration: string = '1:00:00';
if (clubSettings['calendarTimeScale'] === 30) {
	slotDuration = '0:30:00';
}
if (clubSettings['calendarTimeScale'] === 15) {
	slotDuration = '0:15:00';
}

function getStartBlockDatetime(reservation: Reservation): string {
	const startDatetime: Dayjs = dayjs(reservation.start_datetime);
	const startBlockDatetime: Dayjs = startDatetime
		.minute(
			Math.floor(startDatetime.minute() / clubSettings['calendarTimeScale']) *
				clubSettings['calendarTimeScale'],
		)
		.second(0);



  let eventsOnResource = events.value?.filter((event) => event.resourceId === generateResourceId(reservation.slot_id ?? 0));
  let soonerEventsOnResource = eventsOnResource?.filter((event) => dayjs(event.endDatetime).isAfter(dayjs(reservation.start_datetime)));
  if(soonerEventsOnResource.length === 0) {
    return startBlockDatetime.format('YYYY-MM-DD HH:mm:ss');
  }
  let soonerEventOnResourceWithTheBiggestEndDatetime = soonerEventsOnResource.reduce((prev, current) => (dayjs(prev.endDatetime).isAfter(dayjs(current.endDatetime)) ? prev : current), soonerEventsOnResource[0]);
  return soonerEventOnResourceWithTheBiggestEndDatetime.endDatetime;

}

function roundToNextInterval(endDatetime: Dayjs, calendarTimeScale: number) {
	const remainder = endDatetime.minute() % calendarTimeScale;

	let minutesToAdd;
	if (remainder === 0 && endDatetime.second() === 0) {
		minutesToAdd = 0;
	} else if (remainder === 0) {
		minutesToAdd = calendarTimeScale;
	} else {
		minutesToAdd = calendarTimeScale - remainder;
	}

	const roundedEndDatetime = endDatetime.add(minutesToAdd, 'minute').second(0);

	return roundedEndDatetime;
}

function getEndBlockDatetime(reservation: Reservation): string {
	let reservationsOnCurrentSlot = calendarReservationsRendered.value.filter(
		(currentReservation: Reservation) => currentReservation.slot_id === reservation.slot_id,
	);
	let startDatetimesOnCurrentSlot = reservationsOnCurrentSlot
		.map((currentReservation) => getStartBlockDatetime(currentReservation))
		.sort();
	let firstStartDatetimeAfterReservation = startDatetimesOnCurrentSlot.find(
		(startDatetime) => startDatetime > reservation.start_datetime,
	);
	let endDatetime: Dayjs = reservation.end_datetime ? dayjs(reservation.end_datetime) : dayjs.tz();
	const endDatetimeSlot = dayjs(reservation.end_datetime_raw) ?? dayjs.tz();

	endDatetime = endDatetime.isBefore(endDatetimeSlot) ? endDatetimeSlot : endDatetime;

	endDatetime = roundToNextInterval(endDatetime, clubSettings['calendarTimeScale']).format(
		'YYYY-MM-DD HH:mm:ss',
	);
	if (firstStartDatetimeAfterReservation) {
		return endDatetime < firstStartDatetimeAfterReservation
			? endDatetime
			: firstStartDatetimeAfterReservation;
	}
  return endDatetime;
}

let events = ref<object[]>([]);

let timerStatusCustomColors: Record<number, string> = {
	2: '#6967CE',
};

function reloadReservations() {
	renderReservationsVariables();

	events.value = [];

	calendarReservationsRendered.value.forEach((reservation: Reservation) => {
		events.value.push({
			source: reservation.source,
			reservationNumberId: reservation.reservation_number_id,
			reservationNumber: reservation.number,
			reservationVisibleNumber: reservation.visible_number,
			reservationTypeColor: reservation.reservation_type_color,
			createdAt: reservation.created_at,
			resourceId: generateResourceId(reservation.slot_id ?? 0),
			hasSets: (reservation.sets?.length ?? 0) > 0,
			calendarColor: reservation.calendar_color,
			backgroundColor: timerStatusCustomColors.hasOwnProperty(reservation.timer_status)
				? timerStatusCustomColors[reservation.timer_status]
				: reservation.calendar_color,
			sets: reservation.sets,
			hasNote: reservation.customer_note || reservation.club_note,
			clubNote: computed<string>(() => {
				return reservation.club_note;
			}),
			showClubNoteOnCalendar: reservation.show_club_note_on_calendar,
			customerNote: reservation.customer_note,
			showCustomerNoteOnCalendar: reservation.show_customer_note_on_calendar,
			borderColor: 'transparent',
			title: 'Lorem ipsum',
			timerStatus: reservation.timer_status,
			start: getStartBlockDatetime(reservation),
			end: getEndBlockDatetime(reservation),
			reservationDuration: dayjs(reservation.end_datetime ?? dayjs.tz().format('YYYY-MM-DD HH:mm')).diff(
				reservation.start_datetime,
				'minute',
			),
			startDatetime: reservation.start_datetime ?? dayjs.tz().format('YYYY-MM-DD HH:mm'),
			endDatetime: reservation.end_datetime ?? dayjs.tz().format('YYYY-MM-DD HH:mm'),
			displayName: reservation.display_name,
		});
	});
}

let currentModifiedEventInfo = null;

let lastEvent = null;
function modifyEvent(info) {
  lastEvent = info;
	saveScrollTop();
	currentModifiedEventInfo = info;
	let startAt: Dayjs = dayjs(info.event.start).tz(panelStore.timezone, true);
	let endAt: Dayjs = dayjs(info.event.end).tz(panelStore.timezone, true);

	let oldSlotId = info.oldResource?.id ?? 0;
	let newSlotId = info.newResource?.id ?? 0;
	let oldSlot: Slot = props.slots.find((slot) => slot.id === parseInt(oldSlotId)) as Slot;
	let newSlot: Slot = props.slots.find((slot) => slot.id === parseInt(newSlotId)) as Slot;
	let reservation = props.calendarReservations.find(
		(reservation) => reservation.reservation_number_id === info.event.extendedProps.reservationNumberId,
	);

	let clubStart = dayjs(date.value + ' ' + props.calendarHourRange['from']);
	let clubEnd = dayjs(date.value + ' ' + props.calendarHourRange['to']);
	if (props.calendarHourRange['to'] < props.calendarHourRange['from']) {
		clubEnd.add(1, 'day');
	}
	if (
		[2, 3].includes(reservation?.timer_status) &&
		(dayjs(currentModifiedEventInfo.event.start).format('HH:mm') !==
			dayjs(getStartBlockDatetime(reservation)).format('HH:mm') ||
			dayjs(currentModifiedEventInfo.event.end).format('HH:mm') !==
				dayjs(getEndBlockDatetime(reservation)).format('HH:mm'))
	) {
		info.revert();
		statusModalShowing.value = true;
		statusModalHeading.value = capitalize(wTrans('main.after-action.error').value);
		statusModalContent.value = wTrans('reservation.reservation-time-changed-when-timer-enabled-error').value;
		statusModalType.value = 'danger';
	} else if (
		oldSlotId &&
		newSlotId &&
		oldSlot.pricelist_id !== newSlot.pricelist_id &&
		reservation?.status === 1
	) {
		info.revert();
		statusModalShowing.value = true;
		statusModalHeading.value = capitalize(wTrans('main.after-action.error').value);
		statusModalContent.value = wTrans('reservation.reservation-moved-to-different-pricelist-error').value;
		statusModalType.value = 'danger';
	} else if (
		oldSlotId &&
		newSlotId &&
		oldSlot.pricelist_id !== newSlot.pricelist_id &&
		[2, 3].includes(reservation?.timer_status)
	) {
		info.revert();
		statusModalShowing.value = true;
		statusModalHeading.value = capitalize(wTrans('main.after-action.error').value);
		statusModalContent.value = wTrans(
			'reservation.reservation-moved-to-different-pricelist-timer-error',
		).value;
		statusModalType.value = 'danger';
	} else if (reservation?.status === 0 && reservation?.payment_method_online === true) {
		info.revert();
		statusModalShowing.value = true;
		statusModalHeading.value = capitalize(wTrans('main.after-action.error').value);
		statusModalContent.value = wTrans('reservation.pending-reservation-modified-error').value;
		statusModalType.value = 'danger';
	} else if (
		dayjs(reservation.start_datetime).diff(reservation.end_datetime, 'minute') !==
			startAt.diff(endAt, 'minute') &&
		reservation?.status === 1
	) {
		info.revert();
		statusModalShowing.value = true;
		statusModalHeading.value = capitalize(wTrans('main.after-action.error').value);
		statusModalContent.value = wTrans('reservation.paid-reservation-duration-change-error').value;
		statusModalType.value = 'danger';
	} else if (startAt.isBefore(clubStart) || endAt.isAfter(clubEnd)) {
		info.revert();
		statusModalShowing.value = true;
		statusModalHeading.value = capitalize(wTrans('main.after-action.error').value);
		statusModalContent.value = wTrans('reservation.the-club-is-closed-during-these-hours').value;
		statusModalType.value = 'danger';
	} else {
		newPriceModalReservationNumber.value = info.event.extendedProps.reservationNumberId;
		newPriceModalSlotId.value = parseInt(
			info.newResource?.id ??
				calendarReservationsRendered.value.find(
					(reservation) => reservation.reservation_number_id === newPriceModalReservationNumber.value,
				)?.slot_id,
		);
		newPriceHourRange.value = {
			from: [2, 3].includes(reservation?.timer_status)
				? reservation.start_datetime
				: startAt.format('YYYY-MM-DD HH:mm:ss'),
			to: [2, 3].includes(reservation?.timer_status)
				? reservation.end_datetime
				: endAt.format('YYYY-MM-DD HH:mm:ss'),
		};
		newPriceModalShowing.value = true;
	}
}

function paidReservationMovedToDifferentPrice() {
  lastEvent.revert();
  statusModalShowing.value = true;
  statusModalHeading.value = capitalize(wTrans('main.after-action.error').value);
  statusModalContent.value = wTrans('reservation.reservation-moved-to-different-pricelist-error').value;
  statusModalType.value = 'danger';
}

dayjs.tz.setDefault(usePage().props.user.club.country.timezone);
let now = dayjs.tz();
let scrollTime = now
	.minute(now.minute() - (now.minute() % 30))
	.second(0)
	.subtract((clubSettings['calendarTimeScale'] as number) * 4, 'minute')
	.format('HH:mm:ss');
const calendarOptions = ref<CalendarOptions>({
	locales: [plLocale],
	locale: 'pl',
	timeZone: panelStore.timezone,
	expandRows: true,
	allDaySlot: false,
	slotDuration: slotDuration,
	slotLabelInterval: slotDuration,
	snapDuration: '1:00:00',
	slotEventOverlap: false,
	scrollTime: panelStore.calendarNextTimeToScroll ?? scrollTime,
	nowIndicator: true,
	plugins: [resourceTimeGridPlugin, scrollGrid, interactionPlugin, momentTimezonePlugin],
	initialView: 'resourceTimeGridDay',
	initialDate: date.value,
	slotMinTime: props.calendarHourRange['from'].slice(0, -3),
	slotMaxTime: props.calendarHourRange['to'].slice(0, -3),
	eventResizableFromStart: true,
	selectable: true,
	displayEventTime: true,
	longPressDelay: 500,
	schedulerLicenseKey: '0988142021-fcs-1685948574',
	eventOverlap: false,
	selectOverlap: false,
	slotLabelClassNames: 'fc-slot',
	dayMinWidth: 150,
	events: events,
	editable: !props.preview_mode,
	slotLabelFormat: {
		hour: '2-digit',
		minute: '2-digit',
		meridiem: false,
		hour12: false,
	},
	businessHours: {
		daysOfWeek: [0, 1, 2, 3, 4, 5, 6, 7],
		startTime: props.calendarHourRange['from'].slice(0, -3),
		endTime: props.calendarHourRange['to'].slice(0, -3),
	},
	eventDrop: function (info) {
		modifyEvent(info);
	},
	eventResize: function (info) {
		modifyEvent(info);
	},
	select: function (info) {
		if (props.preview_mode) return;

		if (info.allDay === false /* && isWithinBusinessHours(info.start) && isWithinBusinessHours(info.end)*/) {
			reservationCreateModalResetFieldsControl.value++;
			setTimeout(() => {
				let slotId = parseInt(info.resource.id);
				reservationCreateModalStartDatetime.value = info.startStr;
				reservationCreateModalDuration.value = Math.max(
					dayjs(info.endStr).diff(dayjs(info.startStr), 'minutes'),
					60,
				);
        if(props.game.features?.find((feature: Feature) => feature.type === 'slot_has_parent')) {
          reservationCreateModalParentSlotId.value = props.slots.find((slot) => slot.id === slotId)?.slot_id;
        }
				reservationCreateModalSlotId.value = slotId;
				reservationCreateModalShowing.value = true;
				saveScrollTop();
			}, 150);
		}
	},
	eventClick: function (info) {
		if (!preventShowModalOpening) {
			openReservationShowModal(info.event.extendedProps.reservationNumberId);
		}
	},
	resources: resources,
	eventContent: function (renderProps: EventContentArg) {
		return (
			<div
				className={`h-full ${
					isTimerButtonsAvailable(renderProps.event.extendedProps) ? 'event-wrapper' : ''
				}`}>
				<div class="timers h-full w-full items-center justify-center rounded bg-black/50">
					{[1, 3].includes(renderProps.event.extendedProps.timerStatus) && (
						<svg
							class="h-8 w-8 hover:text-gray-2"
							fill="currentColor"
							onClick={() => timerAction(renderProps.event.extendedProps.reservationNumber, 'start')}
							viewBox="0 0 24 24"
							xmlns="http://www.w3.org/2000/svg">
							<path
								clip-rule="evenodd"
								d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z"
								fill-rule="evenodd"
							/>
						</svg>
					)}
					{[2].includes(renderProps.event.extendedProps.timerStatus) && (
						<svg
							class="h-8 w-8 hover:text-gray-2"
							fill="currentColor"
							onClick={() => timerAction(renderProps.event.extendedProps.reservationNumber, 'pause')}
							viewBox="0 0 24 24"
							xmlns="http://www.w3.org/2000/svg">
							<path
								fill-rule="evenodd"
								d="M6.75 5.25a.75.75 0 01.75-.75H9a.75.75 0 01.75.75v13.5a.75.75 0 01-.75.75H7.5a.75.75 0 01-.75-.75V5.25zm7.5 0A.75.75 0 0115 4.5h1.5a.75.75 0 01.75.75v13.5a.75.75 0 01-.75.75H15a.75.75 0 01-.75-.75V5.25z"
								clip-rule="evenodd"
							/>
						</svg>
					)}
					{[2, 3].includes(renderProps.event.extendedProps.timerStatus) && (
						<svg
							class="h-8 w-8 hover:text-gray-2"
							fill="currentColor"
							onClick={() => timerAction(renderProps.event.extendedProps.reservationNumber, 'stop')}
							viewBox="0 0 24 24"
							xmlns="http://www.w3.org/2000/svg">
							<path
								clip-rule="evenodd"
								d="M4.5 7.5a3 3 0 013-3h9a3 3 0 013 3v9a3 3 0 01-3 3h-9a3 3 0 01-3-3v-9z"
								fill-rule="evenodd"
							/>
						</svg>
					)}
				</div>
				<div class="data">
					{renderProps.event.extendedProps.reservationTypeColor && (
						<div
							className="absolute left-0 top-0 h-full w-8 bg-black text-center"
							style={{ backgroundColor: renderProps.event.extendedProps.reservationTypeColor }}></div>
					)}
					<div className="mt absolute left-0 top-0 h-full w-full overflow-hidden text-center">
						<p className="text-xxs font-bold">
							{dayjs(renderProps.event.extendedProps.startDatetime).format('HH:mm')} -{' '}
							{dayjs(renderProps.event.extendedProps.endDatetime).format('HH:mm')}
						</p>
						<div className="flex flex-wrap items-center justify-center space-x-1 text-xs font-normal leading-[13px] hover:!hidden">
							<p class="w-full">{renderProps.event.extendedProps.displayName}</p>

							{clubSettings['reservationNumberOnCalendarStatus'] &&
								dayjs(renderProps.event.extendedProps.endDatetime).diff(
									dayjs(renderProps.event.extendedProps.startDatetime),
									'minute',
								) > clubSettings['calendarTimeScale'] && (
									<p className="w-full">
										{
											wTrans('reservation.reservation-number-value', {
												value: renderProps.event.extendedProps.reservationVisibleNumber ?? renderProps.event.extendedProps.reservationNumber,
											}).value
										}
									</p>
								)}
							{clubSettings['reservationNotesOnCalendarStatus'] &&
								renderProps.event.extendedProps.showCustomerNoteOnCalendar &&
								dayjs(renderProps.event.extendedProps.endDatetime).diff(
									dayjs(renderProps.event.extendedProps.startDatetime),
									'minute',
								) !== clubSettings['calendarTimeScale'] && (
									<p
										className="w-full text-black"
										style="white-space: nowrap; overflow: hidden;text-overflow: ellipsis;">
										{renderProps.event.extendedProps.customerNote}
									</p>
								)}
							{clubSettings['reservationNotesOnCalendarStatus'] &&
								renderProps.event.extendedProps.showClubNoteOnCalendar &&
								dayjs(renderProps.event.extendedProps.endDatetime).diff(
									dayjs(renderProps.event.extendedProps.startDatetime),
									'minute',
								) !== clubSettings['calendarTimeScale'] && (
									<p
										className="w-full text-black"
										style="white-space: nowrap; overflow: hidden;text-overflow: ellipsis;">
										{renderProps.event.extendedProps.clubNote}
									</p>
								)}
						</div>
					</div>
					{renderProps.event.extendedProps.source === 0 && (
						<div className="absolute left-0 top-0 flex h-full w-full justify-start space-x-1 p-1">
							<div className="flex h-5 w-5 items-center justify-center bg-white/15">
								<svg
									width="11"
									height="12"
									viewBox="0 0 11 12"
									fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path
										fill-rule="evenodd"
										clip-rule="evenodd"
										d="M1.47558 0H0.335938L0.603154 1.10855L2.13849 8.13563H2.14514V8.31411H9.3507V8.17665L10.5662 2.82181L10.7536 2.2171H1.95392L1.63798 0.769886L1.47558 0ZM9.3435 3.32564H2.19614L3.04362 7.20556H8.46276L9.3435 3.32564Z"
										fill="white"
									/>
									<path
										d="M3.80386 11.0843C4.09787 11.0843 4.37983 10.9675 4.58772 10.7596C4.79561 10.5517 4.91241 10.2697 4.91241 9.97573C4.91241 9.68173 4.79561 9.39977 4.58772 9.19187C4.37983 8.98398 4.09787 8.86719 3.80386 8.86719C3.50986 8.86719 3.22789 8.98398 3.02 9.19187C2.81211 9.39977 2.69531 9.68173 2.69531 9.97573C2.69531 10.2697 2.81211 10.5517 3.02 10.7596C3.22789 10.9675 3.50986 11.0843 3.80386 11.0843ZM8.79232 9.97573C8.79232 10.2697 8.67553 10.5517 8.46764 10.7596C8.25975 10.9675 7.97778 11.0843 7.68378 11.0843C7.38977 11.0843 7.10781 10.9675 6.89992 10.7596C6.69202 10.5517 6.57523 10.2697 6.57523 9.97573C6.57523 9.68173 6.69202 9.39977 6.89992 9.19187C7.10781 8.98398 7.38977 8.86719 7.68378 8.86719C7.97778 8.86719 8.25975 8.98398 8.46764 9.19187C8.67553 9.39977 8.79232 9.68173 8.79232 9.97573Z"
										fill="white"
									/>
								</svg>
							</div>
						</div>
					)}
					{(Object.keys(renderProps.event.extendedProps?.sets ?? {}).length > 0 ||
						[2, 3, 4].includes(renderProps.event.extendedProps.timerStatus)) && (
						<div className="absolute right-0 top-0 flex h-full w-full justify-end space-x-1 p-1">
							{Object.keys(renderProps.event.extendedProps?.sets ?? {}).length > 0 && (
								<div className="flex h-5 w-5 items-center justify-center bg-white/15">
									<svg xmlns="http://www.w3.org/2000/svg" height="13" viewBox="0 -960 960 960" width="13">
										<path
											fill="white"
											d="M285-80v-368q-52-11-88.5-52.5T160-600v-280h60v280h65v-280h60v280h65v-280h60v280q0 58-36.5 99.5T345-448v368h-60Zm415 0v-320H585v-305q0-79 48-127t127-48v800h-60Z"
										/>
									</svg>
								</div>
							)}
							{[2, 3, 4].includes(renderProps.event.extendedProps.timerStatus) && (
								<div className="flex h-5 w-5 items-center justify-center bg-white/15">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										fill="none"
										viewBox="0 0 24 24"
										stroke-width="1.5"
										stroke="currentColor"
										className="h-6 w-6">
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
										/>
									</svg>
								</div>
							)}
						</div>
					)}
					{renderProps.event.extendedProps.hasNote && (
						<div className="absolute left-0 top-0 flex h-full w-full justify-end space-x-1 p-1">
							<div className="flex h-5 w-5 items-center justify-center bg-white/15">
								<svg
									width="12"
									height="12"
									viewBox="0 0 63 62"
									fill="currentColor"
									xmlns="http://www.w3.org/2000/svg">
									<path
										fill-rule="evenodd"
										clip-rule="evenodd"
										d="M7.51477 46.882C6.62696 49.5443 5.28636 52.123 3.77418 54.4833C3.16794 55.4301 2.1607 57.0028 1.38174 57.8176C0.807248 58.4189 0.791356 59.4526 1.07293 60.1927C1.32767 60.8609 1.91124 61.4399 2.66072 61.4399C8.85119 61.4399 14.6078 58.9379 19.3779 55.1073L19.9358 54.6595L20.6097 54.8998C24.2335 56.1943 28.0293 56.869 31.8791 56.869C43.1844 56.869 54.2001 51.1301 59.643 41.035C61.7396 37.1463 62.8409 32.8542 62.8409 28.4345C62.8409 24.0136 61.7392 19.7208 59.6419 15.8317C54.1983 5.73771 43.1833 0 31.8791 0C20.5753 0 9.56097 5.73767 4.11732 15.8305C2.01968 19.7201 0.917964 24.0132 0.917964 28.4345C0.917964 34.8135 3.23784 40.7765 7.28608 45.6604L7.73963 46.2077L7.51474 46.8819L7.51477 46.882ZM19.3889 49.9812L17.4923 51.5036C15.2151 53.331 12.6333 54.8477 9.8875 55.8504L6.74974 56.9959L8.31901 54.0468C9.26653 52.2663 10.1044 50.4166 10.7413 48.5015L11.8514 45.163L9.71253 42.596C6.37332 38.5882 4.40341 33.6857 4.40341 28.4345C4.40341 25.6131 4.9605 22.8419 6.03501 20.2341C10.2469 10.0154 21.1531 4.29809 31.879 4.29809C42.6046 4.29809 53.51 10.0143 57.7223 20.2322C58.7975 22.8404 59.3546 25.6123 59.3546 28.4345C59.3546 31.2566 58.7975 34.0285 57.7223 36.6368C53.51 46.8547 42.6046 52.5708 31.879 52.5708C28.3614 52.5708 24.8827 51.9574 21.5723 50.7665L19.3889 49.9811V49.9812ZM45.9853 23.8632C43.7558 23.8632 41.9549 25.906 41.9549 28.4345C41.9549 30.9626 43.7558 33.0054 45.9853 33.0054C48.2145 33.0054 50.0155 30.9626 50.0155 28.4345C50.0155 25.906 48.2145 23.8632 45.9853 23.8632ZM31.879 23.8632C29.6506 23.8632 27.8497 25.906 27.8497 28.4345C27.8497 30.9626 29.6506 33.0054 31.879 33.0054C34.1082 33.0054 35.9091 30.9626 35.9091 28.4345C35.9091 25.906 34.1082 23.8632 31.879 23.8632ZM17.7738 23.8632C15.5443 23.8632 13.7434 25.906 13.7434 28.4345C13.7434 30.9626 15.5443 33.0054 17.7738 33.0054C20.003 33.0054 21.804 30.9626 21.804 28.4345C21.804 25.906 20.003 23.8632 17.7738 23.8632Z"
										fill="currentColor"
									/>
								</svg>
							</div>
						</div>
					)}

					<div className="absolute left-0 top-0 flex h-full w-full items-end justify-end space-x-1 pb-0.5 pr-1.25">
						<p className="text-xxs font-bold">
							{dayjs(renderProps.event.extendedProps.createdAt).format('HH:mm')}
						</p>
					</div>
				</div>
			</div>
		);
	},
});

function isTimerButtonsAvailable(reservationExtendedProps: Object) {
	let reservation = calendarReservationsRendered.value.find(
		(reservation: Reservation) => reservation.number === reservationExtendedProps.reservationNumber,
	);
	let clubStart = dayjs(date.value + ' ' + props.calendarHourRange['from']);
	let clubEnd = dayjs(date.value + ' ' + props.calendarHourRange['to']);
	// if reservation timer is started we should let club stop or pause it, no matter what
	if ([2].includes(reservationExtendedProps.timerStatus)) {
		return true;
	}
	if (![1, 2, 3].includes(reservationExtendedProps.timerStatus)) {
		return false;
	}
	if (reservation.status === 1) {
		return false;
	}
	if (reservation.payment_method_online === true && reservation.status === 0) {
		return false;
	}
	if (dayjs().tz(panelStore.timezone, true).add(30, 'minute').isBefore(reservation.start_datetime)) {
		return false;
	}
	if (dayjs().tz(panelStore.timezone, true).subtract(30, 'minute').isAfter(reservation.start_datetime)) {
		return false;
	}
	if (clubStart.isAfter(dayjs.tz())) {
		return false;
	}
	if (clubEnd.isBefore(dayjs.tz())) {
		return false;
	}
	return true;
}

let preventShowModalOpening = false;
function getFormattedDuration(duration: number): string {
	let hours = (duration - (duration % 60)) / 60;
	let minutes = duration - hours * 60;
	let result: string = `${hours}${wTrans('main.hours-postfix').value}`;
	if (minutes) {
		result += ` ${minutes - 1}min`;
	}
	return result;
}
function timerAction(reservationNumber: string, action: string = 'start'): void {
	preventShowModalOpening = true;
	axios
		.get(
			route(`club.reservations.${action}-timer`, {
				reservationNumber: reservationNumber,
			}),
		)
		.then((response) => {
			let reservation = response.data;
			let reservationNumberKey = Object.keys(events.value).find(
				(eventKey: string) => events.value[eventKey].reservationNumber === reservationNumber,
			);

			if (action === 'stop') {
				if (reservation) {
					let formattedDuration = getFormattedDuration(reservation.extended.duration);
					let finalPrice = reservation.final_price;
					statusModalShowing.value = true;
					statusModalHeading.value = capitalize(wTrans('main.after-action.ready').value);
					statusModalContent.value =
						wTrans('reservation.reservation-timer-stopped').value +
						'<br>' +
						formattedDuration +
						' - ' +
						formatAmount(finalPrice);
					statusModalType.value = 'success';
				}

			} else if (action === 'start') {
				events.value[reservationNumberKey]['timerStatus'] = 2;
				// events.value[reservationNumberKey]['start'] = getStartBlockDatetime(reservation);
				// events.value[reservationNumberKey]['startDatetime'] = dayjs.tz().format('YYYY-MM-DD HH:mm:ss');
			} else if (action === 'pause') {
				events.value[reservationNumberKey]['timerStatus'] = 3;
			}
			events.value[reservationNumberKey]['backgroundColor'] =
				timerStatusCustomColors?.[events.value[reservationNumberKey]['timerStatus']] ??
				events.value[reservationNumberKey].calendar_color;
			preventShowModalOpening = false;
		});
	setTimeout(() => {
		preventShowModalOpening = false;
	}, 2000);
}
const calendarRef = ref();

//we check how wide a single slot should be if we are to fill it with the right amount of them
function updateSlotWidth() {
	setTimeout(() => {
		let calendarWidth = calendarRef.value.$el.clientWidth - 34 - clubSettings['visibleCalendarSlots'];
		calendarOptions.value.dayMinWidth = calendarWidth / clubSettings['visibleCalendarSlots'];
	}, 150);
}

const todayMinutes = computed<number>(() => {
	let clubStart = dayjs(date.value + ' ' + props.calendarHourRange['from']);
	let clubEnd = dayjs(date.value + ' ' + props.calendarHourRange['to']);
	if (props.calendarHourRange['to'] < props.calendarHourRange['from']) {
		clubEnd.add(1, 'day');
	}
	return clubEnd.diff(clubStart, 'minute');
});

const calendarScrollX = computed<string>(() => {
	return clubSettings['visibleCalendarSlots'] >= props.slots.length ? 'hidden' : 'scroll';
});

const calendarScrollY = computed<string>(() => {
	return todayMinutes.value / clubSettings['calendarTimeScale'] < 3 ? 'hidden' : 'scroll';
});

const reloadTriggerer = ref(0);
watchDebounced(
	reloadTriggerer,
	() => {
		router.reload({
			preserveScroll: true,
			preserveState: true,
			onSuccess: reloadReservations,
		});
	},
	{ debounce: 200, maxWait: 500 },
);

onMounted(async () => {
	panelStore.clearCalendarMemoryVariables();

	if (panelStore.channel === null) {
		panelStore.channel = await panelStore.channelCalendarLoad();
	}

	panelStore.lastVisitedCalendarGame = props.game as Game;
	panelStore.lastVisitedCalendarDate = date.value;
	if (calendarRef.value !== undefined) {
		updateSlotWidth();
	}
	panelStore.channel.bind('reservation-stored', function (data: ReservationStoredNotification) {
		let reservationData = data.reservationData;

		if (reservationData.creator_id === null || reservationData.creator_id !== usePage().props.user?.id) {
			if (reservationData.source === 0) {
				if (reservationData.customer_name) {
					reservationStoredNotification.value = wTrans(
						'reservation.reservation-stored-notification-content',
						{
							game_name: reservationData.game_name,
							number: reservationData.number,
							start_at: reservationData.start_at,
							customer_name: reservationData.customer_name,
						},
					).value;
				}
				if (reservationStoredNotificationHideTimeout) {
					clearTimeout(reservationStoredNotificationHideTimeout);
				}
				reservationStoredNotificationHideTimeout = setTimeout(() => {
					reservationStoredNotification.value = '';
					reservationStoredNotificationHideTimeout = null;
				}, 10000);
			}
			router.reload({
				preserveScroll: true,
				onSuccess: reloadReservations,
			});
		}
	});

	panelStore.channel.bind('calendar-data-changed', function () {
		reloadTriggerer.value++;
	});

	panelStore.channel.bind('bulb-status-change', function () {
		reloadTriggerer.value++;
	});
});

let timersInterval = null;
onBeforeUnmount(() => {
	const scrollPosition =
		window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
	if (timersInterval) {
		clearInterval(timersInterval);
	}
	sessionStorage.setItem('scrollPosition', JSON.stringify(scrollPosition));
});

onMounted(() => {
	reloadReservations();
	const scrollPosition = JSON.parse(sessionStorage.getItem('scrollPosition') || '0');
	window.scrollTo(0, scrollPosition);
	if (props.game.features.find((feature: Feature) => feature.type === 'has_timers')) {
		setTimeout(() => {
			timersInterval = setInterval(
				() => {
					router.reload({
						preserveScroll: true,
						preserveState: true,
						onSuccess: reloadReservations,
					});
				},
				15 * 60 * 1000,
			);
		}, initialDelay);
	}
});

function calculateInitialDelay() {
	const now = dayjs.tz();
	const remainder = now.minute() % 15;
	const secondsToNextInterval = (15 - remainder) * 60 - now.second();
	return secondsToNextInterval * 1000;
}

const initialDelay = calculateInitialDelay();
</script>
