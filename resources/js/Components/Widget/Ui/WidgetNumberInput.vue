<template>
	<div :class="WRAPPER_STYLES">
		<UnstyledNumberInput
			:id="id"
			:min="min"
			:max="max"
			:step="step"
			:model-value="modelValue"
			:class="twMerge(INPUT_STYLES, compact && INPUT_COMPACT_STYLES)"
			:prev-button-class="twJoin(BUTTON_STYLES, BUTTON_LEFT_STYLES)"
			:next-button-class="twJoin(BUTTON_STYLES, BUTTON_RIGHT_STYLES)"
			@update:modelValue="$emit('update:modelValue', $event)">
			<template #prevButton>-</template>
			<template #nextButton>+</template>
		</UnstyledNumberInput>
	</div>
</template>

<script lang="ts" setup>
import UnstyledNumberInput from '@/Components/Unstyled/UnstyledNumberInput.vue';
import { twJoin, twMerge } from 'tailwind-merge';
import {
	INPUT_STYLES,
	INPUT_COMPACT_STYLES,
	BUTTON_STYLES,
	BUTTON_LEFT_STYLES,
	BUTTON_RIGHT_STYLES,
	WRAPPER_STYLES,
	WidgetNumberInputBaseProps,
} from '@/Styles/WidgetNumberInput.styles';

defineProps<{ id?: string } & WidgetNumberInputBaseProps>();
</script>
