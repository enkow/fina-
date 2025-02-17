<template>
	<li
		v-bind="{ ...(complete && { 'aria-label': `Step ${step} completed` }) }"
		:class="twMerge($props.class, active && activeClass, complete && completeClass)">
		<slot name="complete-icon" v-if="complete" />
		<span v-else>{{ step }}</span>
	</li>
</template>

<script lang="ts" setup>
import { twMerge } from 'tailwind-merge';

defineProps<{
	active?: boolean;
	complete?: boolean;
	class?: string;
	activeClass?: string;
	completeClass?: string;
	step: number;
}>();
</script>
