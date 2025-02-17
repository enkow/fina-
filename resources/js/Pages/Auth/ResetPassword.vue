<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Auth/AuthenticationCardLogo.vue';
import InputError from '@/Components/Auth/InputError.vue';
import TextInput from '@/Components/Auth/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';

const { capitalize } = useString();

const props = defineProps({
	email: String,
	token: String,
});

const form = useForm({
	token: props.token,
	email: props.email,
	password: '',
	password_confirmation: '',
});

const submit = () => {
	form.post(route('password.update'), {
		onFinish: () => form.reset('password', 'password_confirmation'),
	});
};
</script>

<template>
	<Head :title="$t('auth.reset-password')" />

	<AuthenticationCard>
		<template #logo>
			<AuthenticationCardLogo />
		</template>
		<form class="mt-6" @submit.prevent="submit">
			<div>
				<TextInput
					id="email"
					v-model="form.email"
					:placeholder="capitalize($t('main.email-address'))"
					autocomplete="email"
					autofocus
					class="mt-1 block w-full"
					required
					type="email" />
				<InputError :message="form.errors.email" class="mt-2" />
			</div>

			<div class="mt-4">
				<TextInput
					id="password"
					v-model="form.password"
					:placeholder="capitalize($t('main.password'))"
					autocomplete="new-password"
					class="mt-1 block w-full"
					required
					type="password" />
				<InputError :message="form.errors.password" class="mt-2" />
			</div>

			<div class="mt-4">
				<TextInput
					id="password_confirmation"
					v-model="form.password_confirmation"
					:placeholder="capitalize($t('main.confirm-password'))"
					autocomplete="new-password"
					class="mt-1 block w-full"
					required
					type="password" />
				<InputError :message="form.errors.password_confirmation" class="mt-2" />
			</div>

			<div class="mt-8 flex items-center justify-center">
				<Button
					:class="{ 'opacity-25': form.processing }"
					:disabled="form.processing"
					class="!h-11.5 !px-10 pb-4 pt-2 !text-xl"
					type="submit">
					{{ $t('auth.reset-password') }}
				</Button>
			</div>
		</form>
	</AuthenticationCard>
</template>
