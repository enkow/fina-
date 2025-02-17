<template>
	<component :is="as" class="step-button">
		<slot />
	</component>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
const widgetColor = widgetStore.widgetColor;

withDefaults(
	defineProps<{
		as?: 'button' | 'span';
	}>(),
	{ as: 'button' },
);
</script>

<style scoped>
.step-button {
	@apply flex h-9 w-15 cursor-pointer items-center justify-center rounded-md;
	background-color: v-bind(widgetColor);

	&:deep(svg) {
		@apply text-white;
	}

	&:hover {
		@apply opacity-80;
	}

	&:active {
		@apply opacity-60;
	}

	&:disabled {
		@apply cursor-not-allowed opacity-20;
	}
}
</style>
