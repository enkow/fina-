<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/Auth/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Auth/AuthenticationCardLogo.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const props = defineProps({
	status: String,
});

const form = useForm();

const submit = () => {
	form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
	<Head :title="$t('auth.email-verification')" />

	<AuthenticationCard>
		<template #logo>
			<AuthenticationCardLogo />
		</template>

		<div class="mb-4 text-sm text-gray-600">
			{{ $t('auth.email-verify-description') }}
		</div>

		<div v-if="verificationLinkSent" class="mb-4 text-sm font-medium text-green-600">
			{{ $t('auth.email-verification-link-sent-description') }}
		</div>

		<form @submit.prevent="submit">
			<div class="mt-4 flex items-center justify-between">
				<Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" type="submit">
					{{ $t('auth.resend-verification-email') }}
				</Button>

				<div>
					<Link :href="route('profile.show')" class="text-sm text-gray-600 underline hover:text-gray-900">
						{{ $t('nav.settings') }}
					</Link>

					<Link
						:href="route('logout')"
						as="button"
						class="ml-2 text-sm text-gray-600 underline hover:text-gray-900"
						method="post">
						{{ $t('auth.logout') }}
					</Link>
				</div>
			</div>
		</form>
	</AuthenticationCard>
</template>
