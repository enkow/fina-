<template>
	<label class="custom-radio">
		<input
			type="radio"
			:class="classes"
			:name="name"
			:value="value"
			:checked="checked ?? isChecked"
			:disabled="disabled"
			@input="$emit('update:modelValue', $event.target.value)"
			:id="id" />
		<span class="checkmark"></span>
	</label>
</template>

<script lang="ts" setup>
import { defineProps, defineEmits, computed } from 'vue';

const props = withDefaults(
	defineProps<{
		name: string;
		checked?: boolean;
		disabled?: boolean;
		value?: string | number | boolean;
		modelValue?: string | number | boolean;
		id?: string;
		classes?: any;
	}>(),
	{
		id: '',
		name: '',
		disabled: false,
	},
);

const isChecked = computed(() => props.modelValue === props.value);
</script>

<style scoped>
.custom-radio {
	@apply relative h-4.5 w-4.5 cursor-pointer select-none rounded-full border border-gray-2/80 bg-white shadow-sm transition-all duration-150 hover:bg-gray-1 active:bg-gray-9;

	.checkmark {
		@apply invisible absolute left-1/2 top-1/2 h-2 w-2 -translate-x-1/2 -translate-y-1/2 rounded-full bg-brand-base opacity-0 transition-all duration-150;
	}

	input {
		@apply hidden;

		&:checked {
			& ~ .checkmark {
				@apply visible bg-brand-base opacity-100;
			}

			&:disabled ~ .checkmark {
				@apply bg-gray-500;
			}
		}
	}
}
</style>
