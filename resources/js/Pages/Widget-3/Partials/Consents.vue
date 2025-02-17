<template>
	<div>
		<form
			class="login-container flex h-[300px] w-full flex-col items-center justify-center rounded-md border border-[3px] p-5"
			@submit.prevent="submit">
			<p class="widget-header !mb-6">{{ $t('widget.required-consents') }}</p>
			<div v-if="agreementsStatuses['generalTerms'] && agreementsStatuses['privacyPolicy']" class="consent">
				<Checkbox
					id="termsAndPrivacyPolicyCheckbox"
					v-model="generalTermsAndPrivacyPolicy"
					:class="{
						'border-2 !border-danger-base':
							(form.errors['general_terms'] || form.errors['privacy_policy']) &&
							!generalTermsAndPrivacyPolicy,
					}"
					class="text-widget !h-4 !w-4" />
				<label class="flex space-x-1" for="termsAndPrivacyPolicyCheckbox">
					<p>{{ $t('widget.agreements.accept') }}</p>
					<a
						v-if="agreementsStatuses['generalTerms']['content_type'] === 0"
						:href="'/club-assets/agreements/' + agreementsStatuses['generalTerms']['file']"
						class="agreement-link"
						target="_blank">
						{{ $t('widget.agreements.terms') }}
					</a>
					<p v-else v-tippy :content="agreementsStatuses['generalTerms']['text']">
						{{ $t('widget.agreements.terms') }}
					</p>
					<p>{{ $t('widget.agreements.and') }}</p>
					<a
						v-if="agreementsStatuses['privacyPolicy']['content_type'] === 0"
						:href="'/club-assets/agreements/' + agreementsStatuses['privacyPolicy']['file']"
						class="agreement-link"
						target="_blank">
						{{ $t('widget.agreements.privacy-policy') }}
					</a>
					<p v-else v-tippy :content="agreementsStatuses['privacyPolicy']['text']">
						{{ $t('widget.agreements.privacy-policy') }}
					</p>
				</label>
			</div>
			<div v-else-if="agreementsStatuses['generalTerms']" class="consent">
				<Checkbox
					id="termsCheckbox"
					v-model="form.general_terms"
					:class="{
						'border-2 !border-danger-base': form.errors['general_terms'] && !form.general_terms,
					}"
					class="text-widget !h-4 !w-4" />
				<label class="flex space-x-1" for="termsCheckbox">
					<p>{{ $t('widget.agreements.accept') }}</p>
					<a
						v-if="agreementsStatuses['generalTerms']['content_type'] === 0"
						:href="'/club-assets/agreements/' + agreementsStatuses['generalTerms']['file']"
						class="agreement-link"
						target="_blank">
						{{ $t('widget.agreements.terms') }}
					</a>
					<p v-else v-tippy :content="agreementsStatuses['generalTerms']['text']">
						{{ $t('widget.agreements.terms') }}
					</p>
				</label>
			</div>
			<div v-else-if="agreementsStatuses['privacyPolicy']" class="consent">
				<Checkbox
					id="privacyPolicyCheckbox"
					v-model="form.privacy_policy"
					:class="{
						'border-2 !border-danger-base': form.errors['privacy_policy'] && !form.privacy_policy,
					}"
					class="text-widget !h-4 !w-4" />
				<label class="flex space-x-1" for="privacyPolicyCheckbox">
					<p>{{ $t('widget.agreements.accept') }}</p>
					<a
						v-if="agreementsStatuses['privacyPolicy']['content_type'] === 0"
						:href="'/club-assets/agreements/' + agreementsStatuses['privacyPolicy']['file']"
						class="agreement-link"
						target="_blank">
						{{ $t('widget.agreements.privacy-policy') }}
					</a>
					<p v-else v-tippy :content="agreementsStatuses['privacyPolicy']['text']">
						{{ $t('widget.agreements.privacy-policy') }}
					</p>
				</label>
			</div>
			<div v-if="agreementsStatuses['marketingAgreement']" class="consent">
				<Checkbox
					id="marketingCheckbox"
					v-model="form.marketing_agreement"
					:class="{
						'border-2 !border-danger-base': form.errors['marketing_agreement'] && !form.marketing_agreement,
					}"
					class="text-widget !h-4 !w-4" />
				<label class="flex space-x-1" for="marketingCheckbox">
					<p>{{ $t('widget.agreements.accept') }}</p>
					<a
						v-if="agreementsStatuses['marketingAgreement']['content_type'] === 0"
						:href="'/club-assets/agreements/' + agreementsStatuses['marketingAgreement']['file']"
						class="agreement-link"
						target="_blank">
						{{ $t('widget.agreements.marketing-agreement') }}
					</a>
					<p v-else v-tippy :content="agreementsStatuses['marketingAgreement']['text']">
						{{ $t('widget.agreements.marketing-agreement') }}
					</p>
				</label>
			</div>
			<div class="mt-4 space-y-0.5">
				<button class="widget-button w-48 justify-center" type="submit">
					<p>{{ $t('widget.send') }}</p>
				</button>
			</div>
		</form>
	</div>
</template>

<style scoped>
.consent {
	@apply flex items-center space-x-2 py-0.75 text-sm;
}

.widget-input-group {
	@apply mx-auto;
}

.agreement-link {
	@apply underline;
}
</style>

<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';
import { Agreement } from '@/Types/models';
import { useWidgetStore } from '@/Stores/widget';
import { ref, watch } from 'vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';

const emit = defineEmits<{
	(e: 'reloadCustomer'): void;
}>();

const widgetStore = useWidgetStore();
const generalTermsAndPrivacyPolicy = ref<boolean>(false);
const agreementsStatuses: { [key: string]: Agreement | undefined } = {
	generalTerms: widgetStore.customer.agreements_to_consent?.find(
		(agreement: Agreement) =>
			agreement.type === 0 &&
			((agreement.content_type === 0 && agreement.file !== null) ||
				(agreement.content_type === 1 && agreement.text !== null && agreement.text !== '')),
	),
	privacyPolicy: widgetStore.customer.agreements_to_consent?.find(
		(agreement: Agreement) =>
			agreement.type === 1 &&
			((agreement.content_type === 0 && agreement.file !== null) ||
				(agreement.content_type === 1 && agreement.text !== null && agreement.text !== '')),
	),
	marketingAgreement: widgetStore.customer.agreements_to_consent?.find(
		(agreement: Agreement) =>
			agreement.type === 2 &&
			((agreement.content_type === 0 && agreement.file !== null) ||
				(agreement.content_type === 1 && agreement.text !== null && agreement.text !== '')),
	),
};

const form = useForm({
	club_slug: widgetStore.club.slug,
	general_terms: false,
	privacy_policy: false,
	marketing_agreement: false,
});

function submit() {
	form.post(route('widget.customers.update-consents', { club: widgetStore.club }), {
		onSuccess: () => {
			emit('reloadCustomer');
		},
		preserveScroll: true,
		preserveState: true,
	});
}

watch(generalTermsAndPrivacyPolicy, () => {
	form.general_terms = generalTermsAndPrivacyPolicy.value;
	form.privacy_policy = generalTermsAndPrivacyPolicy.value;
});
</script>
