<template>
	<div class="item">
		<slot />
	</div>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
const widgetColor = widgetStore.widgetColor;
</script>

<style scoped>
.item {
	@apply flex h-12 w-12 items-center justify-center rounded-full border-4 border-solid bg-white text-xl font-bold;
	color: #000;
	border-color: v-bind(widgetColor);

	&.active {
		@apply text-white;
		background: v-bind(widgetColor);
	}
}
</style>
