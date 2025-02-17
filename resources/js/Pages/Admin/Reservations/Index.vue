<template>
	<PanelLayout :breadcrumbs="[{ href: route('admin.reservations.index'), label: 'Rezerwacje' }]">
		<ContentContainer>
			<div class="col-span-6">
				<RangeDatepicker v-model="dateRange" :inputClasses="['opacity-60']" input-with-icon position="left" />
			</div>
			<div class="col-span-6 space-y-2">
				<div class="flex justify-end">
					<SearchFilter
						v-for="(name, index) in cancelerFilters"
						:class="{
							active: getFilterStatus('cancelationType', index),
						}"
						class="mr-3 !w-48 capitalize"
						@click="toggleFilterValue('cancelationType', index)">
						{{ name }}
					</SearchFilter>
				</div>
				<div class="flex justify-end">
					<SearchFilter
						v-for="(name, index) in paymentStatusFilters"
						:class="{
							active: getFilterStatus('paymentStatus', index),
						}"
						class="mr-3 capitalize"
						@click="toggleFilterValue('paymentStatus', index)">
						{{ name }}
					</SearchFilter>
				</div>
				<div class="flex justify-end">
					<SearchFilter
						v-for="(name, index) in paymentTypeFilters"
						:class="{
							active: getFilterStatus('paymentType', index),
						}"
						class="mr-3 capitalize"
						@click="toggleFilterValue('paymentType', index)">
						{{ name }}
					</SearchFilter>
				</div>
			</div>
			<div class="col-span-12">
				<Card>
					<template #header>
						<div class="mb-5 flex items-center justify-between">
							<div>
								<h2>Rezerwacje</h2>
							</div>
							<div class="flex space-x-3">
								<ExportDropdown v-model="exportType" />
								<TableSearch class="!w-80" table-name="reservations" />
								<GameFilter :games="games" table-name="reservations" />
							</div>
						</div>
					</template>

					<ReservationTable
						:reservations="reservations"
						:table-headings="reservationsTableHeadings"
						with-reservation-modals />
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { Game, Reservation } from '@/Types/models';
import { PaginatedResource } from '@/Types/responses';
import Card from '@/Components/Dashboard/Card.vue';
import SearchFilter from '@/Components/Dashboard/SearchFilter.vue';
import GameFilter from '@/Components/Dashboard/GameFilter.vue';
import { ref, Ref } from 'vue';
import { useFilters } from '@/Composables/useFilters';
import { useExport } from '@/Composables/useExport';
import ReservationTable from '@/Components/Dashboard/ReservationTable.vue';
import ExportDropdown from '@/Components/Dashboard/ExportDropdown.vue';
import TableSearch from '@/Components/Dashboard/TableSearch.vue';
import RangeDatepicker from '@/Components/Dashboard/RangeDatepicker.vue';
import { useDateRangeFilter } from '@/Composables/useDateRangeFilter';

const props = defineProps<{
	reservations: PaginatedResource<Reservation>;
	reservationsTableHeadings: Record<string, string>;
	games: Game[];
}>();

const { filters, setFilters, toggleFilterValue, getFilterStatus } = useFilters();
const { dateRange } = useDateRangeFilter('reservations');

const cancelerFilters: Ref<Record<number, string>> = ref({
	0: 'Wszystkie',
	1: 'Anulowane przez klienta',
	2: 'Anulowane przez klub',
	3: 'Anulowane przez system',
});

const paymentStatusFilters: Ref<Record<number, string>> = ref({
	0: 'Wszystkie',
	1: 'Nieopłacone',
	2: 'Opłacone',
	3: 'Klubu',
	4: 'Oczekująca',
});

const paymentTypeFilters: Ref<Record<number, string>> = ref({
	0: 'Wszystkie',
	1: 'Offline',
	2: 'Online',
});

setFilters('reservations', [
	{
		key: 'cancelationType',
		items: Object.keys(cancelerFilters.value),
		active: ['0'],
	},
	{
		key: 'paymentStatus',
		items: Object.keys(paymentStatusFilters.value),
		active: ['0'],
	},
	{
		key: 'paymentType',
		items: Object.keys(paymentTypeFilters.value),
		active: ['0'],
		unique: true,
	},
]);
const exportType = useExport('admin.reservations.export', {});
</script>
