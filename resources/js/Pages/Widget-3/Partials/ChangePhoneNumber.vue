<template>
	<div
		class="login-container text-widget flex h-[300px] w-full flex-col items-center justify-center rounded-md border border-[3px] p-5">
		<p class="mb-3 text-center">
			{{ $t('widget.change-phone-number-info') }}
		</p>
		<form class="text-widget flex flex-col items-center pb-3 pt-2.5" @submit.prevent="changePhoneNumber">
			<div class="widget-input-group">
				<div class="flex">
					<TextInput v-model="form.email" :placeholder="$t('widget.email')" class="widget-input" />
					<div
						v-if="form.errors['email']"
						v-tippy
						:content="$t(form.errors['email'], { attribute: $t('validation.attributes.email'), max: '100' })"
						class="mt-1.25">
						<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
					</div>
				</div>
			</div>
			<div class="widget-input-group">
				<div class="flex">
					<TextInput
						v-model="form.password"
						:placeholder="$t('widget.password')"
						autocomplete="new-password"
						class="widget-input"
						type="password" />
					<div
						v-if="form.errors['password']"
						v-tippy
						:content="
							$t(form.errors['password'], { attribute: $t('validation.attributes.password'), max: '100' })
						"
						class="mt-1.25">
						<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
					</div>
					<div v-else-if="formDataInvalid" v-tippy :content="$t('widget.login-data-invalid')" class="mt-1.25">
						<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
					</div>
				</div>
			</div>
			<div class="widget-input-group">
				<div class="flex">
					<TextInput
						v-model="phone"
						:placeholder="$t('widget.phone-short')"
						@input="formatPhone"
						class="widget-input" />
					<div
						v-if="form.errors['phone']"
						v-tippy
						:content="
							$t(form.errors['phone'], { attribute: $t('validation.attributes.phone'), min: '7', max: '100' })
						"
						class="mt-1.25">
						<InfoIcon class="ml-1 h-3 w-3 text-danger-dark" />
					</div>
				</div>
			</div>

			<button class="widget-button mt-3 !justify-center" @click="changePhoneNumber">
				{{ $t('widget.change-phone-number') }}
			</button>
			<button class="widget-button mt-3 !justify-center" @click="cancelChangePhoneNumber">
				{{ capitalize($t('main.action.cancel')) }}
			</button>
		</form>
	</div>
</template>

<script lang="ts" setup>
import { useWidgetChangePhone } from '@/Composables/useWidgetChangePhone';
import TextInput from '@/Components/Widget-3/TextInput.vue';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';
import { capitalize } from 'vue';

const emit = defineEmits<{
	(e: 'reloadCustomer'): void;
	(e: 'changePhoneNumber'): void;
}>();

const changePhoneNumber = async () => {
	if (await changePhoneNumberImport()) {
		emit('reloadCustomer');
		emit('changePhoneNumber');
	}
};

const cancelChangePhoneNumber = () => {
	emit('changePhoneNumber');
};

const { form, phone, formatPhone, changePhoneNumber: changePhoneNumberImport } = useWidgetChangePhone();
</script>

<style scoped>
.widget-input-group,
.widget-input-group .widget-input {
	width: 12rem !important;
}
</style>
