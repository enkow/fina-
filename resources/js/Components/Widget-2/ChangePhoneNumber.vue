<template>
	<div
		class="widget-2-scrollbar flex h-full flex-col items-center space-y-3 overflow-auto py-2 text-center text-sm md:h-fit">
		<p>
			{{ $t('widget.change-phone-number-info') }}
		</p>
		<form class="flex h-full flex-col gap-y-2.5" @submit.prevent="changePhoneNumber">
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

			<div class="flex justify-between space-x-2">
				<WidgetButton
					size="compact-md"
					color="green"
					@click="changePhoneNumber"
					v-text="$t('widget.change-phone-number')"
					class="shrink-0" />
				<WidgetButton
					size="compact-md"
					color="green"
					@click="$emit('changePhoneNumber')"
					v-text="capitalize($t('main.action.cancel'))"
					class="shrink-0" />
			</div>
		</form>
	</div>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import Widget2InputWrapper from './Widget2InputWrapper.vue';
import Widget2Input from './Widget2Input.vue';
import { useWidgetStore } from '@/Stores/widget';
import Widget2PasswordInput from './Widget2PasswordInput.vue';
import Widget2PhoneInput from './Widget2PhoneInput.vue';
import { useWidgetChangePhone } from '@/Composables/useWidgetChangePhone';
import { capitalize } from 'vue';

const widgetStore = useWidgetStore();
const emit = defineEmits(['changePhoneNumber']);
const changePhoneNumber = async () => {
	if (await changePhoneNumberImport()) {
		emit('changePhoneNumber');
	}
};

const { form, phone, formatPhone, changePhoneNumber: changePhoneNumberImport } = useWidgetChangePhone();
</script>
