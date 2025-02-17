<template>
	<WidgetModal :is-open="isOpen" :on-close="handleModalClose" :close-button="success">
		<WidgetModalTitle>{{ $t('calendar.forgot-password') }}</WidgetModalTitle>
		<form v-if="!success" class="space-y-3.5" @submit.prevent="handleFormSubmit">
			<WidgetInputWrapper
				:error="
					form.errors.email &&
					$t(form.errors.email, {
						attribute: $t('validation.attributes.email'),
					})
				">
				<WidgetInput type="email" v-model="form.email" :placeholder="$t('calendar.email')" />
			</WidgetInputWrapper>
			<WidgetButton type="submit" fill>{{ $t('calendar.continue') }}</WidgetButton>
		</form>
		<div class="space-y-3 text-center" v-else>
			<p class="text-ui-black/60">{{ $t('widget.forgot-password-success-content') }}</p>
		</div>
	</WidgetModal>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import WidgetModal from '@/Components/Widget/Ui/WidgetModal/WidgetModal.vue';
import WidgetModalTitle from '@/Components/Widget/Ui/WidgetModal/WidgetModalTitle.vue';
import WidgetInput from '@/Components/Widget/Ui/WidgetInput.vue';
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import { watch } from 'vue';
import { useWidgetForgotPasswordForm } from '@/Composables/useWidgetForgotPasswordForm';

const { onClose } = defineProps<{
	isOpen: boolean;
	onClose: () => void;
}>();

const { form, success, handleFormSubmit } = useWidgetForgotPasswordForm();

watch(
	() => form.email,
	() => form.clearErrors(),
);

const handleModalClose = () => {
	onClose();
	setTimeout(() => (success.value = false), 200);
};
</script>
