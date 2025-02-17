<template>
	<li class="flex items-center gap-x-2.5 text-sm md:text-base">
		<div
			class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-md text-ui-green-950"
			:class="variants[variant]">
			<slot name="icon" />
		</div>
		{{ title }}
	</li>
</template>

<script lang="ts" setup>
const variants = {
	available: 'border-2 border-ui-green',
	unavailable: 'border-2 border-gray-2',
	optional: 'border-2 border-ui-green bg-ui-green-200',
	selected: 'bg-ui-green',
} as const;

defineProps<{
	variant: keyof typeof variants;
	title: string;
}>();
</script>
