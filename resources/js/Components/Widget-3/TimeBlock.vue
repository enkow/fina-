<template>
	<div class="time-block">
		<div class="time-block__content">
			{{ value }}
		</div>
		<div v-if="withDiscount" class="time-block__discount">
			<DiscountIcon />
		</div>
	</div>
</template>

<style scoped>
.time-block {
	@apply relative h-11 w-full cursor-pointer rounded-md border-[3px] border-solid text-center font-extrabold transition-all;
	border-color: v-bind(widgetColor);
	color: v-bind(widgetColor);

	&:hover,
	&.active {
		@apply text-white;
		background-color: v-bind(widgetColor);
	}

	.time-block__content {
		@apply absolute left-0 top-0 flex h-full w-full items-center justify-center;
	}

	.time-block__discount {
		@apply absolute right-0 top-0.5;
	}
}
</style>

<script lang="ts" setup>
import DiscountIcon from '@/Components/Widget-3/Icons/DiscountIcon.vue';
import { useWidgetStore } from '@/Stores/widget';

const props = withDefaults(
	defineProps<{
		value: string;
		withDiscount?: boolean;
	}>(),
	{
		withDiscount: false,
	},
);

const widgetStore = useWidgetStore();
const widgetColor = widgetStore.widgetColor;
</script>
