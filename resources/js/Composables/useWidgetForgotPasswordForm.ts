import { remindPassword } from '@/Services/auth';
import { useForm } from '@inertiajs/vue3';
import { useWidgetStore } from '@/Stores/widget';
import { ref, watch } from 'vue';

export const useWidgetForgotPasswordForm = () => {
	const success = ref(false);
	const form = useForm({
		email: '',
	});
	const widgetStore = useWidgetStore();

	watch(
		() => form.email,
		() => form.clearErrors(),
	);

	const handleFormSubmit = async () => {
		const { email } = form;

		await remindPassword(
			{ email, widgetChannel: widgetStore.props.channel as string, club: widgetStore.club },
			{
				onSuccess: () => {
					success.value = true;
					form.reset();
				},
				onError: (errors) => {
					Object.entries(errors).forEach(([key, value]) => {
						form.setError(key as keyof typeof form.errors, value[0]);
					});
				},
			},
		);
	};

	return { success, form, handleFormSubmit };
};
