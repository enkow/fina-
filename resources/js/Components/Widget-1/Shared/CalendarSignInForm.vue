<template>
	<CalendarAuthForm :button-title="$t('calendar.sign-in')" @submit="handleFormSubmit">
		<WidgetInputWrapper
			:error="
				form.errors.email &&
				$t(form.errors.email, { attribute: $t('validation.attributes.email'), max: '100' })
			">
			<WidgetInput
				type="email"
				:color="inputColor"
				v-model="form.email"
				:placeholder="$t('calendar.email')" />
		</WidgetInputWrapper>
		<WidgetInputWrapper
			:error="
				invalidDataErrorMessage ||
				(form.errors.password &&
					$t(form.errors.password, { attribute: $t('validation.attributes.password'), max: '100' }))
			">
			<WidgetInput
				type="password"
				:color="inputColor"
				v-model="form.password"
				:placeholder="$t('calendar.password')" />
		</WidgetInputWrapper>
		<button
			type="button"
			class="ml-auto block w-fit font-medium text-ui-green-950 underline"
			@click="onForgotPasswordClick">
			{{ $t('calendar.forgot-password') }}
		</button>
	</CalendarAuthForm>
</template>

<script lang="ts" setup>
import CalendarAuthForm from '@/Components/Widget-1/Shared/CalendarAuthForm/CalendarAuthForm.vue';
import WidgetInput from '@/Components/Widget/Ui/WidgetInput.vue';
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import { useWidgetSignInForm } from '@/Composables/useWidgetSignInForm';

defineProps<{
	inputColor: 'white' | 'green';
	onForgotPasswordClick?: () => void;
}>();

const { form, invalidDataErrorMessage, handleFormSubmit } = useWidgetSignInForm();
</script>
