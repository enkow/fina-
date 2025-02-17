<template>
    <WidgetInputWrapper :error="generalPrivacyError">
        <WidgetCheckbox
            v-if="agreements.generalTerms || agreements.privacyPolicy"
            :checked="general_terms"
            @update:checked="$emit('update:general_terms', $event)"
        >
            <template #label>
                {{ $t('calendar.accept') }}
                <WidgetUnstyledFormAgreement
                    v-if="agreements.generalTerms"
                    :agreement="agreements.generalTerms"
                >
                    {{ $t('calendar.statute') }}
                </WidgetUnstyledFormAgreement>
                <span
                    v-if="agreements.generalTerms && agreements.privacyPolicy"
                >
                    {{ ` ${$t('calendar.and')} ` }}
                </span>
                <WidgetUnstyledFormAgreement
                    v-if="agreements.privacyPolicy"
                    :agreement="agreements.privacyPolicy"
                >
                    {{ $t('calendar.privacy-policy') }}
                </WidgetUnstyledFormAgreement>

                <span
                    v-if="
                        agreements.generalTerms?.required ||
                        agreements.privacyPolicy?.required
                    "
                >
                    *
                </span>
            </template>
        </WidgetCheckbox>
    </WidgetInputWrapper>

    <WidgetInputWrapper :error="marketingError">
        <WidgetCheckbox
            v-if="agreements.marketingAgreement"
            :checked="marketing_agreement"
            @update:checked="$emit('update:marketing_agreement', $event)"
        >
            <template #label>
                {{ $t('calendar.accept') }}
                <WidgetUnstyledFormAgreement
                    :agreement="agreements.marketingAgreement"
                >
                    {{ $t('calendar.marketing-consent') }}
                </WidgetUnstyledFormAgreement>
                <span v-if="agreements.marketingAgreement.required"> * </span>
            </template>
        </WidgetCheckbox>
    </WidgetInputWrapper>
</template>

<script lang="ts" setup>
import WidgetCheckbox from '@/Components/Widget/Ui/WidgetCheckbox.vue';
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import { useWidgetAgreements } from '@/Composables/useWidgetAgreements';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';

type Consent = 'general_terms' | 'privacy_policy' | 'marketing_agreement';
type ConsentError = Record<`consents.${Consent}`, string>;

const props = defineProps<{
    errors: ConsentError; 
    general_terms: boolean;
    marketing_agreement: boolean;
}>();

const emit = defineEmits(['update:general_terms', 'update:marketing_agreement']);

const generalPrivacyError = computed(() => {
    const generalError = props.errors['consents.general_terms'];
    if (generalError) {
        return trans(generalError);
    }

    const privacyError = props.errors['consents.privacy_policy'];
    if (privacyError) {
        return privacyError;
    }

    return '';
});

const marketingError = computed(() => {
    const marketingError = props.errors['consents.marketing_agreement'];
    if (marketingError) {
        return trans(marketingError);
    }

    return '';
});

const agreements = useWidgetAgreements();
</script>
