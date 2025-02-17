<template>
	<button
		type="button"
		:aria-label="label"
		:class="twJoin(BASE_STYLES, radiuses[radius], sizes[size], variants[variant], colors[color])">
		<slot />
	</button>
</template>

<script lang="ts" setup>
import { BASE_STYLES, radiuses, variants, createColors, ButtonBaseProps } from '@/Styles/WidgetButton.styles';
import { twJoin } from 'tailwind-merge';

const props = withDefaults(defineProps<Props>(), {
	color: 'dark-green',
	radius: 'md',
	size: 'md',
	variant: 'filled',
});
const colors = createColors(props.variant);

const sizes = {
	md: 'w-9 h-9 md:w-12 md:h-12',
} as const;

interface Props extends ButtonBaseProps {
	readonly size?: keyof typeof sizes;
	readonly label: string;
}
</script>
