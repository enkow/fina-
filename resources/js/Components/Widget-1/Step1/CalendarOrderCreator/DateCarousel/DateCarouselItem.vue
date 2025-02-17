<template>
	<button
		type="button"
		v-bind="disabled || { onClick: () => onItemClick?.(date) }"
		:class="
			twMerge(
				'flex h-full w-full flex-col items-center justify-center gap-y-3 font-medium text-ui-black transition-colors duration-100 before:absolute before:bottom-0 before:block before:h-0.75 before:w-full before:rounded-full before:bg-ui-green before:opacity-0 before:transition-opacity',
				active && 'text-ui-green before:opacity-100',
				disabled && 'cursor-not-allowed text-gray-2',
			)
		">
		<p class="opacity-60 md:text-lg">
			{{ date.toLocaleString(widgetStore.currentLocale, { weekday: 'short' }).toUpperCase() }}
		</p>
		<p class="text-xl md:text-4xl">{{ date.getDate() }}</p>
		<p class="block w-full overflow-hidden text-ellipsis whitespace-nowrap opacity-60 md:text-lg">
			{{ date.toLocaleString(widgetStore.currentLocale, { month: 'long' }).toLowerCase() }}
		</p>
	</button>
</template>

<script setup lang="ts">
import { useWidgetStore } from '@/Stores/widget';
import { twMerge } from 'tailwind-merge';

defineProps<{
	active?: boolean;
	disabled?: boolean;
	onItemClick?: (date: Date) => void;
	date: Date;
}>();

const widgetStore = useWidgetStore();
</script>
