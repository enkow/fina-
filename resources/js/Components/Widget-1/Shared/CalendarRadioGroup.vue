<template>
	<RadioGroup
		:model-value="modelValue"
		@update:model-value="(value) => $emit('update:modelValue', value)"
		class="flex flex-col gap-y-5">
		<RadioGroupLabel class="sr-only">{{ title }}</RadioGroupLabel>
		<TippyWrapper 
			v-for="{ title, value, label, disabled, message } in options"
			v-bind="message ? { content: message } : {}">

			<RadioGroupOption
			v-slot="{ checked }" 
			:value="value"
			:disabled="disabled"
			v-tippy
			v-bind="disabled && label && { content: label }"
			:class="{ '!cursor-not-allowed opacity-50': disabled }"
			class="flex h-16 cursor-pointer items-center gap-x-3 rounded-2.5xl bg-white px-5.5 text-sm text-ui-black md:text-lg">
				<div
				class="relative h-5 w-5 flex-shrink-0 rounded-full border transition-colors duration-150 before:absolute before:left-1/2 before:top-1/2 before:block before:h-2.5 before:w-2.5 before:-translate-x-1/2 before:-translate-y-1/2 before:rounded-full before:bg-ui-green before:opacity-0 before:transition-opacity before:duration-150"
				:class="checked ? 'border-ui-green before:opacity-100' : 'border-ui-green-950'" />
				{{ title }}
		</RadioGroupOption>
	</TippyWrapper>
	</RadioGroup>
</template>

<script lang="ts" setup>
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue';
import TippyWrapper from '@/Components/TippyWrapper.vue';

defineProps<{
	modelValue: string;
	title: string;
	options: Option[];
}>();

interface Option {
	readonly title: string;
	readonly value: string;
	readonly label?: string;
	readonly disabled?: boolean;
	readonly message?: string;
}
</script>
