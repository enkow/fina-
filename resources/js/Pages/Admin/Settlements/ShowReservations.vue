<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.settlements.index'), label: $t('settlement.plural') },
			{ href: route('admin.settlements.show-club', { club: club.id }), label: club.name },
			{
				href: route('admin.settlements.show', { club: club.id, settlement: invoice.id }),
				label: $t('settlement.settlement-from-range', {
					from: invoice?.from,
					to: invoice?.to,
				}),
			},
			{
				href: route('admin.settlements.club-reservations', {
					settlement: invoice.id,
					club: club.id,
					game: game.id,
				}),
				label: 'Rezerwacje',
			},
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<div class="flex justify-between">
						<div class="flex w-full items-center space-x-8 md:w-1/2">
							<h1 class="text-[28px] font-medium">
								{{ $t('reservation.reservations-list') }}
							</h1>
						</div>
					</div>
					<div class="pt-10">
						<ReservationTable
							:reservations="reservations"
							:game="game"
							:tableHeadings="reservationsTableHeadings"
							table-name="settlement_reservations"
							:table-name-preference-postfix="game.id.toString()"
							disabled-actions />
					</div>
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { Reservation, Invoice, Game, Club } from '@/Types/models';
import { PaginatedResource } from '@/Types/responses';
import Card from '@/Components/Dashboard/Card.vue';
import ReservationTable from '@/Components/Dashboard/ReservationTable.vue';

const props = defineProps<{
	invoice: Invoice;
	reservations: PaginatedResource<Reservation>;
	game: Game;
	club: Club;
	reservationsTableHeadings: Record<string, string>;
}>();
</script>
