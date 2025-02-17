<template>
	<div>
		<div
			v-if="!forgotPasswordEmailSentStatus"
			class="login-container text-widget flex h-[300px] w-full flex-col items-center justify-center rounded-md border border-[3px]">
			<p class="widget-header">{{ $t('widget.forgot-password') }}</p>
			<p
				class="mb-5 w-11/12 text-xs font-bold uppercase sm:w-[480px]"
				v-html="$t('widget.forgot-password-content')"></p>
			<form class="flex flex-col items-center" @submit.prevent="submitForgotPasswordForm">
				<div class="widget-input-group flex flex-col items-center">
					<TextInput v-model="form.email" :placeholder="$t('widget.email')" class="widget-input" />
					<div v-if="form.errors['email']" class="widget-error">
						{{ $t(form.errors['email'], { attribute: $t('validation.attributes.email') }) }}
					</div>
				</div>
				<button class="widget-button mt-3 flex justify-center" type="submit">
					{{ $t('widget.send-link') }}
				</button>
				<p class="mt-3 w-full cursor-pointer text-center text-xs font-bold" @click="emit('close')">
					&lt; {{ $t('widget.go-back-to-login') }}
				</p>
			</form>
		</div>
		<div v-else class="w-full">
			<div class="mx-auto mb-6 mt-10 w-100 text-center">
				{{ $t('widget.forgot-password-success-content') }}
			</div>
			<p class="mt-3 w-full cursor-pointer text-center text-xs font-bold" @click="emit('close')">
				&lt; {{ $t('widget.go-back-to-login') }}
			</p>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';
import { useWidgetStore } from '@/Stores/widget';
import TextInput from '@/Components/Widget-3/TextInput.vue';
import { ref } from 'vue';
import axios from 'axios';

const emit = defineEmits<{
	(e: 'close'): void;
}>();

const widgetStore = useWidgetStore();
const forgotPasswordEmailSentStatus = ref<boolean>(false);

const form = useForm({
	email: null,
	widget_channel: widgetStore.props.channel,
});

function submitForgotPasswordForm(): void {
	axios
		.post(route('widget.customers.forgot-password', { club: widgetStore.club }), form)
		.then((response: any) => {
			if (response.data.result) {
				forgotPasswordEmailSentStatus.value = true;
			}
		})
		.catch((response: { response: { data: { errors: Record<string, any> } } }) => {
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
