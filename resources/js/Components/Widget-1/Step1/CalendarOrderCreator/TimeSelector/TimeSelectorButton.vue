<template>
	<button
		type="button"
		:disabled="disabled"
		v-bind="disabled || { onClick: () => onClick?.(date) }"
		class="w-full select-none rounded-lg border-2 border-ui-green py-1.5 text-sm font-medium transition-colors disabled:cursor-not-allowed disabled:border-gray-2 disabled:text-gray-2 md:text-lg"
		:class="selected ? 'bg-ui-green text-white' : 'text-ui-black'">
		{{ value }}
	</button>
</template>

<script lang="ts" setup>
defineProps<{
	selected?: boolean;
	disabled?: boolean;
	onClick?: (date: Date) => void;
	date: Date;
	value: string;
}>();
</script>
