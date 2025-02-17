<script lang="ts" setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Auth/AuthenticationCardLogo.vue';
import InputError from '@/Components/Auth/InputError.vue';
import TextInput from '@/Components/Auth/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';
import { ref } from 'vue';

const { capitalize } = useString();

const props = defineProps<{
	secret: String;
}>();

const form = useForm({
	password: '',
});

const passwordFieldType = ref<string>('password');
</script>

<template>
	<Head :title="$t('auth.set-password')" />

	<AuthenticationCard>
		<template #logo>
			<AuthenticationCardLogo />
		</template>

		<form
			class="mt-6"
			@submit.prevent="
				form.post(route('register', { secret: secret }), {
					preserveState: true,
					preserveScroll: true,
				})
			">
			<div class="mt-4">
				<div class="relative">
					<TextInput
						id="password"
						v-model="form.password"
						:placeholder="capitalize($t('main.password'))"
						:type="passwordFieldType"
						autocomplete="new-password"
						class="mt-1 block w-full cursor-pointer"
						required />
					<div class="absolute right-3 top-3.5">
						<svg
							v-if="passwordFieldType === 'password'"
							class="h-6 w-6 cursor-pointer"
							fill="none"
							stroke="currentColor"
							stroke-width="1.5"
							viewBox="0 0 24 24"
							xmlns="http://www.w3.org/2000/svg"
							@click="passwordFieldType = 'text'">
							<path
								d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
								stroke-linecap="round"
								stroke-linejoin="round" />
							<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" />
						</svg>
						<svg
							v-if="passwordFieldType === 'text'"
							class="h-6 w-6 cursor-pointer"
							fill="none"
							stroke="currentColor"
							stroke-width="1.5"
							viewBox="0 0 24 24"
							xmlns="http://www.w3.org/2000/svg"
							@click="passwordFieldType = 'password'">
							<path
								d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"
								stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
					</div>
				</div>
				<InputError :message="form.errors.password" class="mt-2" />
			</div>

			<div class="mt-8 flex items-center justify-center">
				<Button
					:class="{ 'opacity-25': form.processing }"
					:disabled="form.processing"
					class="!h-11.5 !px-10 pb-4 pt-2 !text-xl"
					type="submit">
					{{ $t('auth.set-password') }}
				</Button>
			</div>
		</form>
	</AuthenticationCard>
</template>
