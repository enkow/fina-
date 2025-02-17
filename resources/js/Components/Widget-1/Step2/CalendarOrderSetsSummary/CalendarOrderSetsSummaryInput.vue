<template>
	<div class="flex gap-x-3">
		<UnstyledNumberInput
			:min="min"
			:max="max"
			:model-value="modelValue"
			:prev-button-class="BUTTON_STYLES"
			:next-button-class="BUTTON_STYLES"
			class="moz-appearance-textfield w-6 border-0 p-0 text-center text-ui-black/60 ring-0 focus:ring-0 [&::-webkit-inner-spin-button]:appearance-none [&::webkit-outer-spin-button]:appearance-none"
			@update:modelValue="$emit('update:modelValue', $event)">
			<template #prevButton>
				<BinIcon v-if="modelValue === 1" />
				<span v-else>-</span>
			</template>
			<template #nextButton>+</template>
		</UnstyledNumberInput>
	</div>
</template>

<script lang="ts" setup>
import BinIcon from '@/Components/Widget-1/Icons/BinIcon.vue';
import UnstyledNumberInput from '@/Components/Unstyled/UnstyledNumberInput.vue';

defineProps<{
	modelValue: number;
	min: number;
	max: number;
}>();

const BUTTON_STYLES =
	'text-ui-green text-2xl font-semibold w-5 flex items-center justify-center transition-opacity disabled:opacity-50 disabled:cursor-not-allowed';
</script>
