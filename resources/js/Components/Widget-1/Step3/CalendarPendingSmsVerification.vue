<template>
	<CalendarSectionTitle>{{ $t('calendar.account-verification') }}</CalendarSectionTitle>
	<div class="space-y-4 text-center text-ui-text">
		<p>
			{{
				$t('widget.verification-sms-sent', {
					phone: showPhone(widgetStore.customer?.phone || ''),
				})
			}}
		</p>

		<WidgetInputWrapper
			:error="
				form.errors.code &&
				$t(form.errors.code, {
					attribute: $t('validation.attributes.code'),
					min: '6',
					max: '6',
				})
			">
			<WidgetInput v-model="form.code" color="white" :placeholder="$t('calendar.verification-code')" />
		</WidgetInputWrapper>

		<WidgetButton
			color="green"
			@click="handleSendVerificationCode"
			fill
			v-text="$t('calendar.verification-button')" />

		<WidgetButton
			color="green"
			@click="resendVerificationCode"
			fill
			v-text="$t('widget.resend-verification-link')" />

		<WidgetButton
			color="green"
			@click="$emit('changePhoneNumber')"
			fill
			v-text="$t('widget.change-phone-number')" />
		<div
			v-if="verificationResendText !== ''"
			class="mt-2 font-medium"
			:class="verificationResendError ? 'error' : 'success'">
			{{ $t(verificationResendText) }}
		</div>
	</div>
</template>

<script lang="ts" setup>
import CalendarSectionTitle from './CalendarSectionTitle.vue';
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import WidgetInput from '@/Components/Widget/Ui/WidgetInput.vue';
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import { useWidgetAccountVerificationSms } from '@/Composables/useWidgetAccountVerification';
import { useWidgetPhoneFormatting } from '@/Composables/useWidgetPhoneFormatting';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
const { showPhone } = useWidgetPhoneFormatting();
const {
	form,
	handleSendVerificationCode,
	resendVerificationCode,
	verificationResendError,
	verificationResendText,
} = useWidgetAccountVerificationSms();

if (widgetStore.resendCode) {
	resendVerificationCode();
	widgetStore.resendCode = false;
}
</script>
