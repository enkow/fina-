<script lang="ts" setup>
import { onMounted, Ref, ref } from 'vue';

const props = defineProps<{
	modelValue?: String;
	readonly?: boolean;
	disabled?: boolean;
}>();

defineEmits(['update:modelValue']);

const input: Ref<HTMLElement | null> = ref(null);

onMounted(() => {
	if (input.value && input.value.hasAttribute('autofocus')) {
		input.value.focus();
	}
});

defineExpose({ focus: () => (input.value ? input.value.focus() : null) });
</script>

<template>
	<input
		ref="input"
		:value="modelValue"
		class="text-input h-12 w-full rounded-md border border-gray-2 px-3.5 py-3 font-sans !text-base shadow-sm outline-none focus:border-brand-base focus:ring focus:ring-brand-base focus:ring-opacity-0"
		:class="{ disabled: disabled, 'disabled-readable': readonly }"
		:disabled="disabled || readonly"
		@input="$emit('update:modelValue', $event.target.value)" />
</template>

<style scoped>
input {
	&::placeholder {
		@apply text-base text-gray-3;
	}

	&:read-only {
		@apply bg-gray-9 text-gray-6;
	}

	&.disabled:not(.disabled-readable) {
		@apply bg-gray-2 text-transparent;

		&::placeholder {
			@apply text-transparent;
		}
	}

	&.disabled-readable {
		@apply bg-gray-1 text-gray-3;
	}

	&.highlighted {
		@apply border-brand-base;
	}
}
</style>
