<template>
	<PanelLayout :breadcrumbs="[{ href: '', label: $t('statistics.customer-ratings') }]">
		<ContentContainer>
			<Card class="col-span-12">
				<template #header>
					<div class="mb-5 block w-full space-y-3 md:hidden">
						<ExportDropdown v-model="exportType" class="!w-full" />
						<RangeDatepicker
							v-model="dateRange"
							:inputClasses="['opacity-60']"
							class="!w-full xs:mr-2 xs:w-64"
							input-with-icon />
					</div>
					<div class="flex justify-between">
						<div>
							<h2 class="mb-0">{{ $t('rate.average-customer-rates') }}</h2>
						</div>
						<div class="hidden md:flex">
							<RangeDatepicker
								v-model="dateRange"
								:inputClasses="['opacity-60']"
								class="w-full xs:mr-2 xs:w-64"
								input-with-icon />
							<ExportDropdown v-model="exportType" class="!w-60" />
						</div>
					</div>
				</template>
				<template #actions></template>
				<template #description>
					{{ $t('rate.average-customer-rates-description') }}
				</template>
				<div class="grid grid-cols-4 gap-x-4 gap-y-5">
					<RateContainer class="info col-span-4 sm:col-span-2 2xl:col-span-1">
						<template #icon>
							<SmileIcon class="h-9 w-9" />
						</template>
						<template #name>
							{{ $t('rate.service') }}
						</template>
						<template #value>
							{{ formattedFloat(averageRates.service ?? 0, 2) }}
						</template>
					</RateContainer>

					<RateContainer class="grey col-span-4 sm:col-span-2 2xl:col-span-1">
						<template #icon>
							<SofaIcon class="h-9 w-9" />
						</template>
						<template #name>
							{{ $t('rate.atmosphere') }}
						</template>
						<template #value>
							{{ formattedFloat(averageRates.atmosphere ?? 0, 2) }}
						</template>
					</RateContainer>

					<RateContainer class="warning col-span-4 sm:col-span-2 2xl:col-span-1">
						<template #icon>
							<ServiceIcon class="h-9 w-9" />
						</template>
						<template #name>
							{{ $t('rate.staff') }}
						</template>
						<template #value>
							{{ formattedFloat(averageRates.staff ?? 0, 2) }}
						</template>
					</RateContainer>

					<RateContainer class="brand col-span-4 sm:col-span-2 2xl:col-span-1">
						<template #icon>
							<RateIcon class="h-9 w-9" />
						</template>
						<template #name>
							{{ $t('rate.final') }}
						</template>
						<template #value>
							{{
								formattedFloat(
									(parseFloat(averageRates.service ?? 0) +
										parseFloat(averageRates.atmosphere ?? 0) +
										parseFloat(averageRates.staff ?? 0)) /
										3 ?? 0,
									2,
								)
							}}
						</template>
					</RateContainer>
				</div>
			</Card>
			<Card class="col-span-12 !px-0">
				<template #header>
					<div class="flex flex-wrap justify-between space-y-3 px-8 pb-10 md:flex-nowrap md:space-y-0">
						<div class="flex w-full items-center space-x-8 md:w-1/2">
							<h1 class="text-[28px] font-medium">
								{{ $t('rate.see-detailed-reviews') }}
							</h1>
						</div>
						<div class="flex w-full space-x-4 md:w-fit">
							<TableSearch class="!w-full md:!w-80" table-name="rates" />
							<TableColumnEditor
								:header="reservationsTableHeadings"
								:items="reservations"
								table-name="rates" />
						</div>
					</div>
				</template>

				<Table
					:header="reservationsTableHeadings"
					:items="reservations"
					:narrow="['start_date']"
					:sortable="[
						'game_name',
						'number',
						'start_datetime',
						'created_at',
						'rate_service',
						'rate_atmosphere',
						'rate_staff',
					]"
					table-name="rates">
					<template #header_rate_service="props">
						<SmileIcon class="h-5 w-5" />
					</template>

					<template #header_rate_atmosphere="props">
						<SofaIcon class="h-5 w-5" />
					</template>

					<template #header_rate_staff="props">
						<ServiceIcon class="h-5 w-5" />
					</template>

					<template #cell_start_datetime="props">
						{{ formatDate(props.item.start_datetime) }}
					</template>

					<template #header_rate_final="props">
						<RateIcon class="h-5 w-5" />
					</template>

					<template #cell_game_name="props">
						{{ props?.item.game_name }}
					</template>

					<template #cell_customer_name="props">
						{{ props?.item.customer_name }}
					</template>

					<template #cell_customer_phone="props">
						{{ props?.item.customer_phone }}
					</template>

					<template #cell_customer_email="props">
						{{ props?.item.customer_email }}
					</template>

					<template #cell_rate_service="props">
						{{ props?.item.rate_service }}
					</template>

					<template #cell_rate_atmosphere="props">
						{{ props?.item.rate_atmosphere }}
					</template>

					<template #cell_rate_staff="props">
						{{ props?.item.rate_staff }}
					</template>

					<template #cell_rate_final="props">
						{{ props?.item.rate_final }}
					</template>

					<template #cell_rate_content="props">
						<div style="max-width: 200px">
							<LimitedString
								:limit="80"
								:status="props?.item.rate_content_expanded ?? false"
								:string="props?.item.rate_content"
								@toggle="toggleRateContentExpandStatus(props?.item.number)" />
						</div>
					</template>
				</Table>
			</Card>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import RateContainer from '@/Components/Dashboard/RateContainer.vue';
import SmileIcon from '@/Components/Dashboard/Icons/SmileIcon.vue';
import SofaIcon from '@/Components/Dashboard/Icons/SofaIcon.vue';
import ServiceIcon from '@/Components/Dashboard/Icons/ServiceIcon.vue';
import RateIcon from '@/Components/Dashboard/Icons/RateIcon.vue';
import RangeDatepicker from '@/Components/Dashboard/RangeDatepicker.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { useNumber } from '@/Composables/useNumber';
import Table from '@/Components/Dashboard/Table.vue';
import { PaginatedResource } from '@/Types/responses';
import { Reservation } from '@/Types/models';
import { useString } from '@/Composables/useString';
import LimitedString from '@/Components/Dashboard/LimitedString.vue';
import { useExport } from '@/Composables/useExport';
import ExportDropdown from '@/Components/Dashboard/ExportDropdown.vue';
import TableSearch from '@/Components/Dashboard/TableSearch.vue';
import { useDateRangeFilter } from '@/Composables/useDateRangeFilter';
import TableColumnEditor from '@/Components/Dashboard/TableColumnEditor.vue';
import dayjs from 'dayjs';

const { formattedFloat } = useNumber();
const { capitalize } = useString();
const exportType = useExport('club.rates.export', {});
const { dateRange } = useDateRangeFilter('rates');

function formatDate(input: string): string {
	return dayjs(input).format('DD.MM.YYYY');
}

const props = defineProps<{
	averageRates: Object;
	reservations: PaginatedResource<Reservation>;
	reservationsTableHeadings: Record<string, string>;
}>();

props.reservations.data.forEach((reservation, key) => {
	props.reservations.data[key]['rate_content_expanded'] = false;
});

function toggleRateContentExpandStatus(reservationNumber: string): void {
	props.reservations.data.forEach((reservation: Reservation, key: any) => {
		if (reservation.number === reservationNumber) {
			props.reservations.data[key]['rate_content_expanded'] =
				!props.reservations.data[key]['rate_content_expanded'];
		}
	});
}
</script>
