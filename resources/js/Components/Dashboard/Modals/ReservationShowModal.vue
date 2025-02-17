<template>
	<Transition>
		<div v-if="showing && !hidden" class="fixed left-0 top-0 z-10 h-screen w-full bg-black/50">
			<div
				v-click-away="hide"
				class="relative top-1/2 m-auto max-h-screen max-w-[912px] -translate-y-1/2 transform overflow-y-auto rounded-md bg-white pb-4 pt-16 md:pt-5">
				<div class="px-4 lg:px-14">
					<div
						class="absolute right-7 top-6 cursor-pointer text-gray-3 transition hover:text-gray-7"
						@click="hide">
						<XIcon />
					</div>
					<div
						class="w-full rounded-md bg-black px-3 py-2 text-white xs:px-8.25 sm:w-fit sm:min-w-80 sm:max-w-98">
						<p class="text-xl font-semibold">
							{{
								$t('reservation.reservation-number-value', {
									value: reservation.number,
								})
							}}
						</p>
						<div class="flex w-full flex-wrap justify-between gap-x-5 pl-0.5 pt-1 text-xxs">
							<div class="capitalize">
								{{ $t('main.created') }}
								{{ dayjs(reservation.created_at).format('DD.MM.YYYY HH:mm') }}
							</div>
							<div v-if="reservation.extended?.creator_email && reservation.extended?.creator_email.length">
								{{ reservation.extended.creator_email }}
							</div>
						</div>
					</div>
					<div class="mt-4 flex flex-wrap justify-between space-y-2 sm:flex-nowrap sm:space-y-0">
						<div class="flex w-full flex-wrap space-y-2 sm:flex-nowrap sm:space-x-3.5 sm:space-y-0">
							<Badge v-if="reservation.extended?.slot?.name" class="black w-full sm:w-fit">
								{{ usePage().props.gameTranslations[reservation.extended?.game.id]['slot-singular-short'] }}
								{{ reservation.extended?.slot.name }}
							</Badge>
							<Badge class="w-full sm:w-fit" :style="{ background: reservation.calendar_color }">
								{{ reservationStatusBadge }}
							</Badge>
							<Badge class="info-dark w-full sm:w-fit">
								{{ $t('reservation.status.' + (!reservation.source ? 'online' : 'offline')) }}
							</Badge>
							<Badge
								v-if="reservation.extended?.reservationType"
								class="brand w-full sm:w-fit"
								:style="{ backgroundColor: reservation.extended?.reservationType.color }">
								{{ reservation.extended?.reservationType?.name }}
							</Badge>
						</div>
						<div class="flex w-full space-x-2 sm:w-fit">
							<Button
								v-if="
									!!usePage().props.user.club.preview === false && // temporary, remove when `preview` field has been removed from `clubs` table, and `preview_mode` has been added to `clubs` table
									!!usePage().props.user.club.preview_mode === false &&
									['manager', 'employee'].includes(usePage().props.user.type) &&
									readonly === false &&
									!reservation.extended?.cancelation_type &&
									!(reservation.status === 0 && reservation.payment_method_online === true)
								"
								class="sm warning !h-8.5 w-full uppercase sm:w-fit"
								@click="editReservation">
								<PencilSquareIcon class="mr-2" />
								{{ $t('main.action.edit') }}
							</Button>
							<Button
								v-if="['manager', 'employee'].includes(usePage().props.user.type) && readonly === false"
								class="sm grey !h-8.5 w-full !px-2 uppercase sm:w-fit"
								@click="$emit('showReservationHistory')">
								<svg
									class="h-5 w-5 fill-white"
									xmlns="http://www.w3.org/2000/svg"
									viewBox="0 0 32 32"
									width="512"
									height="512">
									<g id="Line">
										<path
											d="M27,29.75H11A2.75,2.75,0,0,1,8.25,27v-.45a7.75,7.75,0,0,1,0-15.1V5A2.75,2.75,0,0,1,11,2.25H22.17a2.74,2.74,0,0,1,1.95.81l4.82,4.82a2.74,2.74,0,0,1,.81,2V27A2.75,2.75,0,0,1,27,29.75Zm-17.25-3V27A1.25,1.25,0,0,0,11,28.25H27A1.25,1.25,0,0,0,28.25,27V10.75H24A2.75,2.75,0,0,1,21.25,8V3.75H11A1.25,1.25,0,0,0,9.75,5v6.25H10a7.75,7.75,0,0,1,0,15.5Zm.25-14A6.25,6.25,0,1,0,16.25,19,6.25,6.25,0,0,0,10,12.75ZM22.75,3.89V8A1.25,1.25,0,0,0,24,9.25h4.11a1.39,1.39,0,0,0-.23-.31L23.06,4.12A1.39,1.39,0,0,0,22.75,3.89ZM25,22.75H20a.75.75,0,0,1,0-1.5h5a.75.75,0,0,1,0,1.5ZM8.5,21.25A.74.74,0,0,1,8,21,.75.75,0,0,1,8,20l1.28-1.28V16a.75.75,0,0,1,1.5,0v3a.75.75,0,0,1-.22.53L9,21A.74.74,0,0,1,8.5,21.25ZM25,18.75H21a.75.75,0,0,1,0-1.5h4a.75.75,0,0,1,0,1.5Zm0-4H20a.75.75,0,0,1,0-1.5h5a.75.75,0,0,1,0,1.5Zm-7-4H15a.75.75,0,0,1,0-1.5h3a.75.75,0,0,1,0,1.5Z" />
									</g>
								</svg>
							</Button>
						</div>
					</div>
					<div class="mb-5 mt-5 flex w-full flex-wrap space-y-3">
						<ClientDetail
							v-if="reservation.customer_name && reservation.customer_name.length > 1"
							:value="reservation.customer_name"
							class="w-full lg:w-1/2">
							<IDIcon class="w-8" />
						</ClientDetail>

						<ClientDetail
							v-if="reservation.customer_phone && reservation.customer_phone.length > 1"
							:value="reservation.customer_phone"
							class="w-full lg:w-1/2">
							<PhoneFilledIcon />
						</ClientDetail>

						<ClientDetail
							v-if="reservation.customer_email && reservation.customer_email.length > 1"
							:value="reservation.customer_email"
							class="w-full lg:w-1/2">
							<EnvelopeIcon class="w-8" />
						</ClientDetail>

						<ClientDetail
							v-if="reservation.extended?.customer?.tags && reservation.extended?.customer?.tags.length > 0"
							:value="reservation.extended?.customer?.tags.map((item) => item.name).join(', ')"
							class="w-full lg:w-1/2">
							<HashIcon class="w-8" />
						</ClientDetail>
					</div>
					<div
						v-if="reservation.extended?.customer"
						class="mb-5 mt-3 grid w-full grid-cols-1 gap-x-10 gap-y-3 space-x-0 text-left font-medium text-gray-7/70 xxs:grid-cols-2 sm:grid-cols-5">
						<ClientStat
							:name="$t('customer.turnover-from-reservations-short')"
							:value="formatAmount(reservation.extended?.customer_reservations_turnover)" />

						<ClientStat
							:name="$t('customer.hour-count')"
							:value="reservation.extended?.customer_reservations_hours.toFixed(2) + 'h'" />

						<ClientStat
							:name="$t('customer.reservation-count')"
							:value="reservation.extended?.customer_reservations_count.toString()" />
						<ClientStat
							:name="$t('customer.presence')"
							:value="`${reservation.extended?.customer_presence}%`" />
					</div>
				</div>
				<div v-if="!reservation.extended?.customer" class="h-5 w-full" />
				<hr v-if="reservation.extended?.customer" />
				<div
					v-for="sectionIndex in Math.ceil(
						reservationDetails.filter((reservationDetail: ReservationDetail) => reservationDetail.visible)
							.length / 4,
					)">
					<div class="mb-4 mt-4 px-4 lg:px-14">
						<div class="grid w-full grid-cols-1 gap-y-5 xxs:grid-cols-2 sm:grid-cols-4">
							<ReservationDetail
								v-for="reservationDetail in reservationDetails
									.filter((reservationDetail) => reservationDetail.visible)
									.slice(sectionIndex * 4 - 4, sectionIndex * 4)"
								:name="reservationDetail.name"
								:value="reservationDetail.value">
								<component :is="reservationDetail.icon" />
							</ReservationDetail>
						</div>
					</div>
					<hr v-if="sectionIndex + 1 !== reservationDetails.length" />
				</div>
				<div class="grid w-full grid-cols-1 gap-y-5 px-4 text-sm text-gray-5 sm:grid-cols-2 lg:px-14">
					<div class="border-b border-r-0 border-solid border-gray-3/40 pb-5 pt-3 sm:border-b-0 sm:border-r">
						<div class="mb-2 flex justify-start">
							<div>
								<MessageIcon class="mr-1.5 h-6.5 w-6.5 text-brand-base" />
							</div>
							<div class="mt-0.5 font-semibold">
								{{ $t('reservation.customer-note') }}:
								<br />
							</div>
						</div>
						<p class="max-w-85 break-words" v-html="reservation.customer_note ? formattedCustomerNote : $t('reservation.no-customer-note')"></p>
					</div>
					<div class="pb-5 pt-3 sm:pl-4 md:pl-6">
						<div class="mb-2 flex justify-start">
							<div>
								<MessageIcon class="mr-1.5 h-6.5 w-6.5 text-brand-base" />
							</div>
							<div class="mt-0.5 font-semibold">
								{{ $t('reservation.club-note') }}:
								<br />
							</div>
						</div>
						<p class="max-w-85 break-words" v-html="reservation.club_note ? formattedClubNote : $t('reservation.no-club-note')"></p>
					</div>
				</div>
				<hr />
				<div v-if="timerActionsVisibleStatus" class="my-5 flex h-12 items-center space-x-3 px-4 lg:px-14">
					<div>
						<p class="font-semibold !text-gray-6">{{ $t('reservation.timer-status') }}:</p>
						<p class="!text-gray-5">
							{{ capitalize($t(`reservation.timer-statuses.${reservation.timer_status.toString()}`)) }}
						</p>
					</div>
					<Button
						v-if="[1, 3].includes(reservation.timer_status)"
						:class="{
							'!bg-gray-2 !shadow-none':
								dayjs(reservation.start_datetime).format('YYYY-MM-DD') !== dayjs().format('YYYY-MM-DD'),
						}"
						class="!px-5"
						@click="timerAction('start')">
						<svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path
								clip-rule="evenodd"
								d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z"
								fill-rule="evenodd" />
						</svg>
					</Button>
					<Button
						v-if="[2].includes(reservation.timer_status)"
						class="info !px-5"
						@click="timerAction('pause')">
						<svg class="h-8 w-8 hover:text-gray-2" fill="currentColor">
							<path
								fill-rule="evenodd"
								d="M6.75 5.25a.75.75 0 01.75-.75H9a.75.75 0 01.75.75v13.5a.75.75 0 01-.75.75H7.5a.75.75 0 01-.75-.75V5.25zm7.5 0A.75.75 0 0115 4.5h1.5a.75.75 0 01.75.75v13.5a.75.75 0 01-.75.75H15a.75.75 0 01-.75-.75V5.25z"
								clip-rule="evenodd" />
						</svg>
					</Button>
					<Button
						v-if="[2, 3].includes(reservation.timer_status)"
						class="danger !px-5"
						@click="timerAction('stop')">
						<svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path
								clip-rule="evenodd"
								d="M4.5 7.5a3 3 0 013-3h9a3 3 0 013 3v9a3 3 0 01-3 3h-9a3 3 0 01-3-3v-9z"
								fill-rule="evenodd" />
						</svg>
					</Button>
				</div>
				<div
					v-else-if="
						!getSettingsByFeatureType('timers_status')?.find(
							(setting) => setting.feature.id == findByFeatureType('has_timers')?.id,
						)?.value
						&&
						getSettingsByFeatureType('bulb_status')?.find(
							(setting) => setting.feature.id == findByFeatureType('slot_has_bulb')?.id,
						)?.value
						&&
						reservation.extended.slotFeatures.find((feature) => feature.type === 'slot_has_bulb')
						&&
						JSON.parse(reservation.extended.slotFeatures.find((feature) => feature.type === 'slot_has_bulb').pivot.data)['name'].length
						&&
						dayjs().diff(reservation.start_datetime, 'minutes') > -30
					"
					class="my-5 flex w-1/2 flex-col space-y-3 px-4 lg:px-14">
					<p
						class="w-full py-2 text-center"
						:class="{
							'bg-gray-2': getFeatureDataByType('slot_has_bulb').type === 'nothing',
							'bg-yellow-200': getFeatureDataByType('slot_has_bulb').type !== 'nothing',
						}">
						{{
							getFeatureDataByType('slot_has_bulb').type === 'nothing'
								? $t('reservation.bulbs.label')
								: $t('reservation.bulbs.switch-off-time', {
										time: getFeatureDataByType('slot_has_bulb').time || '-',
								  })
						}}
					</p>
					<div v-for="bulbsOption in bulbsOptions" class="mb-3 flex items-center space-x-3">
						<Radio
							:id="bulbsOption.code?.toString()"
							name="bulbsStatus"
							:checked="getFeatureDataByType('slot_has_bulb').type === bulbsOption.code"
							@change="updateFeatureReservation('slot_has_bulb', { type: bulbsOption.code })" />

						<InputLabel :for="bulbsOption.code">
							{{ bulbsOption.label }}
						</InputLabel>
					</div>
				</div>
				<div
					v-if="Object.keys(reservation.extended?.relatedReservations ?? {}).length"
					class="mt-5 w-full px-0 lg:px-14">
					<div class="bg-gray-3/25 pb-3 pl-4 pt-4 md:pl-8 lg:rounded-md">
						<div class="flex items-center text-xl font-semibold">
							<PeopleIcon class="mr-3.5 h-5 w-8 text-brand-base" />
							{{ $t('reservation.related-reservations') }}
						</div>
						<div class="mt-2 w-full text-sm">
							<div
								v-for="relatedReservation in reservation.extended?.relatedReservations"
								class="inline-block pr-1">
								<p
									class="inline cursor-pointer underline"
									@click="$emit('showReservationByReservationNumber', relatedReservation.number)">
									{{ relatedReservation.visibleNumber }}
								</p>
								-
								{{
									usePage().props.gameTranslations[reservation.extended.game.id]['slot-singular-short']
								}}
								{{ relatedReservation?.slot_name ?? `(${capitalize($t('main.deleted'))})` }}
								({{ reservationStatus(relatedReservation) }}){{
									reservation.extended?.relatedReservations[
										Object.keys(reservation.extended?.relatedReservations).pop()
									] !== relatedReservation
										? ','
										: ''
								}}
							</div>
						</div>
						<div
							v-if="
								!!usePage().props.user.club.preview === false && // temporary, remove when `preview` field has been removed from `clubs` table, and `preview_mode` has been added to `clubs` table
								!!usePage().props.user.club.preview_mode === false &&
								reservation.extended?.cancelation_type === null &&
								['manager', 'employee'].includes(usePage().props.user.type) &&
								readonly === false
							">
							<div class="mt-3 flex w-full items-center justify-end pr-5.5">
								<Checkbox
									id="cancelRelatedReservations"
									v-model="cancelationForm.cancelRelatedReservations"
									:checked="cancelationForm.cancelRelatedReservations"
									class="bg-gray-3/40" />
								<InputLabel
									:value="$t('reservation.cancel-related-reservations')"
									class="ml-3 cursor-pointer font-normal"
									for="cancelRelatedReservations" />
							</div>
						</div>
					</div>
					<div
						v-if="Object.keys(reservation.extended?.relatedReservations ?? {}).length ?? 0"
						class="mt-4 grid w-full grid-cols-3 items-center gap-x-5">
						<div class="space-y-2">
							<InputLabel>{{ capitalize($t('main.sum')) }}</InputLabel>
							<TextContainer>{{ formatAmount(reservation.extended?.total_price) }}</TextContainer>
						</div>
						<div class="space-y-2">
							<InputLabel>{{ capitalize($t('main.paid')) }}</InputLabel>
							<TextContainer>{{ formatAmount(reservation.extended?.total_paid) }}</TextContainer>
						</div>
						<div class="space-y-2">
							<InputLabel>{{ capitalize($t('main.to-pay')) }}</InputLabel>
							<TextContainer>
								{{ formatAmount(reservation.extended?.total_price - reservation.extended?.total_paid) }}
							</TextContainer>
						</div>
					</div>
				</div>
				<form
					v-if="
						!!usePage().props.user.club.preview === false && // temporary, remove when `preview` field has been removed from `clubs` table, and `preview_mode` has been added to `clubs` table
						!!usePage().props.user.club.preview_mode === false &&
						reservation.extended?.cancelation_type === null &&
						['manager', 'employee'].includes(usePage().props.user.type) &&
						readonly === false &&
						!(reservation.status === 0 && reservation.payment_method_online === true)
					"
					class="flex flex-wrap space-y-3 px-4 pt-4 md:flex-nowrap md:gap-x-6 md:space-y-0 lg:px-14"
					@submit.prevent="cancelationFormSubmit">
					<div ref="toggle" class="toggle relative w-full md:w-1/2">
						<SimpleSelect
							v-model="cancelationForm.reasonType"
							:append-to-body="true"
							:calculatePosition="withPopper"
							:options="cancelationReasonOptions"
							:placeholder="$t('reservation.cancelation-reason-type-select-placeholder')" />
						<div v-show="cancelationForm.reasonType === 0">
							<InputLabel class="mb-2 mt-5">{{ $t('widget.cancelation-reason') }}</InputLabel>
							<TextareaInput v-model="cancelationForm.reasonContent" />
							<div v-if="cancelationForm.errors.reasonContent" class="error">
								{{ cancelationForm.errors.reasonContent }}
							</div>
						</div>
					</div>
					<div class="flex w-full items-end md:w-1/2">
						<Button class="danger lg !h-12 !w-full !px-0" type="submit">
							{{ $t('reservation.cancel-reservation') }}
						</Button>
					</div>
				</form>
				<div v-if="cancelationForm.errors.reasonType" class="error px-4 lg:px-14">
					{{ cancelationForm.errors.reasonType }}
				</div>
			</div>
		</div>
	</Transition>
