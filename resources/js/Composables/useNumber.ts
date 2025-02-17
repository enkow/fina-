import { usePage } from '@inertiajs/vue3';
import { User } from '@/Types/models';

export function useNumber() {
	const formattedFloat = (value: any, digits: number) => {
		return parseFloat(value).toFixed(digits);
	};

	function formatAmount(
		amount: number | string,
		currency: string | null = null,
		minimumFractionDigits = 2,
		maximumFractionDigits = 2,
	): string {
		return ((typeof amount === 'string' ? parseInt(amount) : amount) / 100).toLocaleString('pl-PL', {
			style: 'currency',
			currency: currency ?? (usePage().props.user as User)?.club?.country?.currency ?? 'PLN',
			minimumFractionDigits: minimumFractionDigits,
			maximumFractionDigits: maximumFractionDigits,
		});
	}

	return { formattedFloat, formatAmount };
}
