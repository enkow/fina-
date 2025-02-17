<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Auth/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Auth/Checkbox.vue';
import InputError from '@/Components/Auth/InputError.vue';
import TextInput from '@/Components/Auth/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';

const { capitalize } = useString();

defineProps({
	canResetPassword: Boolean,
	status: String,
	errors: Object,
});

const form = useForm({
	email: '',
	password: '',
	remember: false,
});

const submit = () => {
	form
		.transform((data) => ({
			...data,
			remember: form.remember ? 'on' : '',
		}))
		.post(route('login'), {
			onFinish: () => form.reset('password'),
		});
};
</script>

<template>
	<Head :title="$t('auth.login')" />

	<AuthenticationCard>
		<template #logo>
			<AuthenticationCardLogo />
		</template>

		<h1 class="pb-7 pt-7.5 text-center text-3xl font-semibold text-gray-6">
			{{ $t('auth.login') }}
		</h1>

		<div v-if="status" class="mb-4 text-sm font-medium text-green-600">
			{{ status }}
		</div>

		<div v-if="errors" class="mb-4 text-center text-sm font-medium text-danger-base">
			{{ errors.message }}
		</div>

		<form @submit.prevent="submit">
			<div class="mb-5 space-y-6">
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
						autocomplete="current-password"
						class="mt-1 block w-full"
						required
						type="password" />
					<InputError :message="form.errors.password" class="mt-2" />
				</div>
			</div>

			<div class="block flex justify-between">
				<label class="flex items-center">
					<Checkbox v-model:checked="form.remember" name="remember" />
					<span class="ml-2 text-base text-gray-600">{{ $t('auth.remember-me') }}</span>
				</label>
				<Link
					v-if="canResetPassword"
					:href="route('password.request')"
					class="text-base font-normal text-black/80 underline hover:text-gray-900">
					{{ $t('auth.forgot-your-password') }}
				</Link>
			</div>

			<div class="mt-8 flex items-center justify-center">
				<Button
					:class="{ 'opacity-25': form.processing }"
					:disabled="form.processing"
					class="!h-11.5 !px-10 pb-4 pt-2 !text-xl"
					type="submit">
					{{ $t('auth.login') }}
				</Button>
			</div>
		</form>
	</AuthenticationCard>
</template>
