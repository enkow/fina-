<script setup>
import { capitalize, computed, ref, watch } from 'vue';
import ActionSection from '@/Components/Auth/ActionSection.vue';
import ConfirmsPassword from '@/Components/Auth/ConfirmsPassword.vue';
import InputError from '@/Components/Auth/InputError.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const props = defineProps({
	requiresConfirmation: Boolean,
});

const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
	code: '',
});

const twoFactorEnabled = computed(() => !enabling.value && usePage().props.user?.two_factor_enabled);

watch(twoFactorEnabled, () => {
	if (!twoFactorEnabled.value) {
		confirmationForm.reset();
		confirmationForm.clearErrors();
	}
});

const enableTwoFactorAuthentication = () => {
	enabling.value = true;

	router.post(
		'/user/two-factor-authentication',
		{},
		{
			preserveScroll: true,
			onSuccess: () => Promise.all([showQrCode(), showSetupKey(), showRecoveryCodes()]),
			onFinish: () => {
				enabling.value = false;
				confirming.value = props.requiresConfirmation;
			},
		},
	);
};

const showQrCode = () => {
	return axios.get('/user/two-factor-qr-code').then((response) => {
		qrCode.value = response.data.svg;
	});
};

const showSetupKey = () => {
	return axios.get('/user/two-factor-secret-key').then((response) => {
		setupKey.value = response.data.secretKey;
	});
};

const showRecoveryCodes = () => {
	return axios.get('/user/two-factor-recovery-codes').then((response) => {
		recoveryCodes.value = response.data;
	});
};

const confirmTwoFactorAuthentication = () => {
	confirmationForm.post('/user/confirmed-two-factor-authentication', {
		errorBag: 'confirmTwoFactorAuthentication',
		preserveScroll: true,
		preserveState: true,
		onSuccess: () => {
			confirming.value = false;
			qrCode.value = null;
			setupKey.value = null;
		},
	});
};

const regenerateRecoveryCodes = () => {
	axios.post('/user/two-factor-recovery-codes').then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
	disabling.value = true;

	Inertia.delete('/user/two-factor-authentication', {
		preserveScroll: true,
		onSuccess: () => {
			disabling.value = false;
			confirming.value = false;
		},
	});
};
</script>

<template>
	<ActionSection>
		<template #title>{{ $t('settings.two-factor-authentication') }}</template>

		<template #description>
			{{ $t('settings.two-factor-authentication-description') }}
		</template>

		<template #content>
			<h3 v-if="twoFactorEnabled && !confirming" class="text-lg font-medium text-gray-900">
				{{ $t('settings.two-factor-authentication-enabled') }}
			</h3>

			<h3 v-else-if="twoFactorEnabled && confirming" class="text-lg font-medium text-gray-900">
				{{ $t('settings.two-factor-authentication-finish-enabling') }}
			</h3>

			<h3 v-else class="text-lg font-medium text-gray-900">
				{{ $t('settings.two-factor-authentication-not-enabled') }}
			</h3>

			<div class="mt-3 max-w-xl text-sm text-gray-600">
				<p>
					{{ $t('settings.two-factor-authentication-finish-enabling-description') }}
				</p>
			</div>

			<div v-if="twoFactorEnabled">
				<div v-if="qrCode">
					<div class="mt-4 max-w-xl text-sm text-gray-600">
						<p v-if="confirming" class="font-semibold">
							{{ $t('settings.two-factor-authentication-scan-qr') }}
						</p>

						<p v-else>
							{{ $t('settings.two-factor-authentication-is-now-enabled') }}
						</p>
					</div>

					<div class="mt-4" v-html="qrCode" />

					<div v-if="setupKey" class="mt-4 max-w-xl text-sm text-gray-600">
						<p class="font-semibold">
							{{ $t('settings.setup-key') }}:
							<span v-html="setupKey"></span>
						</p>
					</div>

					<div v-if="confirming" class="mt-4">
						<InputLabel :value="$t('auth.code')" for="code" />

						<TextInput
							id="code"
							v-model="confirmationForm.code"
							autocomplete="one-time-code"
							autofocus
							class="mt-1 block w-1/2"
							inputmode="numeric"
							name="code"
							type="text"
							@keyup.enter="confirmTwoFactorAuthentication" />

						<InputError :message="confirmationForm.errors.code" class="mt-2" />
					</div>
				</div>

				<div v-if="recoveryCodes.length > 0 && !confirming">
					<div class="mt-4 max-w-xl text-sm text-gray-600">
						<p class="font-semibold">
							{{ $t('settings.store-these-recovery-codes') }}
						</p>
					</div>

					<div class="mt-4 grid max-w-xl gap-1 rounded-lg bg-gray-100 px-4 py-4 font-mono text-sm">
						<div v-for="code in recoveryCodes" :key="code">
							{{ code }}
						</div>
					</div>
				</div>
			</div>

			<div class="mt-5">
				<div v-if="!twoFactorEnabled">
					<ConfirmsPassword @confirmed="enableTwoFactorAuthentication">
						<Button :class="{ 'opacity-25': enabling }" :disabled="enabling" type="brand button">
							{{ capitalize($t('main.action.enable')) }}
						</Button>
					</ConfirmsPassword>
				</div>

				<div v-else>
					<ConfirmsPassword @confirmed="confirmTwoFactorAuthentication">
						<Button
							v-if="confirming"
							:class="{ 'opacity-25': enabling }"
							:disabled="enabling"
							class="brand mr-3"
							type="button">
							{{ capitalize($t('main.action.confirm')) }}
						</Button>
					</ConfirmsPassword>

					<ConfirmsPassword @confirmed="regenerateRecoveryCodes">
						<Button v-if="recoveryCodes.length > 0 && !confirming" class="grey mr-3">
							{{ $t('settings.regenerate-recovery-codes') }}
						</Button>
					</ConfirmsPassword>

					<ConfirmsPassword @confirmed="showRecoveryCodes">
						<Button v-if="recoveryCodes.length === 0 && !confirming" class="grey mr-3">
							{{ $t('settings.show-recovery-codes') }}
						</Button>
					</ConfirmsPassword>

					<ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
						<Button v-if="confirming" :class="{ 'opacity-25': disabling }" :disabled="disabling" class="grey">
							{{ capitalize($t('main.action.cancel')) }}
						</Button>
					</ConfirmsPassword>

					<ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
						<Button
							v-if="!confirming"
							:class="{ 'opacity-25': disabling }"
							:disabled="disabling"
							class="danger">
							{{ capitalize($t('main.action.disable')) }}
						</Button>
					</ConfirmsPassword>
				</div>
			</div>
		</template>
	</ActionSection>
</template>
