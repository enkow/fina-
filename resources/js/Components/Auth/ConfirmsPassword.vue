<script setup>
import { nextTick, reactive, ref } from 'vue';
import DialogModal from './DialogModal.vue';
import InputError from './InputError.vue';
import TextInput from './TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';

const { capitalize } = useString();

const emit = defineEmits(['confirmed']);

defineProps({
	title: {
		type: String,
	},
	content: {
		type: String,
	},
	button: {
		type: String,
	},
});

const confirmingPassword = ref(false);

const form = reactive({
	password: '',
	error: '',
	processing: false,
});

const passwordInput = ref(null);

const startConfirmingPassword = () => {
	axios.get(route('password.confirmation')).then((response) => {
		if (response.data.confirmed) {
			emit('confirmed');
		} else {
			confirmingPassword.value = true;

			setTimeout(() => passwordInput.value.focus(), 250);
		}
	});
};

const confirmPassword = () => {
	form.processing = true;

	axios
		.post(route('password.confirm'), {
			password: form.password,
		})
		.then(() => {
			form.processing = false;

			closeModal();
			nextTick().then(() => emit('confirmed'));
		})
		.catch((error) => {
			form.processing = false;
			form.error = error.response.data.errors.password[0];
			passwordInput.value.focus();
		});
};

const closeModal = () => {
	confirmingPassword.value = false;
	form.password = '';
	form.error = '';
};
</script>

<template>
	<span>
		<span @click="startConfirmingPassword">
			<slot />
		</span>

		<DialogModal :show="confirmingPassword" @close="closeModal">
			<template #title>
				{{ title ?? $t('auth.confirm-password') }}
			</template>

			<template #content>
				{{ content ?? $t('auth.confirm-password-to-continue') }}

				<div class="mt-4">
					<TextInput
						ref="passwordInput"
						v-model="form.password"
						:placeholder="capitalize($t('main.password'))"
						class="mt-1 block w-3/4"
						type="password"
						@keyup.enter="confirmPassword" />

					<InputError :message="form.error" class="mt-2" />
				</div>
			</template>

			<template #footer>
				<Button class="secondary capitalize" @click="closeModal">
					{{ $t('main.action.cancel') }}
				</Button>

				<Button
					:class="{ 'opacity-25': form.processing }"
					:disabled="form.processing"
					class="info ml-3"
					@click="confirmPassword">
					{{ capitalize($t('main.action.confirm')) }}
				</Button>
			</template>
		</DialogModal>
	</span>
</template>
