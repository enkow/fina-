<template>
	<PanelLayout :breadcrumbs="[{ href: route('club.customers.index'), label: $t('customer.plural') }]">
		<div class="flex w-full flex-wrap space-y-5 px-10 py-8">
			<div class="flex w-full items-center justify-between">
				<div class="flex flex-wrap">
					<div v-for="(name, index) in availableFilters">
						<SearchFilter
							v-if="availableFiltersTooltips[index]"
							v-tippy="{ allowHTML: true }"
							:content="'<p style=\'font-size:12px\'>' + availableFiltersTooltips[index] + '</p>'"
							:class="{
								active: getFilterStatus('group', index),
							}"
							class="mb-2 mr-3 capitalize"
							@click="toggleFilterValue('group', index)">
							{{ name }}
						</SearchFilter>
						<SearchFilter
							v-else
							v-tippy
							:content="availableFiltersTooltips[index]"
							:class="{
								active: getFilterStatus('group', index),
							}"
							class="mb-2 mr-3 capitalize"
							@click="toggleFilterValue('group', index)">
							{{ name }}
						</SearchFilter>
					</div>
				</div>
				<div class="space-y-2 sm:flex sm:space-x-6 sm:space-y-0">
					<ExportDropdown v-model="exportType" class="w-44" />
					<Button :href="route('club.customers.create')" class="w-44 !px-0" type="link">
						{{ $t('customer.add-new') }}
					</Button>
				</div>
			</div>
			<div class="w-full">
				<div class="mb-3 w-full sm:hidden">
					<TableSearch table-name="customers" />
				</div>
				<Card>
					<div class="flex items-center justify-between pb-8">
						<h1 class="text-[28px] font-medium">
							{{ $t('customer.reservation-list') }}
						</h1>
						<div class="hidden w-80 sm:block">
							<TableSearch table-name="customers" />
						</div>
					</div>
					<Table
						:header="customersTableHeadings"
						:items="customers"
						:sortable="['id', 'full_name', 'latest_reservation']"
						table-name="customers">
						<template #header_agreement_0>
							<div class="text-center text-xs">
								{{ $t('agreement.types.0') }}
							</div>
						</template>
						<template #header_agreement_1>
							<div class="text-center text-xs">
								{{ $t('agreement.types.1') }}
							</div>
						</template>
						<template #header_agreement_2>
							<div class="text-center text-xs">
								{{ $t('agreement.types.2') }}
							</div>
						</template>
						<template v-for="index in 3" :key="index - 1" v-slot:[`cell_agreement_${index-1}`]="props">
							<Link
								:href="
									route('club.customers.toggleConsent', {
										customer: props.item,
										agreementType: index - 1,
									})
								"
								as="link"
								class="flex w-full cursor-pointer items-center justify-center"
								method="post"
								preserve-scroll>
								<div
									class="item-center flex h-8 w-8 justify-center rounded bg-brand-base text-white"
									v-if="props.item.agreements.find((agreement) => agreement.type === index - 1)">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										viewBox="0 0 24 24"
										fill="currentColor"
										class="mt-1 h-6 w-6">
										<path
											fill-rule="evenodd"
											d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z"
											clip-rule="evenodd" />
									</svg>
								</div>
								<div
									class="flex h-8 w-8 items-center justify-center rounded bg-danger-base text-white"
									v-else>
									<svg
										xmlns="http://www.w3.org/2000/svg"
										viewBox="0 0 24 24"
										fill="currentColor"
										class="h-6 w-6">
										<path
											fill-rule="evenodd"
											d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z"
											clip-rule="evenodd" />
									</svg>
								</div>
							</Link>
						</template>
						<template #cell_name="props">
							{{ props?.item.full_name }}
						</template>
						<template #cell_latest_reservation="props">
							{{ props?.item.reservations_max_created_at }}
						</template>
						<template #cell_reservations_count="props">
							{{ props?.item.reservations_count }}
						</template>
						<template #cell_tags="props">
							<div class="flex flex-wrap">
								<Tag v-for="tag in props?.item.tags" class="my-1 mr-2 flex">
									<p>{{ tag.name }}</p>
									<XIcon
										class="ml-1 h-2 w-2 cursor-pointer"
										@click="
											router.delete(
												route('club.customers.detach-tag', {
													customer: props?.item.id,
													tag: tag.id,
												}),
												{ preserveState: true, preserveScroll: true },
											)
										" />
								</Tag>
							</div>
						</template>
						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="route('club.customers.show', { customer: props.item.id })"
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
												route('club.customers.destroy', {
													customer: props.item,
												}),
												{
													method: 'delete',
													preserveScroll: true,
													preserveState: true,
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
		</div>

		<DecisionModal
			:showing="confirmationDialogShowing"
			@confirm="confirmDialog()"
			@decline="cancelDialog()"
			@close="confirmationDialogShowing = false">
			{{ dialogContent }}
		</DecisionModal>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { ComputedRef, Ref, ref } from 'vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import Card from '@/Components/Dashboard/Card.vue';
import { Customer } from '@/Types/models';
import { wTrans } from 'laravel-vue-i18n';
import { useString } from '@/Composables/useString';
import Table from '@/Components/Dashboard/Table.vue';
import { PaginatedResource } from '@/Types/responses';
import Tag from '@/Components/Dashboard/Tag.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import { router, Link } from '@inertiajs/vue3';
import SearchFilter from '@/Components/Dashboard/SearchFilter.vue';
import { useFilters } from '@/Composables/useFilters';
import { useExport } from '@/Composables/useExport';
import ExportDropdown from '@/Components/Dashboard/ExportDropdown.vue';
import TableSearch from '@/Components/Dashboard/TableSearch.vue';
import SquareBadge from '@/Components/Dashboard/Icons/SquareBadge.vue';

const { capitalize } = useString();

const props = defineProps<{
	customers: PaginatedResource<Customer>;
	customersTableHeadings: Record<string, string>;
}>();

const exportType = useExport('club.customers.export', {});
const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const { filters, setFilters, toggleFilterValue, getFilterStatus } = useFilters();

const availableFilters: Ref<Record<string, ComputedRef<string>>> = ref({});
const availableFiltersTooltips: Ref<Record<string, ComputedRef<string>>> = ref({});
for (let i in [...Array(5).keys()]) {
	availableFilters.value[i.toString()] = wTrans(`customer.filter.${i}`);
	if (i !== '0') {
		availableFiltersTooltips.value[i.toString()] = wTrans(`customer.filter-tooltip.${i}`);
	}
}
setFilters('customers', [
	{
		key: 'group',
		items: Object.keys(availableFilters.value),
		active: ['0'],
		unique: true,
	},
]);
</script>
