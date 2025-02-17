<template>
	<PanelLayout :breadcrumbs="[{ href: route('admin.refunds.index'), label: $t('refund.plural') }]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<div class="mb-3 flex w-full justify-between">
							<h2>{{ $t('refund.plural') }}</h2>
							<TableSearch table-name="refunds" class="h-11 max-w-[400px]" />
						</div>
					</template>

					<Table
						table-classes="xl:overflow-x-hidden"
						:centered="['actions']"
						:header="{
							id: $t('refund.refund-id'),
							created_at: $t('refund.canceled'),
							customer_name: $t('refund.customer-name-table-heading'),
							cancelation_type: $t('refund.cancelation-type'),
							refundable_id: $t('reservation.reservation-numbers'),
							price: capitalize($t('main.price')),
						}"
						:items="refunds"
						:sortable="['id', 'created_at', 'refundable_id', 'price', 'status']"
						table-name="refunds">
						<template #cell_refundable_id="props">
							<p
								class="mr-1 inline-block cursor-pointer"
								v-for="(reservationNumber, index) in props.item.reservation_numbers"
								@click="openReservationShowModal(reservationNumber)">
								{{ pad(reservationNumber.id, 5)
								}}{{ props.item.reservation_numbers.length - 1 === index ? '' : ', ' }}
							</p>
						</template>
						<template #cell_id="props">#{{ props.item.id }}</template>
						<template #cell_cancelation_type="props">
							{{ props.item.cancelation_type === 2 ? 'Klub' : 'Klient' }}
						</template>
						<template #cell_price="props">
							{{ formatAmount(props.item.price, props.item.club.country.currency) }}
						</template>
						<template #cell_customer_name="props">
							<Link
								v-if="props.item.customer"
								:href="
									route('club.customers.show', {
										customer: props.item.customer.id,
									})
								">
								{{ (props.item.customer.first_name ?? '') + ' ' + (props.item.customer.last_name ?? '') }}
							</Link>
							<p v-else>---</p>
						</template>
						<template #cell_status="props">
							<p v-if="props.item.status === 1">
								<button
									v-tippy="{ allowHTML: true }"
									:content="`<p style='text-align:center'>${props.item.approved_at}<br />${$t(
										'refund.approved-by-manager',
									)}:<br />${props.item.approver.email}</p>`">
									{{ $t('refund.refunded') }}
								</button>
							</p>
							<p v-else-if="props.item.status === 2">
								{{ $t('refund.no-refund') }}
							</p>
							<div v-else-if="props.item.status === 0">
								<Button
									v-if="usePage().props.user.type === 'manager'"
									:href="route('club.refunds.approve', { refund: props.item })"
									class="sm info !px-3 text-center"
									type="link">
									{{ capitalize($t('main.action.confirm')) }}
								</Button>
								<p v-else>
									{{ $t('refund.pending') }}
								</p>
							</div>
						</template>
					</Table>
				</Card>
			</div>
		</ContentContainer>

		<ReservationShowModal
			:readonly="true"
			:reservation-number="reservationShowModalNumber"
			:showing="reservationShowModalShowing"
			@close-modal="closeReservationShowModal"
			@show-reservation-by-reservation-number="openReservationShowModal" />
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
import Table from '@/Components/Dashboard/Table.vue';
import { PaginatedResource } from '@/Types/responses';
import { useString } from '@/Composables/useString';
import { Refund } from '@/Types/models';
import { useNumber } from '@/Composables/useNumber';
import { Link, usePage } from '@inertiajs/vue3';
import ReservationShowModal from '@/Components/Dashboard/Modals/ReservationShowModal.vue';
import { capitalize, ref, Ref } from 'vue';
import { useModal } from '@/Composables/useModal';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import TableSearch from '@/Components/Dashboard/TableSearch.vue';

const props = defineProps<{
	refunds: PaginatedResource<Refund>;
}>();

const { pad } = useString();
const { formatAmount } = useNumber();

const reservationShowModalShowing = ref<boolean>(false);
useModal(reservationShowModalShowing);

const reservationShowModalNumber = ref<string | undefined>('0');

function openReservationShowModal(reservationNumber: string): void {
	reservationShowModalNumber.value = reservationNumber;
	reservationShowModalShowing.value = true;
}

function closeReservationShowModal(): void {
	reservationShowModalNumber.value = '0';
	reservationShowModalShowing.value = false;
}
</script>
