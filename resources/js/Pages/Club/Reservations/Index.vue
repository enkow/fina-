<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.reservations.index'),
				label: $t('reservation.reservation-search'),
			},
		]">
		<div class="flex w-full flex-wrap space-y-5 px-3 py-8 xl:px-10">
			<div class="w-full space-y-3 md:hidden">
				<ExportDropdown class="w-full" />
				<RangeDatepicker v-model="dateRange" :inputClasses="['opacity-60']" input-with-icon position="left" />
				<GameFilter class="!w-full" custom-route="club.games.statistics.main" table-name="reservations" />
				<TableSearch class="max-h-12" table-name="reservations" />
			</div>
			<div class="hidden w-full justify-between md:flex">
				<div class="flex space-x-3">
					<RangeDatepicker
						v-model="dateRange"
						:inputClasses="['opacity-60']"
						class="!w-[300px] 2xl:!w-[480px]"
						input-with-icon
						position="left" />
					<TableSearch class="max-h-12" table-name="reservations" />
				</div>
				<div class="flex space-x-5">
					<GameFilter custom-route="club.games.statistics.main" table-name="reservations" />
				</div>
			</div>
			<div class="w-full">
				<Card>
					<div class="flex justify-between pb-10">
						<div class="flex w-full items-center space-x-8 md:w-1/2">
							<h1 class="text-[28px] font-medium">
								{{ $t('reservation.reservations-found') }}
							</h1>
						</div>
						<div class="hidden space-x-4 md:flex">
							<ExportDropdown v-model="exportType" />
							<ReservationTableColumnEditor
								:game="game"
								:reservation-table-headings="reservationTableHeadings"
								:reservations="reservations" />
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
import { watch } from 'vue';
import RangeDatepicker from '@/Components/Dashboard/RangeDatepicker.vue';
import { useString } from '@/Composables/useString';
import GameFilter from '@/Components/Dashboard/GameFilter.vue';
import { Game, Reservation } from '@/Types/models';
import { useExport } from '@/Composables/useExport';
import TableSearch from '@/Components/Dashboard/TableSearch.vue';
import { useQueryString } from '@/Composables/useQueryString';
import ReservationTable from '@/Components/Dashboard/ReservationTable.vue';
import { PaginatedResource } from '@/Types/responses';
import { router } from '@inertiajs/vue3';
import ExportDropdown from '@/Components/Dashboard/ExportDropdown.vue';
import { useDateRangeFilter } from '@/Composables/useDateRangeFilter';
import ReservationTableColumnEditor from '@/Components/Dashboard/ReservationTableColumnEditor.vue';

const props = defineProps<{
	game: Game;
	reservations: PaginatedResource<Reservation>;
	reservationTableHeadings: Record<string, string>;
}>();

const { capitalize } = useString();
const { queryValue, queryArray } = useQueryString();

const { dateRange } = useDateRangeFilter('reservations');
const exportType = useExport('club.reservations.export', {});

watch(dateRange, async (newDateRange) => {
	let array: Record<string, string> = queryArray(window.location.search);
	array[`filters[reservations][startRange][from]`] = newDateRange[0].toISOString().split('T')[0];
	array[`filters[reservations][startRange][to]`] = newDateRange[1].toISOString().split('T')[0];
	router.visit(route('club.reservations.index'), {
		data: array,
		preserveScroll: true,
		preserveState: true,
	});
});
</script>
