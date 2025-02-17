import { Ref, ref, watch } from 'vue';
import { useQueryString } from '@/Composables/useQueryString';
import dayjs, { Dayjs } from 'dayjs';
import { router } from '@inertiajs/vue3';

export function useDateRangeFilter(tableName: string | null) {
	const { queryArray } = useQueryString();
	let queryKey: string = 'filters';
	if (tableName) {
		queryKey += `[${tableName}]`;
	}
	queryKey += '[startRange]';
	let startRangeQueryKey = queryKey + '[from]';
	let endRangeQueryKey = queryKey + '[to]';

	const dateRange: Ref<Array<Dayjs>> = ref([
		dayjs(queryArray(window.location.search)[startRangeQueryKey], 'YYYY-MM-DD'),
		dayjs(queryArray(window.location.search)[endRangeQueryKey], 'YYYY-MM-DD'),
	]);

	watch(dateRange, async (newDateRange) => {
		let array: {
			[key: string]: string;
		} = queryArray(window.location.search);
		array[startRangeQueryKey] = dayjs(newDateRange[0]).format('YYYY-MM-DD');
		array[endRangeQueryKey] = dayjs(newDateRange[1]).format('YYYY-MM-DD');
		router.visit(window.location.href.split('?')[0], {
			data: array,
			preserveScroll: true,
		});
	});

	return { dateRange };
}
