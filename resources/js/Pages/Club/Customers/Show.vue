<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('club.customers.index'), label: $t('customer.plural') },
			{
				href: route('club.customers.show', { customer: customer }),
				label: (customer.first_name ?? '') + ' ' + (customer.last_name ?? ''),
			},
		]">
		<div class="flex w-full flex-wrap space-y-5 py-8 md:px-10">
			<div class="w-full">
				<Card>
					<div class="flex flex-wrap space-y-5 xl:space-y-0">
						<div class="flex w-full border-b border-b-black/10 pb-5 xl:w-2/3 xl:border-b-0 xl:pr-36">
							<div class="flex w-full flex-wrap justify-between sm:flex-nowrap">
								<form class="sm:w-fix w-full border-b border-b-black/10 pb-5 sm:mr-10 sm:border-b-0">
									<div
										class="flex h-14 items-center"
										:class="{ 'h-17': form.errors.first_name || form.errors.last_name }">
										<div
											class="mr-3 text-brand-base"
											:class="{ '-mt-6': form.errors.first_name || form.errors.last_name }">
											<IDIcon class="w-8" />
										</div>
										<div
											v-if="!editCustomerFormStatus"
											class="flex items-center space-x-2 pl-2 text-xs xxs:text-sm xs:text-base sm:text-xl">
											<p>{{ customer.full_name }}</p>
										</div>
										<div v-else class="flex w-full space-x-3">
											<div class="w-1/2">
												<TextInput
													v-model="form.first_name"
													class="pl-2"
													:placeholder="capitalize($t('main.first-name'))" />
												<div v-if="form.errors.first_name" class="error">
													{{ form.errors.first_name }}
												</div>
											</div>
											<div class="w-1/2">
												<TextInput
													v-model="form.last_name"
													class="pl-2"
													:placeholder="capitalize($t('main.last-name'))" />
												<div v-if="form.errors.last_name" class="error">
													{{ form.errors.last_name }}
												</div>
											</div>
										</div>
									</div>

									<div class="flex h-14 items-center" :class="{ 'h-17': form.errors.email }">
										<div class="mr-3 text-brand-base">
											<EnvelopeIcon class="w-8" :class="{ '-mt-6': form.errors.email }" />
										</div>
										<div
											v-if="!editCustomerFormStatus"
											class="flex items-center space-x-2 pl-2 text-xs xxs:text-sm xs:text-base sm:text-xl">
											<p>{{ customer.email }}</p>
											<svg
												width="14"
												height="14"
												viewBox="0 0 11 12"
												fill="none"
												xmlns="http://www.w3.org/2000/svg"
												v-if="customer.online">
												<path
													fill-rule="evenodd"
													clip-rule="evenodd"
													d="M1.47558 0H0.335938L0.603154 1.10855L2.13849 8.13563H2.14514V8.31411H9.3507V8.17665L10.5662 2.82181L10.7536 2.2171H1.95392L1.63798 0.769886L1.47558 0ZM9.3435 3.32564H2.19614L3.04362 7.20556H8.46276L9.3435 3.32564Z"
													fill="black" />
												<path
													d="M3.80386 11.0843C4.09787 11.0843 4.37983 10.9675 4.58772 10.7596C4.79561 10.5517 4.91241 10.2697 4.91241 9.97573C4.91241 9.68173 4.79561 9.39977 4.58772 9.19187C4.37983 8.98398 4.09787 8.86719 3.80386 8.86719C3.50986 8.86719 3.22789 8.98398 3.02 9.19187C2.81211 9.39977 2.69531 9.68173 2.69531 9.97573C2.69531 10.2697 2.81211 10.5517 3.02 10.7596C3.22789 10.9675 3.50986 11.0843 3.80386 11.0843ZM8.79232 9.97573C8.79232 10.2697 8.67553 10.5517 8.46764 10.7596C8.25975 10.9675 7.97778 11.0843 7.68378 11.0843C7.38977 11.0843 7.10781 10.9675 6.89992 10.7596C6.69202 10.5517 6.57523 10.2697 6.57523 9.97573C6.57523 9.68173 6.69202 9.39977 6.89992 9.19187C7.10781 8.98398 7.38977 8.86719 7.68378 8.86719C7.97778 8.86719 8.25975 8.98398 8.46764 9.19187C8.67553 9.39977 8.79232 9.68173 8.79232 9.97573Z"
													fill="black" />
											</svg>
										</div>
										<div v-else class="flex w-full">
											<TextInput
												v-model="form.email"
												class="pl-2"
												:placeholder="capitalize($t('main.email-address'))"
												:disabled="customer.online"
												:class="{ 'disabled-readable': customer.online }" />
											<div v-if="form.errors.email" class="error">
												{{ form.errors.email }}
											</div>
										</div>
									</div>

									<div class="flex h-14 items-center" :class="{ 'h-17': form.errors.phone }">
										<div class="mr-3 text-brand-base">
											<PhoneFilledIcon class="w-8" :class="{ '-mt-6': form.errors.phone }" />
										</div>
										<div
											v-if="!editCustomerFormStatus"
											class="flex items-center space-x-2 pl-2 text-xs xxs:text-sm xs:text-base sm:text-xl">
											<p>{{ customer.phone }}</p>
										</div>
										<div v-else class="flex w-full flex-wrap">
											<TextInput
												v-model="form.phone"
												class="pl-2"
												:placeholder="capitalize($t('main.phone'))" />
											<div v-if="form.errors.phone" class="error">
												{{ form.errors.phone }}
											</div>
										</div>
									</div>

									<p
										v-if="!editCustomerFormStatus"
										class="ml-0.5 mt-5 cursor-pointer font-bold text-brand-dark"
										@click.prevent="editCustomerFormStatus = !editCustomerFormStatus">
										{{ capitalize($t('main.action.edit')) }}
									</p>
									<p v-else class="ml-0.5 mt-5 cursor-pointer font-bold text-brand-dark" @click="submitForm">
										{{ capitalize($t('main.action.save')) }}
									</p>
								</form>
								<div class="mt-5 text-left md:mt-0">
									<ReservationDetail
										:name="capitalize($t('main.date'))"
										:value="
											latestReservation ? dayjs(latestReservation.created_at).format('DD.MM.YYYY') : '---'
										"
										class="mb-5 justify-start">
										<CalendarIcon />
									</ReservationDetail>

									<ReservationDetail
										:name="$t('customer.reservation-count')"
										:value="reservationsCount ?? '---'"
										class="mb-5 justify-start">
										<CalendarIcon />
									</ReservationDetail>

									<ReservationDetail
										:name="$t('customer.reservation-amount')"
										:value="formatAmount(reservationsPriceSum)"
										class="mb-5 justify-start">
										<DiscountIcon class="!mr-2.5 !w-6" />
									</ReservationDetail>
								</div>
							</div>
						</div>

						<div class="w-full space-y-2 xl:w-1/3">
							<InputLabel :value="$t('tag.edit-tags')" />
							<Tagify
								:onChange="updateTags"
								:settings="{
									whitelist: availableTags.map(function (value, index) {
										return value['name'];
									}),
									maxTags: 15,
									dropdown: {
										maxItems: 20,
										classname: 'customer-tags',
										enabled: 0,
										closeOnSelect: true,
									},
								}"
								:value="
									customer.tags.map(function (value, index) {
										return value['name'];
									})
								"
								mode="text" />
						</div>
					</div>
				</Card>
			</div>
			<div class="w-full">
				<Card>
					<div class="mb-10 space-y-5 xl:hidden">
						<ExportDropdown v-model="exportType" class="w-full" />
						<GameFilter class="!w-full" table-name="reservations" />
						<TableSearch class="w-ful" table-name="reservations" />
					</div>
					<div class="block justify-between pb-10 xl:flex">
						<div class="flex w-full flex-wrap items-center space-x-8 md:w-1/2 md:flex-nowrap">
							<h1 class="text-[28px] font-medium">
								{{ $t('customer.reservation-list') }}
							</h1>
							<TableSearch class="hidden !w-80 xl:block" table-name="reservations" />
						</div>
						<div class="hidden space-x-4 xl:flex">
							<ExportDropdown v-model="exportType" />
							<GameFilter table-name="reservations" />
						</div>
					</div>
					<ReservationTable
						:game="game"
						:reservations="reservations"
						:table-headings="reservationTableHeadings"
						with-reservation-modals />
				</Card>
			</div>
		</div>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import { computed, Ref, ref } from 'vue';
