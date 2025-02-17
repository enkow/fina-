<template>
	<div class="relative">
		<TextInput
			:value="modelValue"
			@input="updateValue"
			class="h-12 text-base"
			:disabled="disabled"
			:filterChar="filterChar"
			type="text" />
		<div class="absolute right-3 top-3.5 text-sm">
			{{ customSymbol ?? currencySymbols[usePage().props.user.club.country.currency] }}
		</div>
	</div>
</template>

<script setup lang="ts">
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useReservations } from '@/Composables/useReservations';
import { usePage } from '@inertiajs/vue3';

const props = withDefaults(
	defineProps<{
		modelValue?: string;
		disabled?: boolean;
		customSymbol?: string;
		filterChar?: string;
	}>(),
	{
		modelValue: '',
		disabled: false,
	},
);

const { currencySymbols } = useReservations();

const emit = defineEmits<{
	(e: 'update:modelValue', value: string): void;
}>();

function updateValue(event: InputEvent) {
	emit('update:modelValue', event.target?.value ?? '');
}
</script>

<style scoped>
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
	-webkit-appearance: none;
	margin: 0;
}

/* Firefox */
input[type='number'] {
	-moz-appearance: textfield;
}
</style>
