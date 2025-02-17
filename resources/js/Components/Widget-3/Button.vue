<template>
	<a v-if="type === 'a'" :href="href" class="button block">
		<slot></slot>
	</a>
	<Link v-else-if="type === 'link'" :href="href" class="button">
		<slot></slot>
	</Link>
	<button v-else :disabled="disabled" :type="type" class="button">
		<slot></slot>
	</button>
</template>

<style scoped>
button,
.button {
	@apply rounded-md border-[3px] py-2.5 text-base font-bold uppercase;
	border-color: v-bind(widgetColor);
	color: v-bind(widgetColor);

	&:disabled,
	&.disabled {
		@apply cursor-not-allowed !border-gray-3 !bg-transparent !text-gray-3;
	}

	&:not(.disabled):hover {
		@apply text-white;
		background-color: v-bind(widgetColor);
	}

	&:active {
		@apply opacity-75;
	}
}
</style>

<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { useWidgetStore } from '@/Stores/widget';

const props = withDefaults(
	defineProps<{
		type?: string;
		href?: string;
		disabled?: boolean;
	}>(),
	{
		type: 'button',
		href: '#',
		disabled: false,
	},
);

const widgetStore = useWidgetStore();
const widgetColor = widgetStore.widgetColor;
</script>
