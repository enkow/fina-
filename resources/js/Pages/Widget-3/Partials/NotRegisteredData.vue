<template>
	<div
		class="login-container flex h-[300px] w-full flex-col items-center justify-center rounded-md border border-[3px]">
		<p class="widget-header">{{ $t('widget.pass-reservation-data') }}</p>
		<div class="widget-input-group">
			<div class="flex">
				<TextInput
					v-model="widgetStore.form.customer.first_name"
					:placeholder="$t('widget.first-name')"
					class="widget-input" />
				<div v-if="firstNameError" v-tippy :content="firstNameError" class="mt-1.25">
					<InfoIcon class="ml-1.25 h-3 w-3 text-danger-dark" />
				</div>
			</div>
		</div>
		<div class="widget-input-group">
			<div class="flex">
				<TextInput
					v-model="widgetStore.form.customer.last_name"
					:placeholder="$t('widget.last-name')"
					class="widget-input" />
				<div v-if="lastNameError" v-tippy :content="lastNameError" class="mt-1.25">
					<InfoIcon class="ml-1.25 h-3 w-3 text-danger-dark" />
				</div>
			</div>
		</div>
		<div class="widget-input-group">
			<div class="flex">
				<TextInput
					v-model="widgetStore.form.customer.email"
					:placeholder="$t('widget.email')"
					class="widget-input" />
				<div v-if="emailError" v-tippy :content="emailError" class="mt-1.25">
					<InfoIcon class="ml-1.25 h-3 w-3 text-danger-dark" />
				</div>
			</div>
		</div>
		<div class="widget-input-group">
			<div class="flex">
				<TextInput
					v-model="phone"
					@input="formatPhone"
					:placeholder="$t('widget.phone-short')"
					class="widget-input" />
				<div v-if="phoneError" v-tippy :content="phoneError" class="mt-1.25">
					<InfoIcon class="ml-1.25 h-3 w-3 text-danger-dark" />
				</div>
			</div>
		</div>


		<div v-if="agreements['generalTerms'] && agreements['privacyPolicy']" class="consent mt-1">
				<Checkbox
					id="termsAndPrivacyPolicyCheckbox"
					v-model="widgetStore.form.customer.generalTerms"
					class="text-widget !h-4 !w-4" />
				<label class="flex flex-wrap" for="termsAndPrivacyPolicyCheckbox">
					<p>
						{{ $t('widget.agreements.accept') }}
						<a
							class="agreement-link"
							v-if="agreements['generalTerms']['content_type'] === 0"
							:href="'/club-assets/agreements/' + agreements['generalTerms']['file']"
							target="_blank">
							&nbsp;{{ $t('widget.agreements.terms') }}
						</a>
						<a v-else v-tippy :content="agreements['generalTerms']['text']">
							{{ $t('widget.agreements.terms') }}
						</a>
						{{ $t('widget.agreements.and') }}
						<a
							class="agreement-link"
							v-if="agreements['privacyPolicy']['content_type'] === 0"
							:href="'/club-assets/agreements/' + agreements['privacyPolicy']['file']"
							target="_blank">
							{{ $t('widget.agreements.privacy-policy') }}
						</a>
						<a v-else v-tippy :content="agreements['privacyPolicy']['text']">
							{{ $t('widget.agreements.privacy-policy') }}
						</a>

						<a
							v-if="
								(consentErrors['consents.general_terms'] || consentErrors['consents.privacy_policy'])
							"
							v-tippy
							:content="$t(consentErrors['consents.general_terms'])"
							class="-mb-0.75 inline-block">
							<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
						</a>
					</p>
				</label>
			</div>
			<div v-else-if="agreements['generalTerms']" class="consent">
				<Checkbox id="termsCheckbox" v-model="widgetStore.form.customer.generalTerms" class="text-widget !h-4 !w-4" />
				<label class="flex space-x-1" for="termsCheckbox">
					<p>
						{{ $t('widget.agreements.accept') }}
						<a
							class="agreement-link"
							v-if="agreements['generalTerms']['content_type'] === 0"
							:href="'/club-assets/agreements/' + agreements['generalTerms']['file']"
							target="_blank">
							{{ $t('widget.agreements.terms') }}
						</a>
						<a v-else v-tippy :content="agreements['generalTerms']['text']">
							{{ $t('widget.agreements.terms') }}
						</a>
						<a
							v-if="consentErrors['consents.general_terms']"
							v-tippy
							:content="$t(consentErrors['consents.general_terms'])"
							class="-mb-0.75 inline-block">
							<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
						</a>
					</p>
				</label>
			</div>
			<div v-else-if="agreements['privacyPolicy']" class="consent">
				<Checkbox
					id="privacyPolicyCheckbox"
					v-model="widgetStore.form.customer.privacyPolicy"
					class="text-widget !h-4 !w-4" />
				<label class="flex space-x-1" for="privacyPolicyCheckbox">
					<p>
						{{ $t('widget.agreements.accept') }}
						<a
							class="agreement-link"
							v-if="agreements['privacyPolicy']['content_type'] === 0"
							:href="'/club-assets/agreements/' + agreements['privacyPolicy']['file']"
							target="_blank">
							{{ $t('widget.agreements.privacy-policy') }}
						</a>
						<a v-else v-tippy :content="agreements['privacyPolicy']['text']">
							{{ $t('widget.agreements.privacy-policy') }}
						</a>
						<a
							v-if="consentErrors['consents.privacy_policy']"
							v-tippy
							:content="$t(consentErrors['consents.privacy_policy'])"
							class="-mb-0.75 inline-block">
							<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
						</a>
					</p>
				</label>
			</div>
			<div v-if="agreements['marketingAgreement']" class="consent">
				<Checkbox
					id="marketingCheckbox"
					v-model="widgetStore.form.customer.marketingAgreement"
					class="text-widget !h-4 !w-4" />
				<label class="flex space-x-1" for="marketingCheckbox">
					<p>
						{{ $t('widget.agreements.accept') }}
						<a
							class="agreement-link"
							v-if="agreements['marketingAgreement']['content_type'] === 0"
							:href="'/club-assets/agreements/' + agreements['marketingAgreement']['file']"
							target="_blank">
							{{ $t('widget.agreements.marketing-agreement') }}
						</a>
						<a v-else v-tippy :content="agreements['marketingAgreement']['text']">
							{{ $t('widget.agreements.marketing-agreement') }}
						</a>
						<a
							v-if="consentErrors['consents.marketing_agreement']"
							v-tippy
							:content="$t(consentErrors['consents.marketing_agreement'])"
							class="-mb-0.75 inline-block">
							<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
						</a>
					</p>
				</label>
			</div>

		<button class="widget-button mt-4 flex w-48 justify-center" type="submit" @click="submit">
			<p>{{ $t('widget.continue') }}</p>
		</button>
	</div>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import TextInput from '@/Components/Widget-3/TextInput.vue';