import ClientDetail from '@/Components/Dashboard/Modals/Partials/ReservationView/ClientDetail.vue';
import IDIcon from '@/Components/Dashboard/Icons/IDIcon.vue';
import PhoneFilledIcon from '@/Components/Dashboard/Icons/PhoneFilledIcon.vue';
import EnvelopeIcon from '@/Components/Dashboard/Icons/EnvelopeIcon.vue';
import ReservationDetail from '@/Components/Dashboard/Modals/Partials/ReservationView/ReservationDetail.vue';
import CalendarIcon from '@/Components/Dashboard/Icons/CalendarIcon.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import Tagify from '@/Components/Dashboard/Tagify.vue';
import { Customer, Game, Reservation, Tag } from '@/Types/models';
import { useString } from '@/Composables/useString';
import { router, useForm, usePage } from '@inertiajs/vue3';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import GameFilter from '@/Components/Dashboard/GameFilter.vue';
import ReservationTable from '@/Components/Dashboard/ReservationTable.vue';
import { PaginatedResource } from '@/Types/responses';
import TableSearch from '@/Components/Dashboard/TableSearch.vue';
import { useQueryString } from '@/Composables/useQueryString';
import { useExport } from '@/Composables/useExport';
import ExportDropdown from '@/Components/Dashboard/ExportDropdown.vue';
import dayjs from 'dayjs';
import { useNumber } from '@/Composables/useNumber';
import DiscountIcon from '@/Components/Dashboard/Icons/DiscountIcon.vue';

const { capitalize } = useString();
const { queryValue } = useQueryString();
const { formatAmount } = useNumber();

const props = defineProps<{
  games: Game[];
	customer: Customer;
	availableTags: Tag[];
	reservations: PaginatedResource<Reservation>;
	reservationTableHeadings: Record<string, string>;
	latestReservation: Reservation;
	reservationsCount: number;
	reservationsPriceSum: number;
}>();

const exportType = useExport('club.customers.reservations.export', {
	customer: props.customer.id,
});

const game = computed<Game>(() => {
	return props.games.find(
		(game: Game) => game.id.toString() === queryValue(window.location.search, 'filters[reservations][game]'),
	);
});

const editCustomerFormStatus: Ref<boolean> = ref(false);
const form = useForm({
	first_name: props.customer.first_name,
	last_name: props.customer.last_name,
	email: props.customer.email,
	phone: props.customer.phone,
});

function submitForm(): void {
	form.put(route('club.customers.update', { customer: props.customer }), {
		preserveScroll: true,
		onSuccess: () => {
			editCustomerFormStatus.value = !editCustomerFormStatus.value;
		},
	});
}

function updateTags(e: any): void {
	router.put(
		route('club.customers.update-tags', { customer: props.customer }),
		{ tags: e.target?.value },
		{ preserveState: true, preserveScroll: true },
	);
}
</script>
