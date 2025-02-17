<template>
	<CalendarSectionTitle>{{ $t('calendar.pass-reservation-data') }}</CalendarSectionTitle>
	<form @submit.prevent="handleForm" class="space-y-2.5">
		<WidgetInputWrapper :error="formErrors.firstName">
			<WidgetInput
				v-model="widgetStore.form.customer.first_name"
				color="white"
				:placeholder="$t('calendar.first-name')" />
		</WidgetInputWrapper>
		<WidgetInputWrapper :error="formErrors.lastName">
			<WidgetInput
				v-model="widgetStore.form.customer.last_name"
				color="white"
				:placeholder="$t('calendar.last-name')" />
		</WidgetInputWrapper>
		<WidgetInputWrapper :error="formErrors.email">
			<WidgetInput
				type="email"
				v-model="widgetStore.form.customer.email"
				color="white"
				:placeholder="$t('calendar.email')" />
		</WidgetInputWrapper>
		<WidgetInputWrapper :error="formErrors.phone">
			<WidgetPhoneNumberInput v-model="phone"  :countries="widget.props.countries" color="white" />
		</WidgetInputWrapper>

		<CalendarAuthConsents v-model:general_terms="widgetStore.form.customer.generalTerms" v-model:marketing_agreement="widgetStore.form.customer.marketingAgreement" :errors="errors"  />

		<WidgetButton type="submit" :disabled="Object.keys(errors).length > 0">{{ $t('calendar.continue') }}</WidgetButton>
	</form>
</template>

<script lang="ts" setup>
import WidgetInput from '@/Components/Widget/Ui/WidgetInput.vue';
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import CalendarSectionTitle from './CalendarSectionTitle.vue';
import WidgetPhoneNumberInput from "@/Components/Widget/Ui/WidgetPhoneNumberInput.vue"
import { useWidgetNotRequiredRegistrationForm } from '@/Composables/useWidgetNotRequiredRegistrationForm';
import { useWidgetPhoneFormatting } from '@/Composables/useWidgetPhoneFormatting';
import { watch, ref } from 'vue';
import { useWidgetStore } from '@/Stores/widget';
import CalendarAuthConsents from '../Shared/CalendarAuthForm/CalendarAuthConsents.vue';
import { useWidgetAgreements } from '@/Composables/useWidgetAgreements';


const emit = defineEmits<{
	(event: 'submit'): void;
}>();

const { widgetStore, formErrors, handleFormSubmit } = useWidgetNotRequiredRegistrationForm({
	onSuccess: () => emit('submit'),
});

const { phone, formatPhone } = useWidgetPhoneFormatting();
const widget = useWidgetStore();
const agreements = useWidgetAgreements();

function handleForm(){
	consentErrors();

	if(Object.keys(errors.value).length > 0) return

	handleFormSubmit();
}

const errors = ref({})

function consentErrors() {
	errors.value = {};

	if((agreements.generalTerms?.required || agreements.privacyPolicy?.required) && !widgetStore.form.customer.generalTerms){
		errors.value['consents.general_terms'] = "widget.consent-is-required"
	}

	if(agreements.marketingAgreement?.required && !widgetStore.form.customer.marketingAgreement){
		errors.value['consents.marketing_agreement'] = "widget.consent-is-required"
	}
}

watch(widgetStore.form, () => {
	errors.value = {};
});

watch(
	() => phone.value,
	() => {
		widgetStore.form.customer.phone = phone.value;
	},
);
</script>
