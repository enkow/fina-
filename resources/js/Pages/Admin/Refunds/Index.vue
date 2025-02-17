<template>
	<PanelLayout :breadcrumbs="[{ href: route('admin.invoices.index'), label: 'Zwroty' }]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>Zwroty</h2>
					</template>

					<Table
						:centered="['actions']"
						:header="{
							id: 'ID Zwrotu',
							created_at: 'Anulowano',
							club_name: 'Klub',
							reservation_number: 'Numer rezerwacji',
							price: 'Cena',
						}"
						:items="refunds"
						table-name="admin_refunds">
						<template #cell_reservation_number="props">
							<p
								class="mr-1 inline-block cursor-pointer"
								v-for="(reservationNumber, index) in props.item.reservation_numbers"
								@click="openReservationShowModal(reservationNumber)">
								{{ pad(reservationNumber.id, 5)
								}}{{ props.item.reservation_numbers.length - 1 === index ? '' : ', ' }}
							</p>
						</template>
						<template #cell_id="props">#{{ props.item.id }}</template>
						<template #cell_price="props">
							{{ formatAmount(props.item.price, props.item.club.country.currency) }}
						</template>
						<template #cell_club_name="props">
							<Link :href="route('admin.clubs.edit', { club: props.item.club })">
								{{ props.item.club.name }}
							</Link>
						</template>
						<template #cell_actions="props">
							<div v-if="props.item.club.online_payments_enabled === 'internal'">
								<p v-if="props.item.status === 1">
									<button
										v-tippy="{ allowHTML: true }"
										:content="`${props.item.approved_at}<br />${props.item.approver.email}`">
										Admin - Zwrócono
									</button>
								</p>
								<p v-else-if="props.item.status === 2">Admin - Brak zwrotu</p>
								<Button
									v-else-if="props.item.status === 0"
									:href="route('admin.refunds.approve', { refund: props.item })"
									class="sm info !px-3 text-center"
									type="link">
									Admin - Potwierdź
								</Button>
							</div>
							<div v-else>
								<p v-if="props.item.status === 1">
									<button
										v-tippy="{ allowHTML: true }"
										:content="`${props.item.approved_at}<br />${props.item.approver.email}`">
										Klub - Zwrócono
									</button>
								</p>
								<p v-else-if="props.item.status === 2">Klub - Brak zwrotu</p>
								<p v-else-if="props.item.status === 0">Klub - oczekujący</p>
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
import { Link } from '@inertiajs/vue3';
import ReservationShowModal from '@/Components/Dashboard/Modals/ReservationShowModal.vue';
import { ref, Ref } from 'vue';
import { useModal } from '@/Composables/useModal';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

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
