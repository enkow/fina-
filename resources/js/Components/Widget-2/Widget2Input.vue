<template>
	<div
		:class="
			twJoin(
				'relative h-6.5 border-b border-ui-green-500',
				fill && 'w-full',
				error && 'border-widget-2-red focus:border-widget-2-red',
			)
		">
		<input
			:type="type"
			:placeholder="placeholder"
			:value="modelValue"
			:maxlength="maxLength"
			@input="isInputElement($event.target) && $emit('update:modelValue', $event.target.value)"
			:class="
				twMerge(
					'h-full w-full border-0 bg-transparent px-0 py-0 text-xs placeholder:text-ui-black/60 focus:border-ui-green-500 focus:ring-0',
					$slots.rightSection && 'pr-10',
				)
			" />
		<div
			v-if="$slots.rightSection"
			class="absolute right-0 top-0 flex aspect-square h-full items-center justify-center">
			<slot name="rightSection" />
		</div>
	</div>
</template>

<script lang="ts" setup>
import { isInputElement } from '@/Utils';
import { twJoin, twMerge } from 'tailwind-merge';

withDefaults(
	defineProps<{
		type?: 'text' | 'email' | 'password';
		error?: boolean;
		fill?: boolean;
		placeholder?: string;
		modelValue?: string;
		maxLength?: number;
	}>(),
	{ type: 'text' },
);
</script>
