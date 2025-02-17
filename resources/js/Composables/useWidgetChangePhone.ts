import axios from 'axios';
import { useWidgetStore } from '@/Stores/widget';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useWidgetPhoneFormatting } from './useWidgetPhoneFormatting';

export const useWidgetChangePhone = (success = () => {}) => {
	const widgetStore = useWidgetStore();
	const { phone, formatPhone } = useWidgetPhoneFormatting();

	const changePhoneNumber = async (): Promise<boolean> => {
		const { data } = await axios
			.put(
				route('widget.customers.update-phone', {
					club: widgetStore.club,
				}),
				{
					phone: form.phone,
					email: form.email,
					password: form.password,
				},
			)
			.catch((error) => error.response);

		if (data?.errors) {
			Object.entries(data?.errors).forEach(([key, value]) => {
				form.setError(key as keyof typeof form.errors, value[0]);
			});
		} else {
			if (widgetStore.customer) {
				widgetStore.customer.phone = form.phone;
				widgetStore.resendCode = true;
			}
		}

		return data.status;
	};

	watch(phone, () => {
		form.phone = phone.value;
	});

	const form = useForm({
		email: '',
		password: '',
		phone: '',
	});

	return {
		form,
		phone,
		formatPhone,
		changePhoneNumber,
	};
};
