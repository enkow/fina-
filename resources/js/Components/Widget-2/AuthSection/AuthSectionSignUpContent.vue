<template>
	<form class="flex h-full flex-col gap-y-2.5" @submit.prevent="handleFormSubmit">
		<Widget2InputWrapper
			v-slot="{ error }"
			:error="
				form.errors.first_name &&
				$t(form.errors.first_name, {
					attribute: $t('validation.attributes.first_name'),
					min: '7',
					max: '100',
				})
			">
			<Widget2Input v-model="form.first_name" :error="error" :placeholder="$t('widget-2.first-name')" />
		</Widget2InputWrapper>
		<Widget2InputWrapper
			v-slot="{ error }"
			:error="
				form.errors.last_name &&
				$t(form.errors.last_name, { attribute: $t('validation.attributes.last_name'), min: '7', max: '100' })
			">
			<Widget2Input v-model="form.last_name" :error="error" :placeholder="$t('widget-2.last-name')" />
		</Widget2InputWrapper>
		<Widget2InputWrapper
			v-slot="{ error }"
			:error="
				form.errors.email &&
				$t(form.errors.email, { attribute: $t('validation.attributes.email'), min: '7', max: '100' })
			">
			<Widget2Input type="email" v-model="form.email" :error="error" :placeholder="$t('widget-2.email')" />
		</Widget2InputWrapper>
		<Widget2InputWrapper
			v-slot="{ error }"
			:error="
				form.errors.phone &&
				$t(form.errors.phone, { attribute: $t('validation.attributes.phone'), min: '7', max: '100' })
			">
			<Widget2PhoneInput
				v-model="phone"
				@input="formatPhone"
				:countries="widgetStore.props.countries as Country[]"
				:error="error" />
		</Widget2InputWrapper>
		<Widget2InputWrapper
			v-slot="{ error }"
			:error="
				form.errors.password &&
				$t(form.errors.password, { attribute: $t('validation.attributes.password'), min: '7', max: '100' })
			">
			<Widget2PasswordInput v-model="form.password" :error="error" :placeholder="$t('widget-2.password')" />
		</Widget2InputWrapper>
		<div class="mt-auto flex flex-col gap-2.5 md:flex-row md:items-center">
			<div class="md:w-fit">
				<WidgetButton type="submit" size="compact-md" fill>{{ $t('widget-2.sign-up') }}</WidgetButton>
			</div>
			<div class="space-y-0.5 md:flex-1">
				<Widget2InputWrapper
					v-if="agreements.generalTerms || agreements.privacyPolicy"
					v-slot="{ error }"
					:error="
						((form.errors as ConsentError)['consents.general_terms'] &&
							$t((form.errors as ConsentError)['consents.general_terms'])) ||
						((form.errors as ConsentError)['consents.privacy_policy'] &&
							$t((form.errors as ConsentError)['consents.privacy_policy']))
					">
					<WidgetCheckbox :error="error" size="sm" class="!text-xs" v-model="generalTermsAndPrivacyPolicy">
						<template #label>
							{{ $t('widget-2.accept') }}
							<WidgetUnstyledFormAgreement
								v-if="agreements.generalTerms"
								:agreement="agreements.generalTerms">
								{{ $t('widget-2.terms') }}
							</WidgetUnstyledFormAgreement>
							<template v-if="agreements.generalTerms && agreements.privacyPolicy">
								{{ ` ${$t('widget-2.and')} ` }}
							</template>
							<WidgetUnstyledFormAgreement
								v-if="agreements.privacyPolicy"
								:agreement="agreements.privacyPolicy">
								{{ $t('widget-2.privacy-policy') }}
							</WidgetUnstyledFormAgreement>
						</template>
					</WidgetCheckbox>
				</Widget2InputWrapper>
				<Widget2InputWrapper
					v-if="agreements.marketingAgreement"
					v-slot="{ error }"
					:error="
						(form.errors as ConsentError)['consents.marketing_agreement'] &&
						$t((form.errors as ConsentError)['consents.marketing_agreement'])
					">
					<WidgetCheckbox :error="error" size="sm" v-model="form.marketing_agreement" class="!text-xs">
						<template #label>
							{{ $t('widget-2.accept') }}
							<WidgetUnstyledFormAgreement :agreement="agreements.marketingAgreement">
								{{ $t('widget-2.marketing-consent') }}
							</WidgetUnstyledFormAgreement>
						</template>
					</WidgetCheckbox>
				</Widget2InputWrapper>
			</div>
		</div>
	</form>
</template>

<script lang="ts" setup>
import Widget2Input from '../Widget2Input.vue';
import Widget2PasswordInput from '../Widget2PasswordInput.vue';
import Widget2PhoneInput from '../Widget2PhoneInput.vue';
import Widget2InputWrapper from '../Widget2InputWrapper.vue';
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import WidgetCheckbox from '@/Components/Widget/Ui/WidgetCheckbox.vue';
import WidgetUnstyledFormAgreement from '@/Components/Widget/Unstyled/WidgetUnstyledFormAgreement.vue';
import { useWidgetSignUpForm } from '@/Composables/useWidgetSignUpForm';
import { useWidgetAgreements } from '@/Composables/useWidgetAgreements';
import { useWidgetStore } from '@/Stores/widget';
import { Country } from '@/Types/models';

const widgetStore = useWidgetStore();
const agreements = useWidgetAgreements();
const { form, generalTermsAndPrivacyPolicy, handleFormSubmit, phone, formatPhone } = useWidgetSignUpForm();

type Consent = 'general_terms' | 'privacy_policy' | 'marketing_agreement';
type ConsentError = Record<`consents.${Consent}`, string>;
</script>