</template>

<style scoped>
.toggle:deep(.vs__dropdown-toggle) {
	@apply rounded-md border border-gray-3 hover:border-brand-base;
}
</style>

<script lang="ts" setup>
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import IDIcon from '@/Components/Dashboard/Icons/IDIcon.vue';
import ClockIcon from '@/Components/Dashboard/Icons/ClockIcon.vue';
import CouponIcon from '@/Components/Dashboard/Icons/CouponIcon.vue';
import HourglassIcon from '@/Components/Dashboard/Icons/HourglassIcon.vue';
import DiscountIcon from '@/Components/Dashboard/Icons/DiscountIcon.vue';
import FoodIcon from '@/Components/Dashboard/Icons/FoodIcon.vue';
import MessageIcon from '@/Components/Dashboard/Icons/MessageIcon.vue';
import EnvelopeIcon from '@/Components/Dashboard/Icons/EnvelopeIcon.vue';
import PhoneFilledIcon from '@/Components/Dashboard/Icons/PhoneFilledIcon.vue';
import PeopleIcon from '@/Components/Dashboard/Icons/PeopleIcon.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import Badge from '@/Components/Dashboard/Badge.vue';
import Radio from '@/Components/Dashboard/Radio.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import ClientDetail from '@/Components/Dashboard/Modals/Partials/ReservationView/ClientDetail.vue';
import ClientStat from '@/Components/Dashboard/Modals/Partials/ReservationView/ClientStat.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { createPopper, Instance as PopperInstance } from '@popperjs/core';
import { capitalize, computed, ComputedRef, onMounted, ref, shallowRef, watch } from 'vue';
import {Feature, Game, Reservation, SelectOption, SetModel} from '@/Types/models';
import dayjs from 'dayjs';
import { useNumber } from '@/Composables/useNumber';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { wTrans } from 'laravel-vue-i18n';
import PeopleUnfilledIcon from '@/Components/Dashboard/Icons/PeopleUnfilledIcon.vue';
import ReservationDetail from '@/Components/Dashboard/Modals/Partials/ReservationView/ReservationDetail.vue';
import { emptyReservation } from '@/Utils';
import axios from 'axios';
import ClubIcon from '@/Components/Dashboard/Icons/ClubIcon.vue';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import SuccessSquareIcon from '@/Components/Dashboard/Icons/SuccessSquareIcon.vue';
import DangerSquareIcon from '@/Components/Dashboard/Icons/DangerSquareIcon.vue';
import TextContainer from '@/Components/Dashboard/TextContainer.vue';
import { useReservations } from '@/Composables/useReservations';
import HashIcon from '@/Components/Dashboard/Icons/HashIcon.vue';
import { usePanelStore } from '@/Stores/panel';
import { useCalendar } from '@/Composables/useCalendar';
import { useSettings } from '@/Composables/useSettings';

