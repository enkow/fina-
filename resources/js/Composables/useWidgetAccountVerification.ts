import axios from 'axios';
import { ref } from 'vue';
import { useWidgetStore } from '@/Stores/widget';
import { Customer } from '@/Types/models';
import { useForm } from '@inertiajs/vue3';

export const useWidgetAccountVerificationMail = () => {
	const verificationLinkResend = ref(false);
	const widgetStore = useWidgetStore();

	const handleResendButtonClick = async () => {
		await axios.get(route('widget.customers.verification-resend', { club: widgetStore.club }));

		verificationLinkResend.value = true;
	};

	widgetStore.channel.bind('customer-verified', async ({ customer }: { customer: Customer }) => {
		if (customer.encryptedId) {
			const { data } = await axios.get<{ customer: Customer }>(
				route('widget.customers.show', { club: widgetStore.club, encryptedCustomerId: customer.encryptedId }),
			);

			widgetStore.customer = data.customer;
			verificationLinkResend.value = true;
		} else {
			widgetStore.customer = customer;
			verificationLinkResend.value = true;
		}
	});

	return { verificationLinkResend, handleResendButtonClick };
};

export const useWidgetAccountVerificationSms = (success = () => {}) => {
	const widgetStore = useWidgetStore();

	const handleSendVerificationCode = async () => {
		const { data } = await axios
			.put(
				route('widget.customers.sms-verification-code', {
					club: widgetStore.club,
					encryptedCustomerId: widgetStore.customer?.encryptedId,
				}),
				{ code: form.code },
			)
			.catch((error) => error.response);

		if (data.errors?.code) {
			form.errors.code = data.message || data.errors.code;
		} else {
			form.errors.code = '';
			widgetStore.customer = data.customer;
			success();
		}
	};
	const verificationResendError = ref<boolean>(false);
	const verificationResendText = ref<string>('');

	function resendVerificationCode() {
		axios
			.get(
				route('widget.customers.verification-resend', {
					club: widgetStore.club,
					encryptedCustomerId: widgetStore.customer?.encryptedId,
				}),
			)
			.then((response) => {
				verificationResendError.value = response.data.error;
				verificationResendText.value = response.data.message;
				setTimeout(() => {
					verificationResendError.value = false;
					verificationResendText.value = '';
				},5000);
			});
	}

	const form = useForm({
		code: '',
	});

	return {
		form,
		handleSendVerificationCode,
		resendVerificationCode,
		verificationResendError,
		verificationResendText,
	};
};
