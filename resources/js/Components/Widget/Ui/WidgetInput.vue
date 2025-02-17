<template>
	<div class="relative">
		<input
			:id="id"
			:type="type"
			:placeholder="placeholder"
			:value="modelValue"
			@input="isInputElement($event.target) && $emit('update:modelValue', $event.target?.value)"
			:class="
				twMerge(
					'w-full rounded-2xl border-0 border-none px-5 py-3.5 text-base sm:text-lg ring-0 placeholder:text-ui-text focus:ring-2 focus:ring-ui-green',
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
import { isInputElement } from '@/Utils';
import { twMerge } from 'tailwind-merge';

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
	},
);
</script>
