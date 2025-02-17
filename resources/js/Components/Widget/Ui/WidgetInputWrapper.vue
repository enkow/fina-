<template>
	<div class="space-y-3">
		<label v-if="$slots.label" :for="id" class="flex gap-x-3 text-lg font-medium text-ui-black">
			<slot name="label"></slot>
		</label>
		<slot :id="id" />
		<p
			v-if="error || success"
			:class="twJoin('text-sm md:text-base', error && 'text-ui-red', success && 'text-ui-green')">
			{{ error ?? success }}
		</p>
	</div>
</template>

<script lang="ts" setup>
import { getCurrentInstance } from 'vue';
import { twJoin } from 'tailwind-merge';

defineProps<{
	for?: string;
	error?: string;
	success?: string;
}>();

const id = `input-${getCurrentInstance()?.uid}`;
</script>