interface WithPopperOptions {
	width: string;
}

interface ReservationDetail {
	name: string | ComputedRef;
	value: string;
	icon: InstanceType<any>;
	visible: boolean;
}

const props = withDefaults(
	defineProps<{
		showing?: boolean;
    game?: Game | null;
		reservationNumber: string | number;
		readonly?: boolean;
	}>(),
	{
    game: null,
		showing: false,
		readonly: false,
	},
);
const { formatAmount } = useNumber();
const { currencySymbols } = useReservations();
const hidden = ref<boolean | undefined>(undefined);
let activeReservationNumber = null;

const emit = defineEmits<{
	(e: 'closeModal'): void;
	(e: 'showReservationByReservationNumber'): void;
	(e: 'cancelReservation', related: boolean): void;
	(e: 'editReservation'): void;
	(e: 'showReservationHistory'): void;
	(e: 'reload'): void;
}>();

const hide = () => {
	reservation.value = emptyReservation;
	hidden.value = true;
	document.body.classList.remove('modal-open');
	document.documentElement.scrollTop = parseInt(document.body.style.top.replace('-', '').replace('px', ''));
	emit('closeModal');
};

function editReservation() {
	hidden.value = true;
	emit('editReservation');
}

function withPopper(dropdownList: HTMLElement, component: any, options: WithPopperOptions) {
	dropdownList.style.width = options.width;
	const popper: PopperInstance = createPopper(component.$refs.toggle as HTMLElement, dropdownList, {
		placement: 'top',
	});
	return () => popper.destroy();
}

