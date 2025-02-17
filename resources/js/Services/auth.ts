import { wTrans } from 'laravel-vue-i18n';
import { Club, Customer } from '@/Types/models';
import axios, { isAxiosError } from 'axios';

interface ApiError {
	readonly errors: Record<string, string[]>;
}

const isApiError = (data: unknown): data is ApiError =>
	!!data && typeof data === 'object' && 'errors' in data;

export const signIn = async (
	{
		email,
		password,
		widgetChannel,
		club,
	}: { email: string; password: string; widgetChannel: string; club: Club },
	{
		onSuccess,
		onInvalidData,
		onError,
	}: {
		onSuccess?: (customer: Customer) => void;
		onInvalidData?: (message: string) => void;
		onError?: (errors: Record<string, string[]>) => void;
	} = {},
) => {
	try {
		const { data } = await axios.post<{ customer?: Customer }>(route('widget.customers.login', { club }), {
			email,
			password,
			widget_channel: widgetChannel,
			club_slug: club.slug,
		});

		if (data.customer) {
			onSuccess?.(data.customer);
		} else {
			onInvalidData?.(wTrans('widget.login-data-invalid').value);
		}
	} catch (err) {
		if (isAxiosError(err) && err.response && isApiError(err.response.data)) {
			onError?.(err.response.data?.errors);
		}
	}
};

export const signUp = async (
	{
		firstName,
		lastName,
		email,
		phone,
		password,
		widgetChannel,
		generalTerms,
		privacyPolicy,
		marketingAgreement,
		club,
	}: {
		firstName: string;
		lastName: string;
		email: string;
		phone: string;
		password: string;
		widgetChannel: string;
		generalTerms: boolean;
		privacyPolicy: boolean;
		marketingAgreement: boolean;
		club: Club;
	},
	{
		onSuccess,
		onError,
	}: {
		onSuccess?: (customer: Customer) => void;
		onError?: (errors: Record<string, string[]>) => void;
	} = {},
) => {
	try {
		const { data } = await axios.post<{ customer?: Customer }>(route('widget.customers.register', { club }), {
			first_name: firstName,
			last_name: lastName,
			email,
			phone,
			password,
			consents: {
				general_terms: generalTerms,
				privacy_policy: privacyPolicy,
				marketing_agreement: marketingAgreement,
			},
			club_slug: club.slug,
			widget_channel: widgetChannel,
		});

		if (data.customer) {
			onSuccess?.(data.customer);
		}
	} catch (err) {
		if (isAxiosError(err) && err.response && isApiError(err.response.data)) {
			onError?.(err.response.data.errors);
		}
	}
};

export const verificationByCode = async (
	{
		code,
		club,
	}: {
		code: string;
		club: Club;
	},
	{
		onSuccess,
		onError,
	}: {
		onSuccess?: (customer: Customer) => void;
		onError?: (errors: Record<string, string[]>) => void;
	} = {},
) => {
	try {
		const { data } = await axios.post(route('widget.customers.verification.sms', { club }), {
			code: code,
		});

		if (data.customer) {
			onSuccess?.(data.customer);
		}
	} catch (err) {
		if (isAxiosError(err) && err.response && isApiError(err.response.data)) {
			onError?.(err.response.data.errors);
		}
	}
};

export const signOut = async ({ club }: { club: Club }, { onSuccess }: { onSuccess?: () => void } = {}) => {
	await axios.post(route('widget.customers.logout', { club }));
	onSuccess?.();
};

export const remindPassword = async (
	{ email, widgetChannel, club }: { email: string; widgetChannel: string; club: Club },
	{
		onSuccess,
		onError,
	}: {
		onSuccess?: () => void;
		onError?: (errors: Record<string, string[]>) => void;
	} = {},
) => {
	try {
		const { data } = await axios.post(route('widget.customers.forgot-password', { club }), {
			email,
			widget_channel: widgetChannel,
		});

		if (data.result) {
			onSuccess?.();
		}
	} catch (err) {
		if (isAxiosError(err) && err.response && isApiError(err.response.data)) {
			onError?.(err.response.data?.errors);
		}
	}
};
