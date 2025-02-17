import { reactive, watch } from 'vue';
import { useWidgetStore } from '@/Stores/widget';
import { wTrans } from 'laravel-vue-i18n';

export const useWidgetNotRequiredRegistrationForm = ({ onSuccess }: { onSuccess?: () => void } = {}) => {
	const widgetStore = useWidgetStore();
	const formErrors = reactive({
		firstName: '',
		lastName: '',
		email: '',
		phone: '',
	});

	const resetErrors = () => {
		formErrors.firstName = '';
		formErrors.lastName = '';
		formErrors.email = '';
		formErrors.phone = '';
	};

	const handleFormSubmit = () => {
		resetErrors();

		if (!widgetStore.form.customer.first_name) {
			formErrors.firstName = wTrans('widget.enter-valid-first-name').value;
		}

		if (!widgetStore.form.customer.last_name) {
			formErrors.lastName = wTrans('widget.enter-valid-last-name').value;
		}

		if (
			widgetStore.form.customer.phone?.length < 7 ||
			!/^[+0-9 \-]*$/.test(widgetStore.form.customer.phone)
		) {
			formErrors.phone = wTrans('widget.enter-valid-phone').value;
		}

		if (
			widgetStore.form.customer.email?.length < 6 ||
			!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(widgetStore.form.customer.email)
		) {
			formErrors.email = wTrans('widget.enter-valid-email').value;
		}

		if (Object.values(formErrors).every((value) => !value)) {
			onSuccess?.();
		}
	};

	watch(widgetStore.form, () => resetErrors());

	return { widgetStore, formErrors, handleFormSubmit };
};
