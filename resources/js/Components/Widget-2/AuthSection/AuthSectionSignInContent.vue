<template>
	<form class="flex h-full flex-col gap-y-2.5" @submit.prevent="handleFormSubmit">
		<Widget2InputWrapper
			v-slot="{ error }"
			:error="
				form.errors.email &&
				$t(form.errors.email, { attribute: $t('validation.attributes.email'), max: '100' })
			">
			<Widget2Input type="email" :placeholder="$t('widget-2.email')" v-model="form.email" :error="error" />
		</Widget2InputWrapper>
		<Widget2InputWrapper
			v-slot="{ error }"
			:error="
				invalidDataErrorMessage ||
				(form.errors.password &&
					$t(form.errors.password, { attribute: $t('validation.attributes.password'), max: '100' }))
			">
			<Widget2Input
				type="password"
				:placeholder="$t('widget-2.password')"
				v-model="form.password"
				:error="error" />
		</Widget2InputWrapper>
		<button
			type="button"
			class="ml-auto block text-xs text-ui-green-950 underline"
			@click="onForgotPasswordClick">
			{{ $t('widget-2.forgot-password') }}
		</button>
		<div class="!mt-auto">
			<WidgetButton type="submit" size="compact-md" fill>{{ $t('widget-2.sign-in') }}</WidgetButton>
		</div>
	</form>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import Widget2Input from '../Widget2Input.vue';
import Widget2InputWrapper from '../Widget2InputWrapper.vue';
import { useWidgetSignInForm } from '@/Composables/useWidgetSignInForm';

defineProps<{
	onForgotPasswordClick?: () => void;
}>();

const { form, invalidDataErrorMessage, handleFormSubmit } = useWidgetSignInForm();
</script>