const { getSettingsByFeatureType } = useSettings(usePage().props?.clubSettings ?? []);

// reservation data
const reservation = ref<Reservation>(emptyReservation);
const reservationDetails = shallowRef<ReservationDetail[]>([]);

let reloadReservationInterval = null;
const panel = usePanelStore();

function loadReservation(): void {
	if (props.reservationNumber === '0') {
		hidden.value = true;
		reservation.value = JSON.parse(JSON.stringify(emptyReservation));
		cancelationForm.reasonType = 1;
		cancelationForm.reasonContent = '';
		cancelationForm.reservationNumber = props.reservationNumber;
		cancelationForm.cancelRelatedReservations = false;
		panel.currentShowingReservation = reservation.value;
		return;
	} else {
		axios
			.get(
				route('reservations.show', {
					reservationNumber: props.reservationNumber,
				}),
			)
			.then(function (response: { data: Reservation }) {
				reservation.value = response.data;
        hidden.value = (hidden.value === undefined);
				panel.currentShowingReservation = reservation.value;
				loadReservationDetails();
				if (reloadReservationInterval) {
					clearInterval(reloadReservationInterval);
				}
				if (props.reservationNumber && reservation?.value?.extended?.timer_enabled === true) {
					const now = new Date();
					const currentSeconds = now.getSeconds();
					const delay = (60 - currentSeconds) * 1000;
					setTimeout(() => {
						loadReservation();
						reloadReservationInterval = setInterval(loadReservation, 60000);
					}, delay);
				}
				cancelationForm.cancelRelatedReservations = false;
			});
	}
}

