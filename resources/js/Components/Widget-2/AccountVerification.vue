<template>
	<div
		class="widget-2-scrollbar flex h-full flex-col items-center space-y-3 overflow-auto py-2 text-center text-sm md:h-fit">
		<p>
			{{ $t('widget-2.account-verification-success') }}
		</p>
		<p>{{ $t('widget-2.verify-yourself-to-complete-your-reservation') }}</p>
		<WidgetButton size="compact-md" @click="handleResendButtonClick" class="shrink-0">
			{{ $t('widget-2.resubmit') }}
		</WidgetButton>
		<p v-if="verificationLinkResend" class="font-medium text-ui-green-800">
			{{ $t('widget-2.verification-link-has-been-resent') }}
		</p>
	</div>
</template>

<script lang="ts" setup>
import WidgetButton from '../Widget/Ui/WidgetButton.vue';
import { useWidgetAccountVerification } from '@/Composables/useWidgetAccountVerification';

const { verificationLinkResend, handleResendButtonClick } = useWidgetAccountVerification();
</script>
