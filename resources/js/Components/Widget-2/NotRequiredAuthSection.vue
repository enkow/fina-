<template>
	<div>
		<h2 class="my-0 text-center text-lg font-semibold">{{ $t('widget-2.enter-your-order-details') }}</h2>
		<form class="space-y-3" @submit.prevent="handleFormSubmit">
			<Widget2InputWrapper :error="formErrors.firstName" v-slot="{ error }">
				<Widget2Input
					v-model="widgetStore.form.customer.first_name"
					:placeholder="$t('widget-2.first-name')"
					:error="error" />
			</Widget2InputWrapper>
			<Widget2InputWrapper :error="formErrors.lastName" v-slot="{ error }">
				<Widget2Input
					v-model="widgetStore.form.customer.last_name"
					:placeholder="$t('widget-2.last-name')"
					:error="error" />
			</Widget2InputWrapper>
			<Widget2InputWrapper :error="formErrors.email" v-slot="{ error }">
				<Widget2Input
					type="email"
					v-model="widgetStore.form.customer.email"
					:placeholder="$t('widget-2.email')"
					:error="error" />
			</Widget2InputWrapper>
			<Widget2InputWrapper :error="formErrors.phone" v-slot="{ error }">
				<Widget2Input
					v-model="phone"
					@input="formatPhone"
					:placeholder="$t('widget-2.phone-short')"
					:error="error" />
			</Widget2InputWrapper>
			<WidgetButton type="submit" size="compact-md" fill>{{ $t('widget-2.continue') }}</WidgetButton>
		</form>
	</div>
</template>

<script lang="ts" setup>
import Widget2InputWrapper from './Widget2InputWrapper.vue';
import Widget2Input from './Widget2Input.vue';
import WidgetButton from '../Widget/Ui/WidgetButton.vue';
import { useWidgetNotRequiredRegistrationForm } from '@/Composables/useWidgetNotRequiredRegistrationForm';
import { useWidgetPhoneFormatting } from '@/Composables/useWidgetPhoneFormatting';
import { watch } from 'vue';

const emit = defineEmits<{
	(event: 'submit'): void;
}>();

const { formErrors, widgetStore, handleFormSubmit } = useWidgetNotRequiredRegistrationForm({
	onSuccess: () => emit('submit'),
});

const { phone, formatPhone } = useWidgetPhoneFormatting();

watch(
	() => phone.value,
	() => {
		widgetStore.form.customer.phone = phone.value;
	},
);
</script>
