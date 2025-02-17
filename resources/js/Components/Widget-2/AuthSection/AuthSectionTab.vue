<template>
	<button
		type="button"
		:class="
			twMerge(
				'rounded-t-2xl px-5 py-2.5 text-start text-sm font-medium text-ui-green transition-colors md:rounded-full',
				selected && 'bg-ui-green-100 text-ui-green-950',
			)
		">
		{{ name }}
	</button>
</template>

<script lang="ts" setup>
import { twMerge } from 'tailwind-merge';

defineProps<{
	selected?: boolean;
	name: string;
}>();
</script>
