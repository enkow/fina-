<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.settlements.index'), label: $t('settlement.plural') },
			{ href: route('admin.settlements.show-club', { club: club.id }), label: club.name },
		]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-12">
				<Card>
					<div class="flex items-center justify-between pb-8">
						<h1 class="text-[28px] font-medium">
							{{ $t('settlement.plural') }}
						</h1>
						<div class="hidden w-80 sm:block">
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
							date: $t('invoice.duration'),
							amount: $t('main.sum'),
						}"
						:items="invoices"
						table-name="settlements"
						:narrow="['id', 'actions']">
						<template #cell_date="props">
							{{ getInvoiceDateRange(props.item) }}
						</template>
						<template #cell_amount="props">
							<p
								:class="{
									'text-danger-base': calcAmount(props.item) < 0,
									'text-brand-base': calcAmount(props.item) > 0,
								}"
								class="font-bold">
								{{ formattedFloat(calcAmount(props.item) / 100, 2) }}
								{{ currencySymbols[club?.country?.currency || 'PLN'] }}
							</p>
						</template>
						<template #cell_actions="props">
							<div class="flex items-center space-x-2">
								<Button
									:href="route('admin.settlements.show', { settlement: props.item.id, club: club.id })"
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
import { Club, Invoice } from '@/Types/models';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Table from '@/Components/Dashboard/Table.vue';
import { PaginatedResource } from '@/Types/responses';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import EyeIcon from '@/Components/Widget-3/Icons/EyeIcon.vue';
import { useReservations } from '@/Composables/useReservations';
import { useNumber } from '@/Composables/useNumber';
import { useSettlements } from '@/Composables/useSettlements';
import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';
const { formattedFloat } = useNumber();

const { currencySymbols } = useReservations();
const { calcAmount, dateFilterValue, getInvoiceDateRange } = useSettlements();

const props = defineProps<{
	club: Club;
	invoices: PaginatedResource<Invoice>;
}>();
</script>
