<template>
	<div class="flex h-full flex-col justify-center">
		<p v-if="success" class="text-center text-sm text-ui-black/60">
			{{ $t('widget-2.forgot-password-success') }}
		</p>
		<form class="space-y-2" @submit.prevent="handleFormSubmit" v-else>
			<h2 class="my-0 text-center text-base font-semibold">{{ $t('widget-2.forgot-password') }}</h2>
			<p class="text-center text-xs text-ui-black/60">
				{{ $t('widget-2.forgot-password-content') }}
			</p>
			<Widget2InputWrapper
				:error="
					form.errors.email &&
					$t(form.errors.email, {
						attribute: $t('validation.attributes.email'),
					})
				"
				v-slot="{ error }">
				<Widget2Input type="email" v-model="form.email" :placeholder="$t('widget-2.email')" :error="error" />
			</Widget2InputWrapper>
			<div class="!mt-4 text-sm">
				<WidgetButton type="submit" size="compact-md" fill>{{ $t('widget-2.send-link') }}</WidgetButton>
			</div>
		</form>
		<button type="button" class="mx-auto mt-3 flex items-center gap-x-1.5 text-xs" @click="onBackClick">
			<ArrowRightIcon class="h-2.5 w-2.5 rotate-180" />
			{{ $t('widget-2.back-to-login') }}
		</button>
	</div>
</template>

<script lang="ts" setup>
import Widget2InputWrapper from '../Widget2InputWrapper.vue';
import Widget2Input from '../Widget2Input.vue';
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import ArrowRightIcon from '../Icons/ArrowRightIcon.vue';
import { useWidgetForgotPasswordForm } from '@/Composables/useWidgetForgotPasswordForm';

const { form, success, handleFormSubmit } = useWidgetForgotPasswordForm();

defineProps<{
	onBackClick?: () => void;
}>();
</script>
