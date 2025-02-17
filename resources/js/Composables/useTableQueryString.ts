import { useQueryString } from '@/Composables/useQueryString';
import { router } from '@inertiajs/vue3';
import { Ref } from 'vue';

export function useTableQueryString() {
	const { queryArray, queryUrl } = useQueryString();
	const sortedStatus = (tableName: string, fieldName: string): string => {
		let params: any = queryArray(window.location.search);
		let value: string = params[`sorters[${tableName}][${fieldName}]`];
		return !value ? 'none' : params[`sorters[${tableName}][${fieldName}]`];
	};

	const sortedAddressWithToggledField = (tableName: string, fieldName: string): string => {
		let params: any = queryArray(window.location.search);

		const currentParamName = `sorters[${tableName}][${fieldName}]`;
		const currentParamValue = params[currentParamName];

		Object.keys(params).forEach((key) => {
			if (!key.startsWith('sorters') || key === currentParamName) {
				return;
			}
			delete params[key];
		});

		if (!currentParamValue) {
			params[currentParamName] = 'asc';
		} else if (currentParamValue === 'asc') {
			params[currentParamName] = 'desc';
		} else {
			delete params[currentParamName];
		}

		return queryUrl(params);
	};

	const paginatonLink = (link: string): string => {
		let currentParams: Record<string, string> = queryArray(window.location.search);
		let paginationParams: Record<string, string> = queryArray(link);

		for (let key in paginationParams) {
			if (paginationParams.hasOwnProperty(key)) {
				currentParams[key] = paginationParams[key];
			}
		}

		return queryUrl(currentParams);
	};

	const searchRedirect = (tableName: string, value: Ref<string>): void => {
		let params: Record<string, string> = queryArray(window.location.search);
		params[`searcher[${tableName}]`] = value.value;
		delete params['page'];
		router.visit(queryUrl(params), {
			preserveScroll: true,
		});
	};

	return {
		paginatonLink,
		sortedStatus,
		sortedAddressWithToggledField,
		searchRedirect,
	};
}