reservation.value = emptyReservation;
hidden.value = true;

function loadReservationDetails(): void {
	let pricePerPersonTypeKeys = Object.keys(usePage().props.clubSettings).filter((key) =>
		key.includes('price_per_person_type'),
	);
	let gamePricePerPersonTypeKey = null;
	pricePerPersonTypeKeys.forEach((pricePerPersonTypeKey) => {
		if (
			usePage().props.clubSettings[pricePerPersonTypeKey]['feature']['game']['id'] ===
			reservation.value.extended.game.id
		) {
			gamePricePerPersonTypeKey = pricePerPersonTypeKey;
		}
	});
	let gamePricePerPersonType = usePage().props.clubSettings[gamePricePerPersonTypeKey]?.['value'];
	let pricePerPersonValue = JSON.parse(
		(
			reservation.value.extended?.features?.find((feature: Feature) => feature.type === 'price_per_person')
				?.pivot?.data ?? JSON.stringify({ person_count: 0 })
		).toString() ?? JSON.stringify({ person_count: 0 }),
	).person_count;

	reservationDetails.value = [];
	reservationDetails.value.push(
		{
			name: wTrans('main.date').value,
			value: formatDate(reservation.value.start_datetime),
			icon: ClockIcon,
			visible: true,
		},
      {
        name: `${wTrans('main.start').value} - ${wTrans('main.end').value}`,
        value:
            dayjs(reservation.value.start_datetime).format('HH:mm') +
            ' - ' +
            (reservation.value.end_datetime !== null
                ? dayjs(reservation.value.end_datetime)
                    .add(reservation.value.timer_status === 2 ? 1 : 0, 'minute')
                    .format('HH:mm')
                : dayjs().format('HH:mm')),
        icon: HourglassIcon,
        visible: !(
            reservation.value.extended?.game?.features?.find(
                (feature) => feature.type === 'fixed_reservation_duration',
            ) && getFeatureSetting('fixed_reservation_duration', 'fixed_reservation_duration_value')?.value === 24
            && getFeatureSetting('fixed_reservation_duration', 'fixed_reservation_duration_status')?.value === true
        ),
      },
      {
        name: `${wTrans('main.start-hour').value}`,
        value:
            dayjs(reservation.value.start_datetime).format('HH:mm'),
        icon: HourglassIcon,
        visible: (
            reservation.value.extended?.game?.features?.find(
                (feature) => feature.type === 'fixed_reservation_duration',
            ) && getFeatureSetting('fixed_reservation_duration', 'fixed_reservation_duration_value')?.value === 24
            && getFeatureSetting('fixed_reservation_duration', 'fixed_reservation_duration_status')?.value === true
        ),
      },
		{
			name: wTrans('reservation.duration-time').value,
			value: reservation.value.extended?.duration
				? getFormattedDuration(reservation.value.extended?.duration)
				: '0h',
			icon: HourglassIcon,
			visible: !(
				reservation.value.extended?.game?.features?.find(
					(feature) => feature.type === 'fixed_reservation_duration',
				) && getFeatureSetting('fixed_reservation_duration', 'fixed_reservation_duration_value')?.value === 24
        && getFeatureSetting('fixed_reservation_duration', 'fixed_reservation_duration_status')?.value === true
			),
		},
		{
			name: wTrans('main.final-amount').value,
			value: formatAmount(reservation.value.final_price ?? 0),
			icon: DiscountIcon,
			visible: true,
		},
		{
			name:
				reservation.value.extended?.game?.features?.find(
					(feature: Feature) => feature.type === 'price_per_person',
				)?.translations['person-count'] ?? '',
			value: pricePerPersonValue,
			icon: PeopleUnfilledIcon,
			visible:
				reservation.value.extended?.game?.features?.find(
					(feature: Feature) => feature.type === 'price_per_person',
				) !== undefined &&
				gamePricePerPersonType &&
				parseInt(pricePerPersonValue) > 0,
		},
	);
	Object.values(reservation.value.sets ?? {}).forEach((set: SetModel) => {
		reservationDetails.value.push({
			name: wTrans('reservation.set').value,
			value: `${set.count} x ${set.name} - ${formatAmount(set.price ?? 0)}`,
			icon: FoodIcon,
			visible: true,
		});
	});
	reservationDetails.value.push(
		{
			name: wTrans('reservation.discount-code').value,
			value: reservation.value.extended?.discountCode
				? reservation.value.extended?.discountCode.code +
				  ' - ' +
				  reservation.value.extended?.discountCode.value +
				  (reservation.value.extended?.discountCode.type === 0
						? '%'
						: currencySymbols[usePage().props.user.club?.country?.currency ?? 'PLN'])
				: '',
			icon: CouponIcon,
			visible: reservation.value.extended?.discountCode !== null,
		},
		{
			name: wTrans('reservation.special-offer').value,
			value: reservation.value.extended?.specialOffer?.name ?? '',
			icon: CouponIcon,
			visible: (reservation.value.extended?.specialOffer ?? null) !== null,
		},
		{
			name:
				reservation.value.extended?.game?.features?.find(
					(feature: Feature) => feature.type === 'slot_has_parent',
				)?.translations['parent-slot'] ?? '',
			value: reservation.value.extended?.slot?.parentSlot?.name ?? '',
			icon: ClubIcon,
			visible:
				reservation.value.extended?.game?.features?.find(
					(feature: Feature) => feature.type === 'slot_has_parent',
				) !== undefined,
		},
		{
			name:
				reservation.value.extended?.game?.features?.find(
					(feature: Feature) => feature.type === 'person_as_slot',
				)?.translations['quantity-reservation-view-label'] ?? '',
			value: reservation.value.extended?.reservation_slots_count?.toString() ?? '0',
			icon: PeopleUnfilledIcon,
			visible:
				reservation.value.extended?.game?.features?.find(
					(feature: Feature) => feature.type === 'person_as_slot',
				) !== undefined,
		},
	);
}

