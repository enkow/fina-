<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('club.settlements.index'), label: $t('settlement.plural') },
			{
				href: route('club.settlements.show', { settlement: invoice.id }),
				label: $t('settlement.settlement-from-range', {
					from: invoice?.from,
					to: invoice?.to,
				}),
			},
			{
				href: route('club.settlements.reservations', {
					settlement: invoice.id,
					game: game,
				}),
				label: $t('reservation.plural') + ' - ' + usePage().props.gameNames[game.id],
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
							:table-headings="reservationsTableHeadings"
							:table-name-preference-postfix="game.id.toString()"
							with-reservation-modals
              :custom-table-preference="usePage().props.tablePreferences['settlement_reservations_'+props.game.id]"
            />
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
import { usePage } from '@inertiajs/vue3';
import {usePanelStore} from "@/Stores/panel";

const props = defineProps<{
	invoice: Invoice;
	reservations: PaginatedResource<Reservation>;
	game: Game;
	club: Club;
	reservationsTableHeadings: Record<string, string>;
}>();

const panelStore = usePanelStore();
</script>
