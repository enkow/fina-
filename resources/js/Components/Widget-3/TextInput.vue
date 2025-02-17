<script lang="ts" setup>
import { onMounted, Ref, ref } from 'vue';
import { useWidgetStore } from '@/Stores/widget';

const props = withDefaults(
	defineProps<{
		modelValue?: String;
		type?: string;
	}>(),
	{
		type: 'text',
	},
);

defineEmits(['update:modelValue']);

const input: Ref<HTMLElement | null> = ref(null);

onMounted(() => {
	if (input.value && input.value.hasAttribute('autofocus')) {
		input.value.focus();
	}
});

defineExpose({ focus: () => (input.value ? input.value.focus() : null) });

const widgetStore = useWidgetStore();
const widgetColor = widgetStore.widgetColor;
</script>

<template>
	<input
		ref="input"
		:style="{ borderBottomColor: widgetColor }"
		:type="props.type"
		:value="modelValue"
		class="h-8 w-full border-0 border-b-[3px] py-2.5 pt-3 shadow-sm outline-none focus:border-brand-base focus:ring focus:ring-brand-base focus:ring-opacity-0"
		@input="$emit('update:modelValue', $event.target.value)" />
</template>

<style scoped>
input {
	@apply text-xs font-bold;
	color: v-bind(widgetColor);

	&::placeholder {
		@apply uppercase;
		color: v-bind(widgetColor);
	}

	&.highlighted {
		@apply border-brand-base;
	}
}
</style>
