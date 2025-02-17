import { Ref, ref } from 'vue';
import { useQueryString } from '@/Composables/useQueryString';
import { router } from '@inertiajs/vue3';

export function useFilters() {
	const filters: Ref<Record<string, any>> = ref({});
	const { queryArray } = useQueryString();

	interface Filter {
		key?: string;
		items: Array<string>;
		unique?: boolean;
		canBeEmpty?: boolean;
		active?: Array<string>;
		firstOrOthers?: boolean;
	}

	function setFilters(tableName: string, filterArray: Array<Filter>) {
		filterArray.forEach((filter) => {
			let currentQueryArray = queryArray(window.location.search);
			let filterData = {
				tableName: tableName,
				items: filter['items'],
				unique: false,
				canBeEmpty: false,
				active: [],
				firstOrOthers: true,
			};
			filterData['unique'] = !filter['unique'] ? false : filter['unique'];
			filterData['canBeEmpty'] = !filter['canBeEmpty'] ? false : filter['canBeEmpty'];
			// @ts-ignore
			filterData['active'] = currentQueryArray[`filters[${tableName}][${filter.key}]`]
				? currentQueryArray[`filters[${tableName}][${filter.key}]`].split(',').map(String)
				: filter['active'] ?? [];
			// @ts-ignore
			filterData['firstOrOthers'] = !filter['firstOrOthers'] ? true : filter['firstOrOthers'];
			// @ts-ignore
			filters.value[filter.key] = filterData;
		});
	}

	function toggleFilterValue(filterKey: string, filterValue: any) {
		if (!filters.value[filterKey].items.includes(filterValue)) {
			return false;
		}
		if (filters.value[filterKey].active.includes(filterValue)) {
			if (filters.value[filterKey].canBeEmpty === false && filters.value[filterKey].active.length === 1) {
				filters.value[filterKey].active = [filters.value[filterKey].items[0]];
			} else {
				filters.value[filterKey].active = filters.value[filterKey].active.filter(function (e: Event) {
					return e !== filterValue;
				});
			}
		} else {
			if (filters.value[filterKey].unique === true) {
				filters.value[filterKey].active = [filterValue];
			} else {
				if (filters.value[filterKey].firstOrOthers === true) {
					if (filterValue === filters.value[filterKey].items[0]) {
						filters.value[filterKey].active = [];
					} else {
						let indexToRemove = filters.value[filterKey].active.indexOf(filters.value[filterKey].items[0]);
						if (indexToRemove !== -1) {
							filters.value[filterKey].active.splice(indexToRemove, 1);
						}
					}
				}
				filters.value[filterKey].active.push(filterValue);
			}
		}
		updateQueryString();
	}

	function getFilterStatus(filterKey: string, filterValue: string | number) {
		return filters.value?.[filterKey]?.active.includes(filterValue) ?? false;
	}

	function updateQueryString() {
		const { queryArray, queryUrl } = useQueryString();
		let currentQueryArray = queryArray(window.location.search);
		Object.keys(filters.value).forEach(function (key) {
			currentQueryArray[`filters[${filters.value[key].tableName}][${key}]`] = filters.value[key].active;
		});
		delete currentQueryArray['page'];
		router.visit(queryUrl(currentQueryArray), {
			preserveScroll: true,
			preserveState: true,
		});
	}

	return { filters, setFilters, toggleFilterValue, getFilterStatus };
}
