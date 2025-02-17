<template>
	<PanelLayout :breadcrumbs="[{ href: route('club.customers.index'), label: 'Klienci' }]">
		<ContentContainer>
			<div class="col-span-12">
				<div class="flex w-full items-center justify-between">
					<div class="flex flex-wrap">
						<SearchFilter
							v-for="(name, index) in customerTypeFilters"
							:class="{
								active: getFilterStatus('type', index.toString()),
							}"
							class="mb-2 mr-3 capitalize"
							@click="toggleFilterValue('type', index.toString())">
							{{ name }}
						</SearchFilter>
					</div>
					<div class="mb-2">
						<ExportDropdown v-model="exportType" />
					</div>
				</div>
				<Card>
					<template #header>
						<div class="mb-5 flex items-center justify-between">
							<h2>Lista klientów</h2>
							<TableSearch class="max-w-104" table-name="admin_customers" />
						</div>
					</template>

					<Table
						:disabled="['last_name']"
						:header="customersTableHeadings"
						:items="customers"
						:narrow="['id']"
						:sortable="['id', 'full_name', 'reservations_count']"
						table-name="admin_customers">
						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="route('admin.customers.edit', { customer: props.item.id })"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									as="button"
									class="danger-light xs uppercase"
									preserve-scroll
									type="button"
									@click="
										showDialog(capitalize($t('main.are-you-sure')), () => {
											router.visit(
												route('admin.customers.destroy', {
													customer: props.item,
												}),
												{
													method: 'delete',
													preserveState: true,
													preserveScroll: true,
												},
											);
										})
									">
									<TrashIcon class="-mx-0.5 -mt-0.5" />
								</Button>
							</div>
						</template>
					</Table>
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>

	<DecisionModal
		:showing="confirmationDialogShowing"
		@confirm="confirmDialog()"
		@decline="cancelDialog()"
		@close="confirmationDialogShowing = false">
		{{ dialogContent }}
	</DecisionModal>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { Customer } from '@/Types/models';
import { PaginatedResource } from '@/Types/responses';
import Card from '@/Components/Dashboard/Card.vue';
import Table from '@/Components/Dashboard/Table.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import TableSearch from '@/Components/Dashboard/TableSearch.vue';
import { router } from '@inertiajs/vue3';
import SearchFilter from '@/Components/Dashboard/SearchFilter.vue';
import { useFilters } from '@/Composables/useFilters';
import { Ref, ref } from 'vue';
import ExportDropdown from '@/Components/Dashboard/ExportDropdown.vue';
import { useExport } from '@/Composables/useExport';
import { useString } from '@/Composables/useString';

const props = defineProps<{
	customers: PaginatedResource<Customer>;
	customersTableHeadings: Record<string, string>;
}>();

const { filters, setFilters, toggleFilterValue, getFilterStatus } = useFilters();
const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const { capitalize } = useString();

const exportType = useExport('admin.customers.export', {});

const customerTypeFilters: Ref<Record<number, string>> = ref({
	0: 'Wszyscy',
	1: 'Online',
	2: 'Offline',
});

setFilters('admin_customers', [
	{
		key: 'type',
		items: Object.keys(customerTypeFilters.value).map(String),
		active: ['0'],
		unique: true,
	},
]);
</script>
