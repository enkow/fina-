<script setup>
import { capitalize, ref } from 'vue';
import ActionMessage from '@/Components/Auth/ActionMessage.vue';
import ActionSection from '@/Components/Auth/ActionSection.vue';
import DialogModal from '@/Components/Auth/DialogModal.vue';
import InputError from '@/Components/Auth/InputError.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

defineProps({
	sessions: Array,
});

const confirmingLogout = ref(false);
const passwordInput = ref(null);

const form = useForm({
	password: '',
});

const confirmLogout = () => {
	confirmingLogout.value = true;

	setTimeout(() => passwordInput.value.focus(), 250);
};

const logoutOtherBrowserSessions = () => {
	form.delete(route('other-browser-sessions.destroy'), {
		preserveScroll: true,
		onSuccess: () => closeModal(),
		onError: () => passwordInput.value.focus(),
		onFinish: () => form.reset(),
	});
};

const closeModal = () => {
	confirmingLogout.value = false;

	form.reset();
};
</script>

<template>
	<ActionSection>
		<template #title>{{ $t('settings.browser-sessions') }}</template>

		<template #description>
			{{ $t('settings.browser-sessions-description') }}
		</template>

		<template #content>
			<div class="max-w-xl text-sm text-gray-600">
				{{ $t('settings.browser-sessions-content') }}
			</div>

			<!-- Other Browser Sessions -->
			<div v-if="sessions.length > 0" class="mt-5 space-y-6">
				<div v-for="(session, i) in sessions" :key="i" class="flex items-center">
					<div>
						<svg
							v-if="session.agent.is_desktop"
							class="h-8 w-8 text-gray-500"
							fill="none"
							stroke="currentColor"
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							viewBox="0 0 24 24">
							<path
								d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
						</svg>

						<svg
							v-else
							class="h-8 w-8 text-gray-500"
							fill="none"
							stroke="currentColor"
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							viewBox="0 0 24 24"
							xmlns="http://www.w3.org/2000/svg">
							<path d="M0 0h24v24H0z" stroke="none" />
							<rect height="16" rx="1" width="10" x="7" y="4" />
							<path d="M11 5h2M12 17v.01" />
						</svg>
					</div>

					<div class="ml-3">
						<div class="text-sm text-gray-600">
							{{ session.agent.platform ? session.agent.platform : 'Unknown' }}
							- {{ session.agent.browser ? session.agent.browser : 'Unknown' }}
						</div>

						<div>
							<div class="text-xs text-gray-500">
								{{ session.ip_address }},

								<span v-if="session.is_current_device" class="font-semibold text-green-500">
									{{ $t('settings.this-device') }}
								</span>
								<span v-else>{{ $t('settings.last-active') }} {{ session.last_active }}</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="mt-5 flex items-center">
				<Button class="brand" @click="confirmLogout">
					{{ $t('settings.logout-other-browser-sessions') }}
				</Button>

				<ActionMessage :on="form.recentlySuccessful" class="ml-3">{{ $t('settings.done') }}.</ActionMessage>
			</div>

			<!-- Log Out Other Devices Confirmation Modal -->
			<DialogModal :show="confirmingLogout" @close="closeModal">
				<template #title>
					{{ $t('settings.logout-other-browser-sessions') }}
				</template>

				<template #content>
					{{ $t('settings.enter-password-to-logout-other-browser-sessions') }}

					<div class="mt-4">
						<TextInput
							ref="passwordInput"
							v-model="form.password"
							:placeholder="$t('main.password')"
							class="mt-1 block w-3/4"
							type="password"
							@keyup.enter="logoutOtherBrowserSessions" />

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
						class="brand ml-3"
						@click="logoutOtherBrowserSessions">
						{{ $t('settings.logout-other-browser-sessions') }}
					</Button>
				</template>
			</DialogModal>
		</template>
	</ActionSection>
</template>
