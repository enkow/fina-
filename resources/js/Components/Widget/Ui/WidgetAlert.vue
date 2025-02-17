<template>
	<div :class="twJoin('space-y-4 rounded-4.5xl p-6 md:p-8.5', colors[color])">
		<div class="flex items-center gap-x-5">
			<WarningIcon class="w-5 text-ui-green" />
			<h2 class="my-0 text-xl font-bold text-ui-black md:text-2xl">
				<slot name="title" />
			</h2>
		</div>
		<p v-if="$slots.description" class="text-ui-black/60 md:text-lg">
			<slot name="description" />
		</p>
	</div>
</template>

<script lang="ts" setup>
import WarningIcon from '../Icons/WarningIcon.vue';
import { twJoin } from 'tailwind-merge';

const colors = {
	primary: 'bg-ui-green-50',
	white: 'bg-white',
} as const;

withDefaults(defineProps<{ color: keyof typeof colors }>(), { color: 'primary' });
</script>
