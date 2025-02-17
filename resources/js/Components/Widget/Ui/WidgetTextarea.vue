<template>
	<div class="relative">
		<textarea
			@input="updateText"
			:value="modelValue"
			:id="id"
			:type="type"
			:placeholder="placeholder"
			:class="
				twMerge(
					'w-full rounded-2xl border-0 border-none px-5 py-3.5 text-base sm:text-lg ring-0 placeholder:text-ui-text focus:ring-2 focus:ring-ui-green h-32',
					colors[color],
					$slots.rightSection && 'pr-12',
				)
			" />
		<div
			v-if="$slots.rightSection"
			class="absolute right-2.5 top-1/2 flex aspect-square h-8 w-8 -translate-y-1/2 items-center justify-center">
			<slot name="rightSection" />
		</div>
	</div>
</template>

<script lang="ts" setup>
import { twMerge } from 'tailwind-merge';
import { TextareaHTMLAttributes, defineEmits } from 'vue';

const emit = defineEmits(["update:modelValue"]);

const colors = {
	green: 'bg-ui-green-400',
	white: 'bg-white',
} as const;

withDefaults(
	defineProps<{
		id?: string;
		type?: 'text' | 'email' | 'password';
		color?: keyof typeof colors;
		placeholder?: string;
		modelValue?: string;
	}>(),
	{
		type: 'text',
		color: 'green',
		modelValue: ''
	},
);

function updateText(e: Event) {
	emit('update:modelValue', (e.target as TextareaHTMLAttributes).value)
}
</script>