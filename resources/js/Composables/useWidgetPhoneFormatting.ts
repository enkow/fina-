import { nextTick, ref } from 'vue';

export const useWidgetPhoneFormatting = () => {
	const phone = ref<string>('');

	function formatPhone(): string {
		nextTick(() => {
			let digits = phone.value.replace(/[^\d]/g, '');
			phone.value = digits.substring(0, 30).replace(/(.{3})(?=.)/g, '$1 ');
		});
		return phone.value;
	}

	function showPhone(number: string): string {
		let digits = number.replace(/[^\d]/g, '');
		let firstDigits = digits.substring(0, 6);
		let restDigits = digits.substring(6);
		let hassed = firstDigits.replace(/./g, '*') + restDigits.replace(/(.{4})(?=.)/g, '$1 ');
		const formatted = hassed.substring(0, 9).replace(/(.{3})(?=.)/g, '$1 ');
		return formatted;
	}

	return { phone, formatPhone, showPhone };
};
