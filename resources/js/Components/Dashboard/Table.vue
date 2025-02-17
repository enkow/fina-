<template>
	<div>
		<div class="relative overflow-x-auto">
			<table :class="['table w-full table-auto', tableClasses]">
				<thead>
					<tr>
						<th
							v-for="cell in columnList"
							v-show="cell.enabled"
							:class="{
								min: narrow && narrow.includes(cell.key),
								'whitespace-nowrap': nowrap?.includes(cell.key),
							}"
							:style="{
								width:
									columnsCustomWidths && columnsCustomWidths.hasOwnProperty(cell.key)
										? columnsCustomWidths[cell.key] + 'px'
										: 'unset',
							}">
							<div
								:class="{
									'!justify-center text-center': centered && centered.includes(cell.key),
								}"
								class="flex items-center justify-between space-x-1.5">
								<div>
									<slot
										:class="{
											min: narrow && narrow.includes(cell.key),
										}"
										:name="'header_' + cell.key">
										{{
											capitalize(
												Object.keys(header).includes(cell.key)
													? typeof header[cell.key] === 'string'
														? header[cell.key]
														: header[cell.key].value
													: cell.key,
											)
										}}
									</slot>
								</div>

								<div v-if="sortable?.includes(cell.key)" class="-mr-2">
									<Link
										v-if="sortedStatus(tableName, cell.key) === 'none'"
										:href="sortedAddressWithToggledField(tableName, cell.key)"
										preserve-scroll>
										<svg
											class="h-4 w-4"
											fill="none"
											stroke="currentColor"
											stroke-width="1.5"
											viewBox="0 0 24 24"
											xmlns="http://www.w3.org/2000/svg">
											<path
												d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"
												stroke-linecap="round"
												stroke-linejoin="round" />
										</svg>
									</Link>
									<Link
										v-if="sortedStatus(tableName, cell.key) === 'asc'"
										:href="sortedAddressWithToggledField(tableName, cell.key)"
										preserve-scroll>
										<svg
											class="h-4 w-4"
											fill="none"
											stroke="currentColor"
											stroke-width="1.5"
											viewBox="0 0 24 24"
											xmlns="http://www.w3.org/2000/svg">
											<path
												d="M8.25 6.75L12 3m0 0l3.75 3.75M12 3v18"
												stroke-linecap="round"
												stroke-linejoin="round" />
										</svg>
									</Link>
									<Link
										v-if="sortedStatus(tableName, cell.key) === 'desc'"
										:href="sortedAddressWithToggledField(tableName, cell.key)"
										preserve-scroll>
										<svg
											class="h-4 w-4"
											fill="none"
											stroke="currentColor"
											stroke-width="1.5"
											viewBox="0 0 24 24"
											xmlns="http://www.w3.org/2000/svg">
											<path
												d="M15.75 17.25L12 21m0 0l-3.75-3.75M12 21V3"
												stroke-linecap="round"
												stroke-linejoin="round" />
										</svg>
									</Link>
								</div>
							</div>
						</th>
						<th
							v-if="slots.cell_actions && (!disabled || !disabled.includes('actions'))"
							:class="{
								min: narrow?.includes('actions'),
							}">
							{{ capitalize($t('main.action.plural')) }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="item in items.data">
						<td
							v-for="cell in columnList"
							v-show="cell.enabled"
							:class="{
								'text-center': centered?.includes(cell.key),
								min: narrow?.includes(cell.key),
							}">
							<slot :item="item" :name="'cell_' + cell.key">
								{{ item[cell.key] }}
							</slot>
						</td>
						<td
							v-if="slots.cell_actions && (!disabled || !disabled.includes('actions'))"
							:class="{
								min: narrow?.includes('actions'),
							}">
							<slot :item="item" name="cell_actions" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<Pagination :meta="items.meta ?? items" :table-name="tableName" />
	</div>
</template>

<script lang="ts" setup>
import { computed, Ref, ref, useSlots } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import { useString } from '@/Composables/useString';
import { useTableQueryString } from '@/Composables/useTableQueryString';

const { capitalize } = useString();
const { sortedStatus, sortedAddressWithToggledField } = useTableQueryString();

const props = defineProps<{
	tableName?: string;
	tableClasses?: string;
	tableNamePreferencePostfix?: string;
	disabled?: Array<string>;
	sortable?: Array<string>;
	centered?: Array<string>;
	narrow?: Array<string>;
	nowrap?: Array<string>;
	header: Object;
	items: Object;
	columnsCustomWidths?: Record<string, number>;
	customTablePreference?: Object[];
}>();

const tablePreferenceName: string =
	props.tableName + (props.tableNamePreferencePostfix ? '_' + props.tableNamePreferencePostfix : '');

const columnList: Ref<Array<Object>> = computed<Object[]>(() => {
	let tablePreference =
		props.customTablePreference ??
		usePage().props.tablePreferences[tablePreferenceName.replace('canceledR', 'r')];

	let result = [];
	for (var index in tablePreference) {
		let item: Object = tablePreference[index];
		if (!props.disabled || !props.disabled.includes(item.key)) {
			result.push({
				key: item.key,
				name: Object.keys(props.header).includes(item.key) ? props.header[item.key] : item.key,
				enabled: item.enabled,
			});
		}
	}
	return result;
});

const slots = useSlots();
</script>

<style scoped>
table {
	@apply border-b border-b-gray-2;

	th,
	td {
		@apply border-r border-r-gray-2 px-3 py-3;

		&.min {
			width: 1%;
			white-space: nowrap;
		}
	}

	td:last-child,
	th:last-child {
		@apply border-r-0;
	}

	thead {
		th {
			@apply text-left text-sm font-semibold;
		}
	}

	tbody {
		tr {
			@apply border-t border-t-gray-2 text-sm;
		}

		td {
			@apply font-light;
		}
	}
}
</style>
