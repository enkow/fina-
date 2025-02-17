<template>
	<div :class="WRAPPER_STYLES">
		<UnstyledNumberInput
			:min="min"
			:max="max"
			:step="step"
			:model-value="modelValue"
			:class="twMerge('flex items-center justify-center', INPUT_STYLES, compact && INPUT_COMPACT_STYLES)"
			:prev-button-class="twJoin(BUTTON_STYLES, BUTTON_LEFT_STYLES)"
			:next-button-class="twJoin(BUTTON_STYLES, BUTTON_RIGHT_STYLES)"
			@update:modelValue="$emit('update:modelValue', $event)">
			<template #default>
				{{ label }}
			</template>
			<template #prevButton>-</template>
			<template #nextButton>+</template>
		</UnstyledNumberInput>
	</div>
</template>

<script lang="ts" setup>
import UnstyledNumberInput from '@/Components/Unstyled/UnstyledNumberInput.vue';
import {
	INPUT_STYLES,
	INPUT_COMPACT_STYLES,
	BUTTON_STYLES,
	BUTTON_LEFT_STYLES,
	BUTTON_RIGHT_STYLES,
	WRAPPER_STYLES,
	WidgetNumberInputBaseProps,
} from '@/Styles/WidgetNumberInput.styles';
import { twJoin, twMerge } from 'tailwind-merge';

defineProps<{ label: string } & WidgetNumberInputBaseProps>();
</script>
