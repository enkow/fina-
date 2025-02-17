<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Auth/AuthenticationCardLogo.vue';
import InputError from '@/Components/Auth/InputError.vue';
import TextInput from '@/Components/Auth/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';

const { capitalize } = useString();

defineProps({
	status: String,
});

const form = useForm({
	email: '',
});

const submit = () => {
	form.post(route('password.email'));
};
</script>

<template>
	<Head :title="$t('auth.forgot-your-password')" />

	<AuthenticationCard>
		<template #logo>
			<AuthenticationCardLogo />
		</template>

		<div class="mb-4 mt-4 text-sm text-gray-600">
			{{ $t('auth.forgot-password-description') }}
		</div>

		<div v-if="status" class="mb-4 text-sm font-medium text-green-600">
			{{ status }}
		</div>

		<form @submit.prevent="submit">
			<div>
				<TextInput
					id="email"
					v-model="form.email"
					:placeholder="capitalize($t('main.email-address'))"
					autofocus
					class="mt-1 block w-full"
					required
					type="email" />
				<InputError :message="form.errors.email" class="mt-2" />
			</div>

			<div class="mt-8 flex items-center justify-center">
				<Button
					:class="{ 'opacity-25': form.processing }"
					:disabled="form.processing"
					class="!h-11.5 !px-10 pb-4 pt-2 !text-xl"
					type="submit">
					{{ $t('auth.email-password-reset-link') }}
				</Button>
			</div>
		</form>
	</AuthenticationCard>
</template>
