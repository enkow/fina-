<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Auth/AuthenticationCardLogo.vue';
import InputError from '@/Components/Auth/InputError.vue';
import InputLabel from '@/Components/Auth/InputLabel.vue';
import TextInput from '@/Components/Auth/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';

const { capitalize } = useString();

const form = useForm({
	password: '',
});

const passwordInput = ref(null);

const submit = () => {
	form.post(route('password.confirm'), {
		onFinish: () => {
			form.reset();

			passwordInput.value.focus();
		},
	});
};
</script>

<template>
	<Head :title="$t('auth.secure-area')" />

	<AuthenticationCard>
		<template #logo>
			<AuthenticationCardLogo />
		</template>

		<div class="mb-4 text-sm text-gray-600">
			{{ $t('auth.confirm-your-password-description') }}
		</div>

		<form @submit.prevent="submit">
			<div>
				<InputLabel :value="capitalize($t('main.password'))" for="password" />
				<TextInput
					id="password"
					ref="passwordInput"
					v-model="form.password"
					autocomplete="current-password"
					autofocus
					class="mt-1 block w-full"
					required
					type="password" />
				<InputError :message="form.errors.password" class="mt-2" />
			</div>
			<div class="mt-8 flex items-center justify-center">
				<Button
					:class="{ 'opacity-25': form.processing }"
					:disabled="form.processing"
					class="!h-11.5 !px-10 pb-4 pt-2 !text-xl"
					type="submit">
					{{ $t('main.action.confirm') }}
				</Button>
			</div>
		</form>
	</AuthenticationCard>
</template>
