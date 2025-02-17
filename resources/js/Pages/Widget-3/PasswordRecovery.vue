<template>
	<Widget3Layout :without-navigation="true" :without-logout="true">
		<div v-if="!passwordRecoveredStatus" class="flex h-100 w-full flex-col items-center justify-center">
			<form class="flex h-100 w-full flex-col items-center justify-center" @submit.prevent="submitForm">
				<p class="widget-header">{{ $t('widget.set-new-password') }}</p>
				<div class="widget-input-group mb-5 mt-8">
					<TextInput
						v-model="form.password"
						:placeholder="$t('widget.password')"
						autocomplete="new-password"
						class="widget-input"
						type="password" />
					<div v-if="form.errors['password']" class="widget-error">
						{{ form.errors['password'] }}
					</div>
				</div>
				<div class="widget-input-group mb-5">
					<TextInput
						v-model="form.password_confirmation"
						:placeholder="$t('auth.confirm-password')"
						autocomplete="new-password"
						class="widget-input"
						type="password" />
					<div v-if="form.errors['password_confirmation']" class="widget-error">
						{{ form.errors['password_confirmation'] }}
					</div>
				</div>
				<button class="widget-button pl-7" type="submit">
					<p class="block h-7 w-7"></p>
					<p>{{ $t('widget.set') }}</p>
				</button>
			</form>
		</div>
		<div v-else>
			<div class="mx-auto flex h-100 w-100 flex-col items-center justify-center text-center">
				<p class="widget-header">{{ $t('widget.success-header') }}</p>
				{{ $t('widget.password-changed-successfully') }}
			</div>
		</div>
	</Widget3Layout>
</template>

<script lang="ts" setup>
import Widget3Layout from '@/Layouts/Widget3Layout.vue';
import TextInput from '@/Components/Widget-3/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { Customer } from '@/Types/models';
import { ref } from 'vue';
import { useWidgetStore } from '@/Stores/widget';

const props = defineProps<{
	customer: Customer;
	token: string;
	widgetChannel: string;
}>();
const widgetStore = useWidgetStore();

const form = useForm({
	password: '',
	password_confirmation: '',
	widget_channel: props.widgetChannel,
});

const passwordRecoveredStatus = ref<boolean>(false);

function submitForm() {
	form.post(
		route('widget.customers.password-recovery-action', {
			club: widgetStore.club,
			customer: props.customer.id,
			token: props.token,
		}),
		{
			onSuccess: (response) => {
				passwordRecoveredStatus.value = true;
			},
			preserveState: true,
			preserveScroll: true,
		},
	);
}
</script>
