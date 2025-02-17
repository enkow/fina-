<template>
	<form
		class="border-widget flex flex-col items-center pb-3 pt-2.5 sm:border-r-2"
		@submit.prevent="submitRegisterForm">
		<p class="widget-header !mb-0 hidden w-full text-center md:block">
			{{ $t('widget.do-not-have-account-question') }}
		</p>
		<p class="widget-header !mb-0 block w-full text-center md:hidden">
			{{ $t('widget.register') }}
		</p>
		<div
			:class="{
				'h-0 overflow-hidden': !formShowing,
				'h-fit': formShowing,
			}">
			<div class="widget-input-group">
				<div class="flex">
					<TextInput v-model="form.first_name" :placeholder="$t('widget.first-name')" class="widget-input" />
					<div
						v-if="form.errors['first_name']"
						v-tippy
						:content="
							$t(form.errors['first_name'], {
								attribute: $t('validation.attributes.first_name'),
								min: '7',
								max: '100',
							})
						"
						class="mt-1.25">
						<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
					</div>
				</div>
			</div>
			<div class="widget-input-group">
				<div class="flex">
					<TextInput v-model="form.last_name" :placeholder="$t('widget.last-name')" class="widget-input" />
					<div
						v-if="form.errors['last_name']"
						v-tippy
						:content="
							$t(form.errors['last_name'], {
								attribute: $t('validation.attributes.last_name'),
								min: '7',
								max: '100',
							})
						"
						class="mt-1.25">
						<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
					</div>
				</div>
			</div>
			<div class="widget-input-group">
				<div class="flex">
					<TextInput v-model="form.email" :placeholder="$t('widget.email')" class="widget-input" />
					<div
						v-if="form.errors['email']"
						v-tippy
						:content="
							$t(form.errors['email'], { attribute: $t('validation.attributes.email'), min: '7', max: '100' })
						"
						class="mt-1.25">
						<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
					</div>
				</div>
			</div>
			<div class="widget-input-group">
				<div class="flex">
					<TextInput
						v-model="phone"
						:placeholder="$t('widget.phone-short')"
						@input="formatPhone"
						class="widget-input" />
					<div
						v-if="form.errors['phone']"
						v-tippy
						:content="
							$t(form.errors['phone'], { attribute: $t('validation.attributes.phone'), min: '7', max: '100' })
						"
						class="mt-1.25">
						<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
					</div>
				</div>
			</div>
			<div class="widget-input-group">
				<div class="flex">
					<TextInput
						v-model="form.password"
						:placeholder="$t('widget.password')"
						:type="registerPasswordHiddenStatus ? 'password' : 'text'"
						autocomplete="new-password"
						class="widget-input" />
					<div class="-ml-4 mt-1.25 cursor-pointer" @click="toggleRegisterPasswordHiddenStatus">
						<EyeIcon v-if="registerPasswordHiddenStatus" />
						<EyeCrossedIcon v-else class="-ml-0.25" />
					</div>
					<div
						v-if="form.errors['password']"
						v-tippy
						:content="
							$t(form.errors['password'], {
								attribute: $t('validation.attributes.password'),
								min: '7',
								max: '100',
							})
						"
						class="mt-1.25">
						<InfoIcon class="ml-1.25 h-3 w-3 text-danger-dark" />
					</div>
				</div>
			</div>
			<div v-if="agreements['generalTerms'] && agreements['privacyPolicy']" class="consent mt-1">
				<Checkbox
					id="termsAndPrivacyPolicyCheckbox"
					v-model="generalTermsAndPrivacyPolicy"
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
								(form.errors['consents.general_terms'] || form.errors['consents.privacy_policy']) &&
								!generalTermsAndPrivacyPolicy
							"
							v-tippy
							:content="$t(form.errors['consents.general_terms'] ?? form.errors['consents.privacy_policy'])"
							class="-mb-0.75 inline-block">
							<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
						</a>
					</p>
				</label>
			</div>
			<div v-else-if="agreements['generalTerms']" class="consent">
				<Checkbox id="termsCheckbox" v-model="form.consents.general_terms" class="text-widget !h-4 !w-4" />
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
							v-if="form.errors['consents.general_terms']"
							v-tippy
							:content="$t(form.errors['consents.general_terms'])"
							class="-mb-0.75 inline-block">
							<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
						</a>
					</p>
				</label>
			</div>
			<div v-else-if="agreements['privacyPolicy']" class="consent">
				<Checkbox
					id="privacyPolicyCheckbox"
					v-model="form.consents.privacy_policy"
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
							v-if="form.errors['consents.privacy_policy']"
							v-tippy
							:content="$t(form.errors['consents.privacy_policy'])"
							class="-mb-0.75 inline-block">
							<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
						</a>
					</p>
				</label>
			</div>
			<div v-if="agreements['marketingAgreement']" class="consent">
				<Checkbox
					id="marketingCheckbox"
					v-model="form.consents.marketing_agreement"
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
							v-if="form.errors['consents.marketing_agreement']"
							v-tippy
							:content="$t(form.errors['consents.marketing_agreement'])"
							class="-mb-0.75 inline-block">
							<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
						</a>
					</p>
				</label>
			</div>
		</div>
		<div class="mt-1 w-full space-y-0.5 sm:mt-2">
			<button class="widget-button mx-auto w-48" type="submit">
				<p class="w-full">{{ $t('widget.register') }}</p>
			</button>
			<!--      <p class="widget-header !mb-0 w-full text-center block md:hidden !text-sm !mt-2">-->
			<!--        {{ $t("widget.have-account-question") }}-->
			<!--      </p>-->
			<button class="widget-button mx-auto w-48 sm:!hidden" @click.prevent="emit('showLogin')">
				<p class="w-full">{{ $t('widget.already-have-account') }}</p>
			</button>
		</div>
	</form>
