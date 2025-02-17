<script setup>
import { nextTick, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Auth/AuthenticationCardLogo.vue';
import InputError from '@/Components/Auth/InputError.vue';
import InputLabel from '@/Components/Auth/InputLabel.vue';
import TextInput from '@/Components/Auth/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';

const { capitalize } = useString();

const recovery = ref(false);

const form = useForm({
	code: '',
	recovery_code: '',
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
	recovery.value ^= true;

	await nextTick();

	if (recovery.value) {
		recoveryCodeInput.value.focus();
		form.code = '';
	} else {
		codeInput.value.focus();
		form.recovery_code = '';
	}
};

const submit = () => {
	form.post(route('two-factor.login'));
};
</script>

<template>
	<Head :title="$t('auth.two-factor-confirmation')" />

	<AuthenticationCard>
		<template #logo>
			<AuthenticationCardLogo />
		</template>

		<div class="mb-4 mt-6 text-sm text-gray-600">
			<template v-if="!recovery">
				{{ $t('auth.two-factor-auth-description') }}
			</template>

			<template v-else>
				{{ $t('auth.two-factor-recovery-codes-description') }}
			</template>
		</div>

		<form @submit.prevent="submit">
			<div v-if="!recovery">
				<TextInput
					id="code"
					ref="codeInput"
					v-model="form.code"
					:placeholder="$t('auth.code')"
					autocomplete="one-time-code"
					autofocus
					class="placeholder-uppercase mt-1 block w-full"
					inputmode="numeric"
					type="text" />
				<InputError :message="form.errors.code" class="mt-2" />
			</div>

			<div v-else>
				<InputLabel :value="$t('auth.recovery-code')" for="recovery_code" />
				<TextInput
					id="recovery_code"
					ref="recoveryCodeInput"
					v-model="form.recovery_code"
					:placeholder="capitalize($t('auth.recovery-code'))"
					autocomplete="one-time-code"
					class="mt-1 block w-full"
					type="text" />
				<InputError :message="form.errors.recovery_code" class="mt-2" />
			</div>

			<div class="mt-4 flex items-center justify-between">
				<button
					class="cursor-pointer text-sm text-gray-600 underline hover:text-gray-900"
					type="button"
					@click.prevent="toggleRecovery">
					<template v-if="!recovery">
						{{ $t('auth.use-a-recovery-code') }}
					</template>

					<template v-else>
						{{ $t('auth.use-an_authentication-code') }}
					</template>
				</button>

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
