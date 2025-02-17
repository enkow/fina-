import { Club, Invoice } from '@/Types/models';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useQueryString } from '@/Composables/useQueryString';
import dayjs from 'dayjs';

export function useSettlements() {
	const { queryUrl, queryArray } = useQueryString();

	let qa = queryArray(window.location.search);
	let month = qa['filters[date]']?.split('-')[1] ?? new Date().getMonth();
	let year = qa['filters[date]']?.split('-')[0] ?? new Date().getFullYear();
	const dateFilterValue = ref({
		month: parseInt(month) - 1,
		year: parseInt(year),
	});

	watch(dateFilterValue, async () => {
		let dateParts = dateFilterValue.value.split('-');
		let currentQueryArray: Record<string, string> = queryArray(window.location.search);
		currentQueryArray['filters[date]'] = dateParts[0] + '-' + dateParts[1];
		router.visit(queryUrl(currentQueryArray), {
			preserveScroll: true,
		});
	});

	const calcClubAmount = (club: Club): number => {
		let amount = 0;
		club.invoices?.forEach((invoice) => {
			amount += calcAmount(invoice);
		});
		return amount;
	};

	const calcAmount = (invoice: Invoice) => {
		let amount = 0;

		invoice.items.forEach((item) => {
			const details = item.details;
			amount += parseInt(`${details?.offline?.price || 0}`);
			amount += parseInt(`${details?.online?.club?.price || 0}`);
			amount += parseInt(`${details?.online?.online?.price || 0}`);
			amount += parseInt(`${details?.online?.expired?.price || 0}`);

			amount -= parseInt(`${item.total || 0}`);
		});

		return amount;
	};

	const getInvoiceDateRange = (invoice: Invoice): string => {
		return dayjs(invoice?.from).format('YYYY-MM-DD') + ' - ' + dayjs(invoice?.to).format('YYYY-MM-DD');
	};

	return { calcAmount, calcClubAmount, dateFilterValue, getInvoiceDateRange };
}
