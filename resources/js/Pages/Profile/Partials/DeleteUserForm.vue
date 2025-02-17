<script setup>
import { capitalize, ref } from 'vue';
import ActionSection from '@/Components/Auth/ActionSection.vue';
import DialogModal from '@/Components/Auth/DialogModal.vue';
import InputError from '@/Components/Auth/InputError.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
	password: '',
});

const confirmUserDeletion = () => {
	confirmingUserDeletion.value = true;

	setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
	form.delete(route('current-user.destroy'), {
		preserveScroll: true,
		onSuccess: () => closeModal(),
		onError: () => passwordInput.value.focus(),
		onFinish: () => form.reset(),
	});
};

const closeModal = () => {
	confirmingUserDeletion.value = false;

	form.reset();
};
</script>

<template>
	<ActionSection>
		<template #title>{{ $t('settings.delete-account') }}</template>

		<template #description>
			{{ $t('settings.permanently-delete-your-account') }}
		</template>

		<template #content>
			<div class="max-w-xl text-sm text-gray-600">
				{{ $t('settings.delete-account-description') }}
			</div>

			<div class="mt-5">
				<Button class="danger" @click="confirmUserDeletion">
					{{ $t('settings.delete-account') }}
				</Button>
			</div>

			<!-- Delete Account Confirmation Modal -->
			<DialogModal :show="confirmingUserDeletion" @close="closeModal">
				<template #title>{{ $t('settings.delete-account') }}</template>

				<template #content>
					{{ $t('settings.delete-account-confirmation') }}

					<div class="mt-4">
						<TextInput
							ref="passwordInput"
							v-model="form.password"
							:placeholder="$t('main.password')"
							class="mt-1 block w-3/4"
							type="password"
							@keyup.enter="deleteUser" />

						<InputError :message="form.errors.password" class="mt-2" />
					</div>
				</template>

				<template #footer>
					<Button class="grey" @click="closeModal">
						{{ capitalize($t('main.action.cancel')) }}
					</Button>

					<Button
						:class="{ 'opacity-25': form.processing }"
						:disabled="form.processing"
						class="danger ml-3"
						@click="deleteUser">
						{{ $t('settings.delete-account') }}
					</Button>
				</template>
			</DialogModal>
		</template>
	</ActionSection>
</template>
