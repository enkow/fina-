import { useWidgetStore } from '@/Stores/widget';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { signIn } from '@/Services/auth';

export const useWidgetSignInForm = () => {
	const widgetStore = useWidgetStore();
	const form = useForm({
		email: '',
		password: '',
	});
	const invalidDataErrorMessage = ref<string | null>(null);

	const handleFormSubmit = async () => {
		const { email, password } = form;

		invalidDataErrorMessage.value = null;

		await signIn(
			{ email, password, widgetChannel: widgetStore.props.channel as string, club: widgetStore.club },
			{
				onSuccess: (customer) => {
					widgetStore.customer = customer;
				},
				onInvalidData: (message) => {
					invalidDataErrorMessage.value = message;
				},
				onError: (errors) => {
					Object.entries(errors).forEach(([key, value]) => {
						form.setError(key as keyof typeof form.errors, value[0]);
					});
				},
			},
		);
	};

	watch(
		() => [form.email, form.password],
		() => {
			form.clearErrors();
		},
	);

	return { form, invalidDataErrorMessage, handleFormSubmit };
};
