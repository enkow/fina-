<template>
	<Toggle
		:model-value="modelValue"
		:disabled="disabled"
		:off-label="offLabel"
		:on-label="onLabel"
		class="toggle-component mt-3"
		@update:modelValue="handleUpdate" />
</template>
<style scoped>
.toggle-component {
	&:deep(.toggle-container):focus {
		@apply outline-none;
		box-shadow: 0 0 0 var(--toggle-ring-width, 3px) var(--toggle-ring-color, rgba(16, 185, 129, 0.188));
	}

	&:deep(.toggle) {
		@apply flex cursor-pointer items-center rounded-full border transition-all duration-300;
		font-size: var(--toggle-font-size, 0.75rem);
		height: var(--toggle-height, 1.25rem);
		line-height: 1;
		position: relative;
		width: var(--toggle-width, 3rem);
	}

	&:deep(.toggle-on) {
		@apply justify-start;
		background: var(--toggle-bg-on, #10b981);
		border-color: var(--toggle-border-on, #10b981);
		color: var(--toggle-text-on, #fff);
	}

	&:deep(.toggle-off) {
		@apply justify-end;
		background: var(--toggle-bg-off, #e5e7eb);
		border-color: var(--toggle-border-off, #e5e7eb);
		color: var(--toggle-text-off, #374151);
	}

	&:deep(.toggle-on-disabled) {
		@apply cursor-not-allowed justify-start;
		background: var(--toggle-bg-on-disabled, #d1d5db);
		border-color: var(--toggle-border-on-disabled, #d1d5db);
		color: var(--toggle-text-on-disabled, #9ca3af);
	}

	&:deep(.toggle-off-disabled) {
		@apply cursor-not-allowed justify-end;
		background: var(--toggle-bg-off-disabled, #e5e7eb);
		border-color: var(--toggle-border-off-disabled, #e5e7eb);
		color: var(--toggle-text-off-disabled, #9ca3af);
	}

	&:deep(.toggle-handle) {
		@apply absolute top-0.5 block rounded-full bg-white 2xl:top-0;
		transition-property: all;
		transition-duration: var(--toggle-duration, 0.15s);
		transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
		height: var(--toggle-height, 1.25rem);
		width: var(--toggle-height, 1.25rem);
	}

	&:deep(.toggle-handle-on) {
		@apply left-full;
		transform: translateX(-100%);
	}

	&:deep(.toggle-handle-off) {
		@apply left-1;
	}

	&:deep(.toggle-handle-on-disabled) {
		@apply left-full bg-gray-200;
		transform: translateX(-100%);
	}

	&:deep(.toggle-handle-off-disabled) {
		@apply left-1 bg-gray-200;
	}

	&:deep(.toggle-label) {
		@apply box-border select-none whitespace-nowrap text-center;
		width: calc(var(--toggle-width, 3.25rem) - var(--toggle-height, 1.25rem));
	}
}
</style>
<script lang="ts" setup>
import Toggle from '@vueform/toggle';
import { computed } from 'vue';
import { wTrans } from 'laravel-vue-i18n';

const props = withDefaults(
	defineProps<{
		modelValue?: boolean;
		disabled?: boolean;
	}>(),
	{
		modelValue: false,
		disabled: false,
	},
);

const emit = defineEmits<{
	(e: 'update:modelValue', value: number): void;
}>();

const handleUpdate = (value: boolean) => {
	emit('update:modelValue', value);
};

const offLabel = computed(() => [wTrans('main.turned-off').value]);
const onLabel = computed(() => [wTrans('main.turned-on').value]);
</script>
