import { useForm } from '@inertiajs/vue3';
import { useWidgetStore } from '@/Stores/widget';
import { signUp } from '@/Services/auth';
import { watch, computed, ref } from 'vue';
import { useWidgetPhoneFormatting } from '@/Composables/useWidgetPhoneFormatting';

export const useWidgetSignUpForm = () => {
	const widgetStore = useWidgetStore();

	const { phone, formatPhone } = useWidgetPhoneFormatting();

	const form = useForm({
		first_name: '',
		last_name: '',
		email: '',
		password: '',
		general_terms: false,
		privacy_policy: false,
		marketing_agreement: false,
		phone: ''
	});

	const consents = ref({
		general_terms: false,
		marketing_agreement: false,
	});

	const consentsErrors = computed(() => {
		return {
			'consents.general_terms': (form.errors as Record<string, string>)['consents.general_terms'],
			'consents.privacy_policy': (form.errors as Record<string, string>)['consents.privacy_policy'],
			'consents.marketing_agreement': (form.errors as Record<string, string>)['consents.marketing_agreement'],
		}
	})

	const handleFormSubmit = async () => {
		const {
			first_name,
			last_name,
			email,
			password,
		} = form;

		await signUp(
			{
				firstName: first_name,
				lastName: last_name,
				email,
				phone: phone.value,
				password,
				widgetChannel: widgetStore.props.channel as string,
				generalTerms: consents.value.general_terms,
				privacyPolicy: consents.value.general_terms,
				marketingAgreement: consents.value.marketing_agreement,
				club: widgetStore.club,
			},
			{
				onSuccess: (customer) => {
					widgetStore.customer = customer;

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

	watch(
		() => [
			form.first_name,
			form.last_name,
			form.email,
			form.password,
			form.general_terms,
			form.privacy_policy,
			form.marketing_agreement,
			phone.value,
			consents.value
		],
		() => form.clearErrors(),
	);

	return { form, handleFormSubmit, phone, formatPhone, consentsErrors, consents };
};
