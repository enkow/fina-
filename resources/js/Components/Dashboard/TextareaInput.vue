<template>
	<textarea
		ref="input"
		class="w-full resize-none rounded-md border border-gray-3 px-3.5 py-2.5 pt-3 shadow-sm outline-none focus:border-brand-base focus:ring focus:ring-brand-base focus:ring-opacity-0"
		@input="$emit('update:modelValue', $event.target.value)"
    :class="{ disabled: disabled, 'disabled-readable': readonly }"
    :disabled="disabled || readonly"
  ></textarea>
</template>

<script lang="ts" setup>
import { onMounted, Ref, ref } from 'vue';

const props = defineProps<{
	modelValue?: String;
  readonly?: boolean;
  disabled?: boolean;
}>();

defineEmits(['update:modelValue']);

const input: Ref<String> = ref('');

onMounted(() => {
	if (input.value.hasAttribute('autofocus')) {
		input.value.focus();
	}
});

defineExpose({ focus: () => input.value.focus() });
</script>

<style scoped>
textarea {
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
    @apply bg-gray-1 border-gray-2 text-gray-3;
  }

  &.highlighted {
    @apply border-brand-base;
  }
}
</style>
