<template>
	<form class="text-widget flex flex-col items-center pb-3 pt-2.5" @submit.prevent="submitLoginForm">
		<p class="widget-header hidden md:block">
			{{ $t('widget.already-have-account') }}
		</p>
		<p class="widget-header block md:hidden">
			{{ $t('widget.login') }}
		</p>
		<div class="widget-input-group">
			<div class="flex">
				<TextInput v-model="form.email" :placeholder="$t('widget.email')" class="widget-input" />
				<div
					v-if="form.errors['email']"
					v-tippy
					:content="$t(form.errors['email'], { attribute: $t('validation.attributes.email'), max: '100' })"
					class="mt-1.25">
					<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
				</div>
			</div>
		</div>
		<div class="widget-input-group">
			<div class="flex">
				<TextInput
					v-model="form.password"
					:placeholder="$t('widget.password')"
					autocomplete="new-password"
					class="widget-input"
					type="password" />
				<div
					v-if="form.errors['password']"
					v-tippy
					:content="
						$t(form.errors['password'], { attribute: $t('validation.attributes.password'), max: '100' })
					"
					class="mt-1.25">
					<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
				</div>
				<div v-else-if="formDataInvalid" v-tippy :content="$t('widget.login-data-invalid')" class="mt-1.25">
					<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
				</div>
			</div>
		</div>
		<p class="mt-1 cursor-pointer text-xxs font-medium uppercase" @click="emit('forgotPassword')">
			{{ $t('widget.forgot-password') }}
		</p>
		<div class="mt-5 flex flex-col items-center gap-y-0.5">
			<button class="widget-button" type="submit">
				<p class="w-full">{{ $t('widget.login') }}</p>
			</button>
			<p class="widget-header !mb-1 !mt-10 block w-full text-center !text-base md:hidden">
				{{ $t('widget.do-not-have-account-question') }}
			</p>
			<button class="widget-button sm:!hidden" @click.prevent="emit('showRegister')">
				<p class="w-full">{{ $t('widget.i-do-not-have-an-account') }}</p>
			</button>
		</div>
	</form>
</template>

<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';
import { useWidgetStore } from '@/Stores/widget';
import { ref } from 'vue';
import axios from 'axios';
import { Customer } from '@/Types/models';
import TextInput from '@/Components/Widget-3/TextInput.vue';
import FacebookIcon from '@/Components/Widget-3/Icons/FacebookIcon.vue';
import { loadLanguageAsync } from 'laravel-vue-i18n';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';

const widgetStore = useWidgetStore();
const formDataInvalid = ref<boolean>(false);

const emit = defineEmits<{
	(e: 'forgotPassword'): void;
	(e: 'reloadCustomer'): void;
	(e: 'showRegister'): void;
}>();

const form = useForm({
	club_slug: widgetStore.club.slug,
	email: '',
	password: '',
	widget_channel: widgetStore.props.channel,
});

function submitLoginForm(): void {
	axios
		.post(route('widget.customers.login', { club: widgetStore.club }), form)
		.then((response: { data: { customer: Customer } }) => {
			if (response.data?.customer ?? null) {
				widgetStore.customer = response.data.customer;
				widgetStore.customerSmsLimitReached = response.data.sms_limit_reached;
				formDataInvalid.value = false;
				form.reset();
			} else {
				form.errors = {};
				formDataInvalid.value = true;
			}
		})
		.catch((response: { response: { data: { errors: Object } } }) => {
			form.errors = {} as Record<string, any>;
			let errors: Record<string, any> = response.response.data.errors;
			Object.keys(errors).forEach((errorKey: string) => {
				errors[errorKey].forEach((error: string) => {
					form.errors[errorKey as keyof typeof form.errors] = error;
				});
			});
		});
}
</script>
