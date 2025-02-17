<template>
	<CalendarSectionTitle>{{ $t('calendar.account-verification') }}</CalendarSectionTitle>
	<div class="space-y-4 text-center text-ui-text">
		<p>{{ $t('widget.verification-email-sent') }}</p>
		<p>{{ $t('widget.verification-button-click-info') }}</p>
		<WidgetButton color="green" @click="handleResendButtonClick" fill>
			{{ $t('widget.resend-verification-link') }}
		</WidgetButton>
		<p v-if="verificationLinkResend" class="font-medium text-ui-green-950">
			{{ $t('widget.verification-email-resent') }}
		</p>
	</div>
</template>

<script lang="ts" setup>
import CalendarSectionTitle from './CalendarSectionTitle.vue';
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import { useWidgetAccountVerification } from '@/Composables/useWidgetAccountVerification';

const { verificationLinkResend, handleResendButtonClick } = useWidgetAccountVerification();
</script>