import { ref, watch } from 'vue';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';
import { wTrans } from 'laravel-vue-i18n';
import { useWidgetPhoneFormatting } from '@/Composables/useWidgetPhoneFormatting';
import { Agreement } from '@/Types/models';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';

const firstNameError = ref<string | null>(null);
const lastNameError = ref<string | null>(null);
const emailError = ref<string | null>(null);
const phoneError = ref<string | null>(null);
const consentErrors = ref({})

const emit = defineEmits<{
	(e: 'submit'): void;
}>();

function submit() {
	phoneError.value = null;
	emailError.value = null;
	firstNameError.value = null;
	lastNameError.value = null;
	if (widgetStore.form.customer.first_name === null || widgetStore.form.customer.first_name.length < 1) {
		firstNameError.value = wTrans('widget.enter-valid-first-name').value;
	}
	if (widgetStore.form.customer.last_name === null || widgetStore.form.customer.last_name.length < 1) {
		lastNameError.value = wTrans('widget.enter-valid-last-name').value;
	}
	let phonePattern = /^[+0-9 \-]*$/;
	if (
		widgetStore.form.customer.phone === null ||
		widgetStore.form.customer.phone.length < 7 ||
		!phonePattern.test(widgetStore.form.customer.phone)
	) {
		phoneError.value = wTrans('widget.enter-valid-phone').value;
	}
	let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	if (
		widgetStore.form.customer.email === null ||
		widgetStore.form.customer.email.length < 6 ||
		!emailPattern.test(widgetStore.form.customer.email)
	) {
		emailError.value = wTrans('widget.enter-valid-email').value;
	}
	if((agreements.generalTerms?.required || agreements.privacyPolicy?.required) && !widgetStore.form.customer.generalTerms){
		consentErrors.value['consents.general_terms'] = "widget.consent-is-required"
	}

	if(agreements.marketingAgreement?.required && !widgetStore.form.customer.marketingAgreement){
		consentErrors.value['consents.marketing_agreement'] = "widget.consent-is-required"
	}
	if (
		phoneError.value === null &&
		emailError.value === null &&
		firstNameError.value === null &&
		lastNameError.value === null
	) {
		emit('submit');
	}
}

const widgetStore = useWidgetStore();

const { phone, formatPhone } = useWidgetPhoneFormatting();

watch(
	() => phone.value,
	() => {
		widgetStore.form.customer.phone = phone.value;
	},
);


const agreements: { [key: string]: Agreement | undefined } = {
	generalTerms: widgetStore.agreements?.find(
		(agreement: Agreement) =>
			agreement.type === 0 &&
			((agreement.content_type === 0 && agreement.file !== null) ||
				(agreement.content_type === 1 && agreement.text !== null && agreement.text !== '')),
	),
	privacyPolicy: widgetStore.agreements?.find(
		(agreement: Agreement) =>
			agreement.type === 1 &&
			((agreement.content_type === 0 && agreement.file !== null) ||
				(agreement.content_type === 1 && agreement.text !== null && agreement.text !== '')),
	),
	marketingAgreement: widgetStore.agreements?.find(
		(agreement: Agreement) =>
			agreement.type === 2 &&
			((agreement.content_type === 0 && agreement.file !== null) ||
				(agreement.content_type === 1 && agreement.text !== null && agreement.text !== '')),
	),
};
</script>

<style scoped>
.consent {
	@apply flex w-72 items-center space-x-2 py-1 text-xxs;
}

.widget-input-group {
	@apply mx-auto;
}

.agreement-link {
	@apply underline;
}
</style>
