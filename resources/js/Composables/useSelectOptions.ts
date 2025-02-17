import { computed, Ref, ref } from 'vue';
import { wTrans } from 'laravel-vue-i18n';
import { useString } from '@/Composables/useString';
import dayjs from 'dayjs';
import { usePage } from '@inertiajs/vue3';
import { PaymentMethod, SelectOption } from '@/Types/models';

export function useSelectOptions() {
	const { capitalize, pad } = useString();
	const gameOptions = (): Array<SelectOption> => {
		let result: Array<SelectOption> = [];
		// @ts-ignore
		usePage().props.user.club.games.forEach((game) => {
			// @ts-ignore
			result.push({ code: game.id, label: usePage().props.gameNames[game.id] });
		});
		return result;
	};

	const discountCodeTypeOptions = ref<SelectOption[]>([
		{ code: 0, label: wTrans('discount-code.types.0') },
		{ code: 1, label: wTrans('discount-code.types.1') },
	]);

	const paymentTypeOptions = ref<SelectOption[]>([
		{ code: 1, label: 'Online' },
		{ code: 2, label: wTrans('statistics.on-the-spot') },
		{ code: 3, label: wTrans('statistics.online-and-on-the-spot') },
	]);

	const statusOptions = computed<SelectOption[]>(() => {
		return [
			{
				code: JSON.stringify({ status: 0, payment_method_id: null }),
				label: capitalize(wTrans('reservation.statuses.0').value),
			},
			{
				code: JSON.stringify({ status: 1, payment_method_id: 1 }),
				label: capitalize(wTrans('reservation.status.paid-cash').value),
			},
			{
				code: JSON.stringify({ status: 1, payment_method_id: 2 }),
				label: capitalize(wTrans('reservation.status.paid-cashless').value),
			},
			{
				code: JSON.stringify({ status: 1, payment_method_id: 3 }),
				label: capitalize(wTrans('reservation.status.paid-card').value),
			},
		];
	});

	const yesNoOptions: Ref<Array<SelectOption>> = ref([
		{ code: false, label: wTrans('main.no') },
		{ code: true, label: wTrans('main.yes') },
	]);

	const reservationCancellationReasons: Ref<Array<SelectOption>> = ref([]);
	for (let i in [...Array(5).keys()]) {
		reservationCancellationReasons.value.push({
			code: i,
			label: wTrans(`reservation.cancelation-status.${i}`),
		});
	}

	const modelOptions = (
		models: Array<any>,
		idFieldName: string,
		labelFieldName: string,
		withNull: boolean = false,
	): Array<SelectOption> => {
		let result: Array<SelectOption> = [];
		if (withNull) {
			result.push({ code: null, label: '‎ ' });
		}
		models?.forEach((model) => {
			result.push({ code: model[idFieldName], label: model[labelFieldName] });
		});
		return result;
	};

	const reservationStatusOptions = (): Array<SelectOption> => {
		let result: Array<SelectOption> = [];
		[1, 2].forEach((status) => {
			result.push({
				code: status,
				label: wTrans(`reservation.statuses.${status}`),
			});
		});
		return result;
	};

	const employeeTypeOptions: Ref<Array<SelectOption>> = ref([
		{ code: 'employee', label: wTrans('employee.type.employee') },
		{ code: 'manager', label: wTrans('employee.type.manager') },
	]);

	const timeOptions = (block: number): Array<SelectOption> => {
		let result: Array<SelectOption> = [];
		for (let i = 0; i <= 23; i++) {
			let control: number = 0;
			while (control < 60) {
				result.push({
					code: pad(i.toString(), 2) + ':' + pad(control.toString(), 2),
					label: pad(i.toString(), 2) + ':' + pad(control.toString(), 2),
				});
				control += block;
			}
		}
		result.push({ code: '24:00', label: '24:00' });
		return result;
	};

	const numberOptions = (min: number, max: number): SelectOption[] => {
		let result: SelectOption[] = [];
		for (let i = min; i <= max; i++) {
			result.push({
				code: i,
				label: i.toString(),
			});
		}
		return result;
	};

	const fromOptions: Ref<Array<SelectOption>> = ref([]);
	const toOptions: Ref<Array<SelectOption>> = ref([]);
	for (let i = 0; i <= 24; i++) {
		fromOptions.value.push({
			code: pad(i.toString(), 2) + ':00',
			label: wTrans('main.from-value', { value: `${i}:00` }),
		});
		toOptions.value.push({
			code: pad(i.toString(), 2) + ':00',
			label: wTrans('main.to-value', { value: `${i}:00` }),
		});
	}
	const fromOptionsHalfHourly: Ref<Array<SelectOption>> = ref([]);
	const toOptionsHalfHourly: Ref<Array<SelectOption>> = ref([]);
	for (let i = 0; i <= 24; i++) {
		fromOptionsHalfHourly.value.push({
			code: pad(i.toString(), 2) + ':00',
			label: wTrans('main.from-value', { value: `${i}:00` }),
		});
		toOptionsHalfHourly.value.push({
			code: pad(i.toString(), 2) + ':00',
			label: wTrans('main.to-value', { value: `${i}:00` }),
		});
		if(i !== 24) {
			fromOptionsHalfHourly.value.push({
				code: pad(i.toString(), 2) + ':30',
				label: wTrans('main.from-value', { value: `${i}:30` }),
			});
			toOptionsHalfHourly.value.push({
				code: pad(i.toString(), 2) + ':30',
				label: wTrans('main.to-value', { value: `${i}:30` }),
			});
		}
	}

	const yearOptions: Array<SelectOption> = [];
	for (let i = dayjs().year() - 2; i < dayjs().year() + 5; i++) {
		yearOptions.push({
			code: i.toString(),
			label: capitalize(wTrans('main.year').value) + ' ' + i.toString(),
		});
	}

	const monthOptions = ref<SelectOption[]>([]);
	[...Array(12).keys()].forEach((month) => {
		monthOptions.value.push({
			code: month,
			label: wTrans(`main.month-name.${month + 1}`),
		});
	});

	const localeOptions = (locales: any): Array<SelectOption> => {
		let result: Array<SelectOption> = [];
		Object.keys(locales).forEach(function (key) {
			result.push({ code: locales[key], label: locales[key] });
		});
		return result;
	};

	const timezoneOptions = (timezones: any): Array<SelectOption> => {
		let result: Array<SelectOption> = [];
		Object.keys(timezones).forEach(function (key) {
			result.push({ code: key, label: timezones[key] });
		});
		return result;
	};

	const currencyOptions: Array<SelectOption> = [
		{ code: 'AED', label: 'AED' },
		{ code: 'AFN', label: 'AFN' },
		{ code: 'ALL', label: 'ALL' },
		{ code: 'AMD', label: 'AMD' },
		{ code: 'ANG', label: 'ANG' },
		{ code: 'AOA', label: 'AOA' },
		{ code: 'ARS', label: 'ARS' },
		{ code: 'AUD', label: 'AUD' },
		{ code: 'AWG', label: 'AWG' },
		{ code: 'AZN', label: 'AZN' },
		{ code: 'BAM', label: 'BAM' },
		{ code: 'BBD', label: 'BBD' },
		{ code: 'BDT', label: 'BDT' },
		{ code: 'BGN', label: 'BGN' },
		{ code: 'BHD', label: 'BHD' },
		{ code: 'BIF', label: 'BIF' },
		{ code: 'BMD', label: 'BMD' },
		{ code: 'BND', label: 'BND' },
		{ code: 'BOB', label: 'BOB' },
		{ code: 'BRL', label: 'BRL' },
		{ code: 'BSD', label: 'BSD' },
		{ code: 'BTN', label: 'BTN' },
		{ code: 'BWP', label: 'BWP' },
		{ code: 'BYN', label: 'BYN' },
		{ code: 'BYR', label: 'BYR' },
		{ code: 'BZD', label: 'BZD' },
		{ code: 'CAD', label: 'CAD' },
		{ code: 'CDF', label: 'CDF' },
		{ code: 'CHF', label: 'CHF' },
		{ code: 'CLF', label: 'CLF' },
		{ code: 'CLP', label: 'CLP' },
		{ code: 'CNY', label: 'CNY' },
		{ code: 'COP', label: 'COP' },
		{ code: 'CRC', label: 'CRC' },
		{ code: 'CUC', label: 'CUC' },
		{ code: 'CUP', label: 'CUP' },
		{ code: 'CVE', label: 'CVE' },
		{ code: 'CZK', label: 'CZK' },
		{ code: 'DJF', label: 'DJF' },
		{ code: 'DKK', label: 'DKK' },
		{ code: 'DOP', label: 'DOP' },
		{ code: 'DZD', label: 'DZD' },
		{ code: 'EGP', label: 'EGP' },
		{ code: 'ERN', label: 'ERN' },
		{ code: 'ETB', label: 'ETB' },
		{ code: 'EUR', label: 'EUR' },
		{ code: 'FJD', label: 'FJD' },
		{ code: 'FKP', label: 'FKP' },
		{ code: 'GBP', label: 'GBP' },
		{ code: 'GEL', label: 'GEL' },
		{ code: 'GHS', label: 'GHS' },
		{ code: 'GIP', label: 'GIP' },
		{ code: 'GMD', label: 'GMD' },
		{ code: 'GNF', label: 'GNF' },
		{ code: 'GTQ', label: 'GTQ' },
		{ code: 'GYD', label: 'GYD' },
		{ code: 'HKD', label: 'HKD' },
		{ code: 'HNL', label: 'HNL' },
		{ code: 'HRK', label: 'HRK' },
		{ code: 'HTG', label: 'HTG' },
		{ code: 'HUF', label: 'HUF' },
		{ code: 'IDR', label: 'IDR' },
		{ code: 'ILS', label: 'ILS' },
		{ code: 'INR', label: 'INR' },
		{ code: 'IQD', label: 'IQD' },
		{ code: 'IRR', label: 'IRR' },
		{ code: 'ISK', label: 'ISK' },
		{ code: 'JMD', label: 'JMD' },
		{ code: 'JOD', label: 'JOD' },
		{ code: 'JPY', label: 'JPY' },
		{ code: 'KES', label: 'KES' },
		{ code: 'KGS', label: 'KGS' },
		{ code: 'KHR', label: 'KHR' },
		{ code: 'KMF', label: 'KMF' },
		{ code: 'KPW', label: 'KPW' },
		{ code: 'KRW', label: 'KRW' },
		{ code: 'KWD', label: 'KWD' },
		{ code: 'KYD', label: 'KYD' },
		{ code: 'KZT', label: 'KZT' },
		{ code: 'LAK', label: 'LAK' },
		{ code: 'LBP', label: 'LBP' },
		{ code: 'LKR', label: 'LKR' },
		{ code: 'LRD', label: 'LRD' },
		{ code: 'LSL', label: 'LSL' },
		{ code: 'LYD', label: 'LYD' },
		{ code: 'MAD', label: 'MAD' },
		{ code: 'MDL', label: 'MDL' },
		{ code: 'MGA', label: 'MGA' },
		{ code: 'MKD', label: 'MKD' },
		{ code: 'MMK', label: 'MMK' },
		{ code: 'MNT', label: 'MNT' },
		{ code: 'MOP', label: 'MOP' },
		{ code: 'MRO', label: 'MRO' },
		{ code: 'MUR', label: 'MUR' },
		{ code: 'MVR', label: 'MVR' },
		{ code: 'MWK', label: 'MWK' },
		{ code: 'MXN', label: 'MXN' },
		{ code: 'MXV', label: 'MXV' },
		{ code: 'MYR', label: 'MYR' },
		{ code: 'MZN', label: 'MZN' },
		{ code: 'NAD', label: 'NAD' },
		{ code: 'NGN', label: 'NGN' },
		{ code: 'NIO', label: 'NIO' },
		{ code: 'NOK', label: 'NOK' },
		{ code: 'NPR', label: 'NPR' },
		{ code: 'NZD', label: 'NZD' },
		{ code: 'OMR', label: 'OMR' },
		{ code: 'PAB', label: 'PAB' },
		{ code: 'PEN', label: 'PEN' },
		{ code: 'PGK', label: 'PGK' },
		{ code: 'PHP', label: 'PHP' },
		{ code: 'PKR', label: 'PKR' },
		{ code: 'PLN', label: 'PLN' },
		{ code: 'PYG', label: 'PYG' },
		{ code: 'QAR', label: 'QAR' },
		{ code: 'RON', label: 'RON' },
		{ code: 'RSD', label: 'RSD' },
		{ code: 'RUB', label: 'RUB' },
		{ code: 'RWF', label: 'RWF' },
		{ code: 'SAR', label: 'SAR' },
		{ code: 'SBD', label: 'SBD' },
		{ code: 'SCR', label: 'SCR' },
		{ code: 'SDG', label: 'SDG' },
		{ code: 'SEK', label: 'SEK' },
		{ code: 'SGD', label: 'SGD' },
		{ code: 'SHP', label: 'SHP' },
		{ code: 'SLL', label: 'SLL' },
		{ code: 'SOS', label: 'SOS' },
		{ code: 'SRD', label: 'SRD' },
		{ code: 'SSP', label: 'SSP' },
		{ code: 'STD', label: 'STD' },
		{ code: 'SVC', label: 'SVC' },
		{ code: 'SYP', label: 'SYP' },
		{ code: 'SZL', label: 'SZL' },
		{ code: 'THB', label: 'THB' },
		{ code: 'TJS', label: 'TJS' },
		{ code: 'TMT', label: 'TMT' },
		{ code: 'TND', label: 'TND' },
		{ code: 'TOP', label: 'TOP' },
		{ code: 'TRY', label: 'TRY' },
		{ code: 'TTD', label: 'TTD' },
		{ code: 'TWD', label: 'TWD' },
		{ code: 'TZS', label: 'TZS' },
		{ code: 'UAH', label: 'UAH' },
		{ code: 'UGX', label: 'UGX' },
		{ code: 'USD', label: 'USD' },
		{ code: 'UYI', label: 'UYI' },
		{ code: 'UYU', label: 'UYU' },
		{ code: 'UZS', label: 'UZS' },
		{ code: 'VEF', label: 'VEF' },
		{ code: 'VND', label: 'VND' },
		{ code: 'VUV', label: 'VUV' },
		{ code: 'WST', label: 'WST' },
		{ code: 'XAF', label: 'XAF' },
		{ code: 'XCD', label: 'XCD' },
		{ code: 'XOF', label: 'XOF' },
		{ code: 'XPF', label: 'XPF' },
		{ code: 'XXX', label: 'XXX' },
		{ code: 'YER', label: 'YER' },
		{ code: 'ZAR', label: 'ZAR' },
		{ code: 'ZMW', label: 'ZMW' },
		{ code: 'ZWL', label: 'ZWL' },
	];

	const downloadOptions: Array<SelectOption> = [
		{ code: 'pdf', label: 'PDF' },
		{ code: 'csv', label: 'CSV' },
	];

	const onlinePaymentMethodOptions: Array<SelectOption> = [];
	// @ts-ignore
	usePage().props.paymentMethods?.forEach((paymentMethod: PaymentMethod) => {
		if (paymentMethod.online) {
			onlinePaymentMethodOptions.push({
				code: paymentMethod.type,
				label: paymentMethod.code,
			});
		}
	});

	const countryOptions: Ref<Array<SelectOption>> = ref([]);
	// @ts-ignore
	if (usePage().props.activeCountries) {
		// @ts-ignore
		for (const [id, code] of Object.entries(usePage().props.activeCountries)) {
			countryOptions.value.push({
				code: parseInt(id),
				label: wTrans('country.' + code),
			});
		}
	}

	return {
		gameOptions,
		discountCodeTypeOptions,
		yesNoOptions,
		modelOptions,
		reservationStatusOptions,
		employeeTypeOptions,
		yearOptions,
		monthOptions,
		fromOptions,
		toOptions,
		fromOptionsHalfHourly,
		toOptionsHalfHourly,
		localeOptions,
		timezoneOptions,
		currencyOptions,
		paymentTypeOptions,
		statusOptions,
		onlinePaymentMethodOptions,
		countryOptions,
		timeOptions,
		numberOptions,
		downloadOptions,
		reservationCancellationReasons,
	};
}
