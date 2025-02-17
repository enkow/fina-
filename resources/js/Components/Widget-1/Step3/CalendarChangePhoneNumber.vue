<template>
	<CalendarSectionTitle>{{ $t('widget.change-phone-number') }}</CalendarSectionTitle>
	<div class="space-y-4 text-center text-ui-text">
		<p>
			{{ $t('widget.change-phone-number-info') }}
		</p>

		<WidgetInputWrapper
			:error="
				form.errors.email &&
				$t(form.errors.email, { attribute: $t('validation.attributes.email'), min: '7', max: '100' })
			">
			<WidgetInput v-model="form.email" type="email" color="white" :placeholder="$t('calendar.email')" />
		</WidgetInputWrapper>
		<WidgetInputWrapper
			:error="
				form.errors.password &&
				$t(form.errors.password, { attribute: $t('validation.attributes.password'), min: '7', max: '100' })
			">
			<WidgetPasswordInput v-model="form.password" color="white" :placeholder="$t('calendar.password')" />
		</WidgetInputWrapper>
		<WidgetInputWrapper
			:error="
				form.errors.phone &&
				$t(form.errors.phone, { attribute: $t('validation.attributes.phone'), min: '7', max: '100' })
			">
			<WidgetInput v-model="phone" @input="formatPhone" color="white" placeholder="000 000 000" />
		</WidgetInputWrapper>

		<WidgetButton color="green" @click="changePhoneNumber" fill v-text="$t('widget.change-phone-number')" />

		<WidgetButton
			color="green"
			@click="$emit('changePhoneNumber')"
			fill
			v-text="capitalize($t('main.action.cancel'))" />
	</div>
</template>

<script lang="ts" setup>
import CalendarSectionTitle from './CalendarSectionTitle.vue';
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import WidgetInput from '@/Components/Widget/Ui/WidgetInput.vue';
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import { useWidgetChangePhone } from '@/Composables/useWidgetChangePhone';
import WidgetPasswordInput from '@/Components/Widget/Ui/WidgetPasswordInput.vue';
import { capitalize } from 'vue';

const emit = defineEmits(['changePhoneNumber']);
const changePhoneNumber = async () => {
	if (await changePhoneNumberImport()) {
		emit('changePhoneNumber');
	}
};

const { form, phone, formatPhone, changePhoneNumber: changePhoneNumberImport } = useWidgetChangePhone();
</script>
