<script setup>
import { onMounted, ref } from 'vue';

defineProps({
	modelValue: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
	if (input.value.hasAttribute('autofocus')) {
		input.value.focus();
	}
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
	<input
		ref="input"
		:value="modelValue"
		class="rounded-md border-gray-300 px-[30px] py-3 shadow-sm focus:border-brand-base focus:ring focus:ring-brand-base focus:ring-opacity-0"
		@input="$emit('update:modelValue', $event.target.value)" />
</template>

<style scoped>
input::placeholder {
	@apply text-lg text-[#9AA1B3];
}
</style>
