<template>
	<div :class="twJoin('relative', fill && 'w-full')">
		<slot :error="Boolean(error)" />
		<component
			v-if="error || success"
			:is="error ? InfoCircleIcon : TickCircleIcon"
			:content="error ?? success"
			:class="twJoin(error && 'text-widget-2-red', success && 'text-ui-green-700')"
			class="absolute right-0 top-1/2 w-4 -translate-y-1/2"
			v-tippy />
	</div>
</template>

<script lang="ts" setup>
import InfoCircleIcon from './Icons/InfoCircleIcon.vue';
import TickCircleIcon from './Icons/TickCircleIcon.vue';
import { twJoin } from 'tailwind-merge';

defineProps<{
	fill?: boolean;
	error?: string;
	success?: string;
}>();
</script>