</template>

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

<script lang="ts" setup>
import { computed, nextTick, ref, watch } from 'vue';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';
import { Agreement, Customer } from '@/Types/models';
import FacebookIcon from '@/Components/Widget-3/Icons/FacebookIcon.vue';
import { useForm } from '@inertiajs/vue3';
import { useWidgetStore } from '@/Stores/widget';
import TextInput from '@/Components/Widget-3/TextInput.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import EyeIcon from '@/Components/Widget-3/Icons/EyeIcon.vue';
import EyeCrossedIcon from '@/Components/Widget-3/Icons/EyeCrossedIcon.vue';
import axios from 'axios';
import { useWidgetPhoneFormatting } from '@/Composables/useWidgetPhoneFormatting';

const emit = defineEmits<{
	(e: 'showLogin'): void;
}>();

const widgetStore = useWidgetStore();

const formShowing = ref<boolean>(true);
const generalTermsAndPrivacyPolicy = ref<boolean>(false);

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
const { phone, formatPhone } = useWidgetPhoneFormatting();

const form = useForm({
	club_slug: widgetStore.club.slug,
	first_name: '',
	last_name: '',
	email: '',
	phone: '',
	password: '',
	widget_channel: widgetStore.props.channel,
	consents: {
		general_terms: false,
		privacy_policy: false,
		marketing_agreement: false,
	},
});

watch(phone, () => {
	form.phone = phone.value;
});

function submitRegisterForm(): void {
	if (formShowing.value) {
		axios
			.post(route('widget.customers.register', { club: widgetStore.club }), form)
			.then((response: { data: { customer: Customer } }) => {
				if (response.data?.customer ?? null) {
					widgetStore.customer = response.data.customer;
					form.reset();
				} else {
					form.errors = {};
				}
			})
			.catch((response: { response: { data: { errors: Object } } }) => {
				form.errors = {} as Record<string, any>;
				let errors: Record<string, any> = response.response.data.errors;
				Object.keys(errors).forEach((errorKey: string) => {
					errors[errorKey].forEach((error: string) => {
						form.errors[errorKey as keyof typeof form.errors] = error;
					});
				});
			});
	} else {
		formShowing.value = true;
	}
}

const registerPasswordHiddenStatus = ref<boolean>(true);

function toggleRegisterPasswordHiddenStatus(): void {
	registerPasswordHiddenStatus.value = !registerPasswordHiddenStatus.value;
}

watch(generalTermsAndPrivacyPolicy, () => {
	form.consents.general_terms = generalTermsAndPrivacyPolicy.value;
	form.consents.privacy_policy = generalTermsAndPrivacyPolicy.value;
});
</script>
