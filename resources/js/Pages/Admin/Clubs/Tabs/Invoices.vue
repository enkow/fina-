<template>
	<div class="grid grid-cols-6 gap-x-4 gap-y-10">
		<BooleanInput
			v-model:modelValue="form.invoice_autopay"
			label="Automatyczna płatność faktur"
			@toggle="toggleField('invoice_autopay')" />
		<BooleanInput
			v-model:modelValue="form.invoice_autosend"
			label="Automatyczna wysyłka faktur"
			@toggle="toggleField('invoice_autosend')" />
		<BooleanInput
			v-model:modelValue="form.invoice_advance_payment"
			label="Obciążenia wykonywane z góry"
			@toggle="toggleField('invoice_advance_payment')" />
		<BooleanInput
			v-model:modelValue="form.invoice_last"
			label="Ostatnia faktura"
			@toggle="toggleField('invoice_last')" />
		<div class="col-span-1 flex flex-col items-center">
			<InputLabel class="mb-6" value="Język faktury" />
			<SimpleSelect
				v-model="form.invoice_lang"
				:options="[
					{ code: 'pl', label: 'Polski' },
					{ code: 'en', label: 'Angielski' },
				]"
				class="self-stretch"
				@update:modelValue="updateField('invoice_lang', form.invoice_lang)" />
		</div>
		<div class="col-span-1 flex flex-col items-center">
			<InputLabel class="mb-6" value="Ilość dni na opłacenie" />
			<AmountInput
				v-model="form.invoice_payment_time"
				@update:modelValue="updateField('invoice_payment_time', form.invoice_payment_time)"
        :disabled="form.invoice_autopay"
				custom-symbol="dni" />
		</div>
		<div class="col-span-1 flex flex-col items-center">
			<InputLabel class="mb-6" value="Najbliższa miesięczna faktura" />
			<SimpleDatepicker
				v-model="form.invoice_next_month"
				clearable
				@update:modelValue="updateField('invoice_next_month', form.invoice_next_month)"
				locale="pl-PL"
				placeholder=" "
				:inputWithIcon="true" />
		</div>
		<div class="col-span-1 flex flex-col items-center">
			<InputLabel class="mb-6" value="Najbliższa roczna faktura" />
			<SimpleDatepicker
				v-model="form.invoice_next_year"
				clearable
				@update:modelValue="updateField('invoice_next_year', form.invoice_next_year)"
				locale="pl-PL"
				placeholder=" "
				:inputWithIcon="true" />
		</div>
		<div class="col-span-1 flex flex-col items-center">
			<InputLabel class="mb-6" value="Wysokość podatku VAT:" />
			<AmountInput v-model="form.vat" @update:modelValue="updateField('vat', form.vat)" custom-symbol="%" />
		</div>
	</div>
</template>
<script lang="ts" setup>
import BooleanInput from '@/Components/Dashboard/BooleanInput.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import AmountInput from '@/Components/Dashboard/AmountInput.vue';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';

import { Club } from '@/Types/models';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';

const props = defineProps<{
	club: Club;
}>();

const form = reactive({
	invoice_autopay: props.club.invoice_autopay,
	invoice_last: props.club.invoice_last,
	invoice_autosend: props.club.invoice_autosend,
	invoice_payment_time: props.club.invoice_payment_time.toString(),
	invoice_advance_payment: props.club.invoice_advance_payment,
	invoice_lang: props.club.invoice_lang,
	invoice_next_year: props.club.invoice_next_year,
	invoice_next_month: props.club.invoice_next_month,
	vat: props.club.vat,
});

const updateField = (fieldName: string, fieldValue: string | null) => {
	router.post(
		route('admin.clubs.set-field', {
			club: props.club,
			field: fieldName,
		}),
		{
			value: fieldValue,
		},
	);
};

const toggleField = (fieldName: string) => {
	router.post(
		route('admin.clubs.toggle-field', {
			club: props.club,
			field: fieldName,
		}),
	);
};
</script>
