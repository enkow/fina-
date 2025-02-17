<template>
	<div
		class="login-container text-widget flex h-[300px] w-full flex-col items-center justify-center rounded-md border border-[3px] p-5">
		<p class="mb-3 text-center" v-if="!widgetStore.customerSmsLimitReached">
			{{
				$t('widget.verification-sms-sent', {
					phone: showPhone(widgetStore.customer?.phone),
				})
			}}
		</p>
		<p class="mb-3 text-center" v-else>
			{{
				$t('widget.verification-sms-limit-reached', {
					phone: showPhone(widgetStore.customer?.phone),
				})
			}}
		</p>
		<div class="widget-input-group">
			<div class="flex">
				<TextInput v-model="form.code" :placeholder="$t('calendar.verification-code')" class="widget-input text-center" maxlength="6" />
				<div
					v-if="form.errors['code']"
					v-tippy
					:content="
						$t(form.errors['code'], {
							attribute: $t('validation.attributes.code'),
							min: '6',
							max: '6',
						})
					"
					class="mt-1.25">
					<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
				</div>
			</div>
		</div>

		<button class="widget-button mt-3 !justify-center" @click="handleSendVerificationCode">
			{{ $t('calendar.verification-button') }}
		</button>
		<button
			class="widget-button mt-3 !justify-center"
			@click="resendVerificationCode"
			v-if="!widgetStore.customerSmsLimitReached">
			{{ $t('widget.resend-verification-link') }}
		</button>
		<button class="widget-button mt-3 !justify-center" @click="changePhoneNumber">
			{{ $t('widget.change-phone-number') }}
		</button>
    <div
        class="transition-opacity font-medium h-4 !mt-2"
        :class="{
      'error': verificationResendError,
      'success': !verificationResendError,
      'opacity-0': verificationResendText == '',
      'opacity-100': verificationResendText != ''
    }">
      {{ verificationResendTextDebounced }}
    </div>
	</div>
</template>

<script lang="ts" setup>
import { useWidgetAccountVerificationSms } from '@/Composables/useWidgetAccountVerification';
import TextInput from '@/Components/Widget-3/TextInput.vue';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';
import { useWidgetPhoneFormatting } from '@/Composables/useWidgetPhoneFormatting';
import { useWidgetStore } from '@/Stores/widget';
import {ref, watch} from 'vue';
import {refDebounced} from "@vueuse/core";

const widgetStore = useWidgetStore();
const { showPhone } = useWidgetPhoneFormatting();
const {
	form,
	handleSendVerificationCode: handleSendVerificationCodeImport,
	resendVerificationCode,
	verificationResendError,
	verificationResendText,
} = useWidgetAccountVerificationSms();

const verificationResendTextDebounced = ref("");
watch(verificationResendText, () => {
  if(verificationResendText.value.length === 0) {
    setTimeout(() => {
      verificationResendTextDebounced.value = "";
    }, 1000);
    return;
  }
  verificationResendTextDebounced.value = verificationResendText.value;
});

const emit = defineEmits<{
	(e: 'reloadCustomer'): void;
	(e: 'changePhoneNumber'): void;
}>();

const handleSendVerificationCode = () => {
	handleSendVerificationCodeImport();
	emit('reloadCustomer');
};

const changePhoneNumber = () => {
	emit('changePhoneNumber');
};

if (widgetStore.resendCode) {
	resendVerificationCode();
	widgetStore.resendCode = false;
}
</script>

<style scoped>
::placeholder {
	text-align: center;
}
.widget-input-group,
.widget-input-group .widget-input {
	width: 12rem !important;
}
.transition-opacity {
  transition: opacity 0.15s ease-in-out;
}
</style>
