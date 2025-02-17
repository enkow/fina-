<template>
	<PanelLayout :breadcrumbs="[{ href: route('admin.settlements.index'), label: $t('settlement.plural') }]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-12">
				<Card>
					<div class="flex items-center justify-between pb-8">
						<h1 class="text-[28px] font-medium">
							{{ $t('settlement.plural') }}
						</h1>
						<div class="hidden sm:flex">
							<SimpleDatepicker
								v-model="dateFilterValue"
								class="!h-12"
								format="yyyy-MM"
								month-picker
								input-with-icon />
						</div>
					</div>
					<Table
						:header="{
							id: '#',
							club_name: 'Nazwa klubu',
							amount: 'Rozliczenie',
						}"
						:items="clubs"
						table-name="admin_settlements_clubs"
						:narrow="['id', 'actions']">
						<template #cell_club_name="props">
							{{ props.item.name }}
						</template>
						<template #cell_amount="props">
							<p
								:class="{
									'text-danger-base': calcClubAmount(props.item) < 0,
									'text-brand-base': calcClubAmount(props.item) > 0,
								}"
								class="font-bold">
								{{ formattedFloat(calcClubAmount(props.item) / 100, 2) }}
								{{ currencySymbols[props.item.country?.currency || 'PLN'] }}
							</p>
						</template>
						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="route('admin.settlements.show-club', { club: props.item.id })"
									class="info-light xs uppercase"
									type="link">
									<EyeIcon class="-mx-0.5 -mt-0.5" />
								</Button>
							</div>
						</template>
					</Table>
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import { Club } from '@/Types/models';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Table from '@/Components/Dashboard/Table.vue';
import { PaginatedResource } from '@/Types/responses';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import EyeIcon from '@/Components/Widget-3/Icons/EyeIcon.vue';
import { useReservations } from '@/Composables/useReservations';
import { useNumber } from '@/Composables/useNumber';
import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';
import { useQueryString } from '@/Composables/useQueryString';
import { useSettlements } from '@/Composables/useSettlements';
const { formattedFloat } = useNumber();
const { queryUrl, queryArray } = useQueryString();

const { currencySymbols } = useReservations();

const { calcClubAmount, dateFilterValue } = useSettlements();

const props = defineProps<{
	clubs: PaginatedResource<Club>;
}>();
</script>
