<template>
	<div
		class="login-container text-widget flex h-[300px] w-full flex-col items-center justify-center rounded-md border border-[3px] p-5">
		<p class="mb-3 text-center">
			{{ $t('widget.verification-email-sent') }}
		</p>
		<p class="mb-4">{{ $t('widget.verification-button-click-info') }}</p>
		<button class="widget-button !justify-center" @click="resendVerificationLink">
			{{ $t('widget.resend-verification-link') }}
		</button>
		<div v-if="verificationLinkResend" class="success mt-2 font-medium">
			{{ $t('widget.verification-email-resent') }}
		</div>
	</div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import axios from 'axios';
import { Customer } from '@/Types/models';
import { useWidgetStore } from '@/Stores/widget';

const props = defineProps<{
	channel: any;
}>();

const emit = defineEmits<{
	(e: 'reloadCustomer'): void;
}>();
const widgetStore = useWidgetStore();

widgetStore.channel.bind('customer-verified', function (data: { customer: Customer; channel: string }) {
	emit('reloadCustomer');
});

const verificationLinkResend = ref<boolean>(false);

function resendVerificationLink() {
	axios
		.get(
			route('widget.customers.verification-resend', {
				club: widgetStore.club,
				encryptedCustomerId: widgetStore.customer?.encryptedId,
			}),
		)
		.then((response) => {
			verificationLinkResend.value = true;
		});
}
</script>

<style scoped></style>