// timer
function timerAction(action: string = 'start'): void {
	axios
		.get(
			route(`club.reservations.${action}-timer`, {
				reservationNumber: props.reservationNumber,
			}),
		)
		.then(() => {
			emit('reload');
			setTimeout(() => {
				loadReservation();
			}, 1500);
		});
}

const timerActionsVisibleStatus = computed<boolean>(() => {
	if (reservation.value.status !== 0) {
		return false;
	}
	if (
		[2, 3].includes(reservation.value.timer_status) ||
		(Math.abs(dayjs().diff(reservation.value.start_datetime, 'minute')) < 30 &&
        (usePage().props.clubSettings['timers_status']?.value ?? false) && reservation.value.extended?.game?.features?.find((feature) => feature.type === 'has_timers'))
	) {
		return true;
	}
	return false;
});

// cancelation form
const cancelationReasonOptions = ref<SelectOption[]>([]);
const cancelationForm = useForm({
	reasonType: 1,
	reasonContent: '',
	reservationNumber: props.reservationNumber,
	cancelRelatedReservations: false,
});

function cancelationFormSubmit(): void {
	cancelationForm.reservationNumber = activeReservationNumber;
	cancelationForm.post(route('club.reservations.cancel'), {
		onFinish: (response) => {
			emit('cancelReservation', cancelationForm.cancelRelatedReservations);
		},
	});
}

