<script setup>
import { capitalize, ref } from 'vue';
import ActionMessage from '@/Components/Auth/ActionMessage.vue';
import FormSection from '@/Components/Auth/FormSection.vue';
import InputError from '@/Components/Auth/InputError.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
	current_password: '',
	password: '',
	password_confirmation: '',
});

const updatePassword = () => {
	form.put(route('user-password.update'), {
		errorBag: 'updatePassword',
		preserveScroll: true,
		onSuccess: () => form.reset(),
		onError: () => {
			if (form.errors.password) {
				form.reset('password', 'password_confirmation');
				passwordInput.value.focus();
			}

			if (form.errors.current_password) {
				form.reset('current_password');
				currentPasswordInput.value.focus();
			}
		},
	});
};
</script>

<template>
	<FormSection @submitted="updatePassword">
		<template #title>{{ $t('settings.update-password') }}</template>

		<template #description>
			{{ $t('settings.update-password-description') }}
		</template>

		<template #form>
			<div class="col-span-6 sm:col-span-4">
				<InputLabel :value="$t('settings.current-password')" for="current_password" />
				<TextInput
					id="current_password"
					ref="currentPasswordInput"
					v-model="form.current_password"
					autocomplete="current-password"
					class="mt-1 block w-full"
					type="password" />
				<InputError :message="form.errors.current_password" class="mt-2" />
			</div>

			<div class="col-span-6 sm:col-span-4">
				<InputLabel :value="$t('settings.new-password')" for="password" />
				<TextInput
					id="password"
					ref="passwordInput"
					v-model="form.password"
					autocomplete="new-password"
					class="mt-1 block w-full"
					type="password" />
				<InputError :message="form.errors.password" class="mt-2" />
			</div>

			<div class="col-span-6 sm:col-span-4">
				<InputLabel :value="$t('auth.confirm-password')" for="password_confirmation" />
				<TextInput
					id="password_confirmation"
					v-model="form.password_confirmation"
					autocomplete="new-password"
					class="mt-1 block w-full"
					type="password" />
				<InputError :message="form.errors.password_confirmation" class="mt-2" />
			</div>
		</template>

		<template #actions>
			<ActionMessage :on="form.recentlySuccessful" class="mr-3">
				{{ capitalize($t('main.after-action.save')) }}.
			</ActionMessage>

			<Button
				:class="{ 'opacity-25': form.processing }"
				:disabled="form.processing"
				class="brand"
				type="submit">
				{{ capitalize($t('main.action.save')) }}
			</Button>
		</template>
	</FormSection>
</template>
