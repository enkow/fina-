<template>
	<div
		class="widget-2-scrollbar flex h-full flex-col items-center space-y-3 overflow-auto py-2 text-center text-sm md:h-fit">
		<p>
			{{
				$t('widget.verification-sms-sent', {
					phone: showPhone(widgetStore.customer?.phone),
				})
			}}
		</p>

		<p>
			{{ $t('widget-2.verify-yourself-to-complete-your-reservation') }}
		</p>

		<Widget2InputWrapper
			:error="
				form.errors.code &&
				$t(form.errors.code, {
					attribute: $t('validation.attributes.code'),
					min: '6',
					max: '6',
				})
			">
			<Widget2Input v-model="form.code" color="white" :placeholder="$t('calendar.verification-code')" />
		</Widget2InputWrapper>

		<WidgetButton
			size="compact-md"
			color="green"
			@click="handleSendVerificationCode"
			v-text="$t('calendar.verification-button')"
			class="shrink-0" />
		<WidgetButton
			size="compact-md"
			color="green"
			@click="resendVerificationCode"
			v-text="$t('widget.resend-verification-link')"
			class="shrink-0" />
		<WidgetButton
			size="compact-md"
			color="green"
			@click="$emit('changePhoneNumber')"
			v-text="$t('widget.change-phone-number')"
			class="shrink-0" />
		<div
			v-if="verificationResendText !== ''"
			class="mt-2 font-medium"
			:class="verificationResendError ? 'error' : 'success'">
			{{ $t(verificationResendText) }}
		</div>
	</div>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import { useWidgetAccountVerificationSms } from '@/Composables/useWidgetAccountVerification';
import Widget2InputWrapper from './Widget2InputWrapper.vue';
import Widget2Input from './Widget2Input.vue';
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