// Utilities
function getFeatureSetting(featureType: string, settingKey: string | null = null) {
	let key = Object.keys(usePage().props.clubSettings).find(
		(key) =>
			(usePage().props.clubSettings[key]?.feature?.type ?? '') === featureType &&
			(usePage().props.clubSettings[key]?.feature?.game?.id ?? 0) === reservation.value.extended.game.id &&
      (settingKey === null || key.includes(settingKey)),
	);
	return usePage().props.clubSettings[key];
}

function reservationStatus(reservation: any): string {
	return reservation.status_locale;
}

const reservationStatusBadge = computed<string>(() => {
	if (reservation.value.status === 0 && reservation.value.payment_method_online === true) {
		return wTrans('reservation.status.during-payment').value;
	}
	if (reservation.value.status === 1 && reservation.value.payment_method_id === 1) {
		return wTrans('reservation.status.paid-cash').value;
	}
	if (reservation.value.status === 1 && reservation.value.payment_method_id === 2) {
		return wTrans('reservation.status.paid-cashless').value;
	}
	if (reservation.value.status === 1 && reservation.value.payment_method_id === 3) {
		return wTrans('reservation.status.paid-card').value;
	}
	return wTrans('reservation.statuses.' + reservation.value.status).value;
});

function getFormattedDuration(duration: number): string {
	let hours = (duration - (duration % 60)) / 60;
	let minutes = duration - hours * 60;
	let result: string = `${hours}${wTrans('main.hours-postfix').value}`;
	if (minutes) {
		result += ` ${minutes}min`;
	}
	return result;
}

