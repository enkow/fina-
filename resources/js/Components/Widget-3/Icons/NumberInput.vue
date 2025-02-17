<template>
	<div class="flex items-center space-x-3 pb-2">
		<button
			:class="{
				disabled: value === min,
			}"
			:disabled="value === min"
			:style="{ background: widgetStore.widgetColor }"
			class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full text-white active:opacity-75"
			@click="$emit('update', Math.max(value - step, min))">
			<svg fill="none" height="2" viewBox="0 0 5 2" width="5" xmlns="http://www.w3.org/2000/svg">
				<path d="M0 0H4.48V1.176H0V0Z" fill="white" />
			</svg>
		</button>
		<p
			:class="{ 'flex-grow': labelWidth === undefined }"
			:style="{ width: labelWidth !== undefined ? labelWidth + 'px' : '' }"
			class="select-none text-center text-sm font-extrabold">
			{{ label === undefined ? value : label }}
		</p>
		<button
			:class="{
				disabled: value === max,
			}"
			:style="{ background: widgetStore.widgetColor }"
			class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full text-white active:opacity-75"
			@click="$emit('update', Math.min(value + step, max))"
			:disabled="value === max">
			<svg fill="none" height="7" viewBox="0 0 7 7" width="7" xmlns="http://www.w3.org/2000/svg">
				<path d="M2.912 7V4.088H0V2.912H2.912V0H4.088V2.912H7V4.088H4.088V7H2.912Z" fill="white" />
			</svg>
		</button>
	</div>
</template>
<style scoped>
.disabled {
	@apply cursor-not-allowed opacity-40;
}
</style>
<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';

const props = defineProps<{
	label?: string;
	labelWidth?: number;
	value: number;
	step: number;
	min: number;
	max: number;
}>();

const emit = defineEmits<{
	(e: 'update', value: number): void;
}>();

const widgetStore = useWidgetStore();
</script>
