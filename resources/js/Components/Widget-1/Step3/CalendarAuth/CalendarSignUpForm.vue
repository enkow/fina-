<template>
    <CalendarAuthForm
        :button-title="$t('calendar.sign-up')"
        @submit="handleFormSubmit"
    >
        <WidgetInputWrapper
            :error="
                form.errors.first_name &&
                $t(form.errors.first_name, {
                    attribute: $t('validation.attributes.first_name'),
                    min: '7',
                    max: '100',
                })
            "
        >
            <WidgetInput
                v-model="form.first_name"
                color="white"
                :placeholder="$t('calendar.first-name')"
            />
        </WidgetInputWrapper>
        <WidgetInputWrapper
            :error="
                form.errors.last_name &&
                $t(form.errors.last_name, {
                    attribute: $t('validation.attributes.last_name'),
                    min: '7',
                    max: '100',
                })
            "
        >
            <WidgetInput
                v-model="form.last_name"
                color="white"
                :placeholder="$t('calendar.last-name')"
            />
        </WidgetInputWrapper>
        <WidgetInputWrapper
            :error="
                form.errors.email &&
                $t(form.errors.email, {
                    attribute: $t('validation.attributes.email'),
                    min: '7',
                    max: '100',
                })
            "
        >
            <WidgetInput
                v-model="form.email"
                type="email"
                color="white"
                :placeholder="$t('calendar.email')"
            />
        </WidgetInputWrapper>
        <WidgetInputWrapper
            :error="
                form.errors.phone &&
                $t(form.errors.phone, {
                    attribute: $t('validation.attributes.phone'),
                    min: '7',
                    max: '100',
                })
            "
        >
            <WidgetPhoneNumberInput
                v-model="phone"
                :countries="widgetStore.props.countries as Country[]"
                color="white"
            />
        </WidgetInputWrapper>

        <WidgetInputWrapper
            :error="
                form.errors.password &&
                $t(form.errors.password, {
                    attribute: $t('validation.attributes.password'),
                    min: '7',
                    max: '100',
                })
            "
        >
            <WidgetPasswordInput
                v-model="form.password"
                color="white"
                :placeholder="$t('calendar.password')"
            />
        </WidgetInputWrapper>

        <CalendarAuthConsents v-model:general_terms="consents.general_terms" v-model:marketing_agreement="consents.marketing_agreement"  :errors="consentsErrors" />
    </CalendarAuthForm>
</template>

<script lang="ts" setup>
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import CalendarAuthForm from '@/Components/Widget-1/Shared/CalendarAuthForm/CalendarAuthForm.vue';
import WidgetPasswordInput from '@/Components/Widget/Ui/WidgetPasswordInput.vue';
import WidgetInput from '@/Components/Widget/Ui/WidgetInput.vue';
import { useWidgetSignUpForm } from '@/Composables/useWidgetSignUpForm';
import WidgetPhoneNumberInput from '@/Components/Widget/Ui/WidgetPhoneNumberInput.vue';
import { useWidgetStore } from '@/Stores/widget';
import { Country } from '@/Types/models';
import CalendarAuthConsents from '../../Shared/CalendarAuthForm/CalendarAuthConsents.vue';

const { form, handleFormSubmit, consentsErrors, phone, consents } = useWidgetSignUpForm();
const widgetStore = useWidgetStore();
</script>