let formattedClubNote = computed(() => {
  return reservation.value.club_note?.replace(/\n/g, '<br />');
});

let formattedCustomerNote = computed(() => {
  return reservation.value.customer_note?.replace(/\n/g, '<br />');
});

function formatDate(input: string): string {
	return dayjs(input).format('DD.MM.YYYY');
}

const { bulbsOptionsComp } = useCalendar(props.game);

const bulbsOptions: ComputedRef<SelectOption[]> = bulbsOptionsComp;

function findByFeatureType(featureType: string) {
	return reservation.value.extended?.features?.find((feature: Feature) => feature.type === featureType);
}

const getFeatureDataByType = (type: string) => {
	return JSON.parse(findByFeatureType(type)?.pivot?.data ?? '{}');
};

const updateFeatureReservation = (name: string, value: any) => {
	const form = {
		feature: name,
		data: value,
	};

	axios
		.patch(
			route('club.reservations.feature-update', {
				reservationNumber: props.reservationNumber,
			}),
			form,
		)
		.then(() => {
			loadReservation();
		});
};

// vue
watch(
	() => props.reservationNumber,
	async () => {
		activeReservationNumber = !['0', 0, null].includes(props.reservationNumber)
			? props.reservationNumber
			: activeReservationNumber;
		loadReservation();
	},
);

onMounted(() => {
	[1, 2, 0].forEach((cancelationTypeIndex: number) => {
		cancelationReasonOptions.value.push({
			code: cancelationTypeIndex,
			label: wTrans('reservation.cancelation-types.' + cancelationTypeIndex.toString()),
		});
	});
});
</script>
