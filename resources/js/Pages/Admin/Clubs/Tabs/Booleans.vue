<template>
	<div class="grid grid-cols-6 gap-x-4 gap-y-10">
		<div v-for="booleanInput in booleanInputs" class="input-group flex flex-col text-center">
			<InputLabel :value="booleanInput.label" class="mx-auto mb-6" for="panel_enabled" />
			<div
				class="cursor-pointer"
				@click="
					router.visit(
						route('admin.clubs.toggle-field', {
							club: club,
							field: booleanInput.field,
						}),
						{ method: 'post' },
					)
				">
				<SuccessSquareIcon v-if="club[booleanInput.field]" class="mx-auto" />
				<DangerSquareIcon v-else class="mx-auto" />
			</div>
		</div>
		<br />
		<div class="col-span-2 flex flex-col items-center">
			<InputLabel class="mb-6" for="online_payments_enabled" value="Płatności online" />
			<SimpleSelect
				v-model="club.online_payments_enabled"
				:options="[
					{ code: 'external', label: 'Zewnętrzne' },
					{ code: 'internal', label: 'Systemowe' },
					{ code: 'disabled', label: 'Wyłączone' },
				]"
				class="self-stretch"
				@update:modelValue="
					(value) =>
						router.post(
							route('admin.clubs.set-field', {
								club: club,
								field: 'online_payments_enabled',
							}),
							{ value: value },
						)
				" />
		</div>
		<div class="col-span-2 flex flex-col items-center">
			<InputLabel class="mb-6" for="online_payments_provider" value="Operator płatności" />
			<SimpleSelect
				:disabled="club.online_payments_enabled === 'disabled'"
				v-model="payment_method"
				:options="
					club.online_payments_enabled === 'disabled'
						? [{ code: '', label: 'Stripe' }]
						: [
								{ code: 'stripe', label: 'Stripe' },
								{ code: 'tpay', label: 'Tpay' },
						  ]
				"
				class="self-stretch"
				@update:modelValue="
					(value) =>
						router.post(
							route('admin.clubs.payment-method-select', {
								club: club,
								field: 'online_payments_provider',
							}),
							{ value: value },
						)
				" />
		</div>
		<div class="col-span-2 flex flex-col items-center">
			<InputLabel class="mb-6" for="customer_verification_type" value="Sposób weryfikacji" />
			<SimpleSelect
				v-model="club.customer_verification_type"
				:options="[
					{ code: 0, label: 'Mail' },
					{ code: 1, label: 'Sms' },
				]"
				class="self-stretch"
				@update:modelValue="
					(value) =>
						router.post(
							route('admin.clubs.set-field', {
								club: club,
								field: 'customer_verification_type',
							}),
							{ value: value },
						)
				" />
		</div>
	</div>
</template>
<script lang="ts" setup>
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import { router } from '@inertiajs/vue3';
import SuccessSquareIcon from '@/Components/Dashboard/Icons/SuccessSquareIcon.vue';
import DangerSquareIcon from '@/Components/Dashboard/Icons/DangerSquareIcon.vue';
import { Club } from '@/Types/models';
import { ref, Ref } from 'vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';

const props = defineProps<{
	club: Club;
}>();

let payment_method = ref(props.club?.paymentMethod?.type || '');

const booleanInputs: Ref<
	Array<{
		field: string;
		label: string;
	}>
> = ref([
	{ field: 'panel_enabled', label: 'Dostęp do panelu' },
	{ field: 'widget_enabled', label: 'Widget' },
	{ field: 'calendar_enabled', label: 'Kalendarz' },
	{ field: 'sets_enabled', label: 'Zestawy' },
	{ field: 'aggregator_enabled', label: 'Widoczny w agregatorze' },
	{ field: 'offline_payments_enabled', label: 'Płatności offline' },
	{ field: 'customer_registration_required', label: 'Wymagana rejestracja' },
	{ field: 'preview_mode', label: 'Status preview' },
	{ field: 'sms_notifications_online', label: 'SMSy online' },
	{ field: 'sms_notifications_offline', label: 'SMSy offline' },
]);
</script>
