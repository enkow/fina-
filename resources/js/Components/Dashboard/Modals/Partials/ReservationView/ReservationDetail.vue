<template>
	<div class="flex">
		<div class="icon-wrapper">
			<slot />
		</div>
		<div>
			<p class="text-xl font-semibold text-gray-5">{{ value }}</p>
			<p class="-mt-1 text-xs text-gray-5">{{ name }}</p>
		</div>
	</div>
</template>

<script lang="ts" setup>
const props = defineProps<{
	name?: String;
	value?: String;
}>();
</script>

<style scoped>
.icon-wrapper:deep(svg) {
	@apply mr-2 mt-1.5 h-6.5 w-6.5 text-brand-base;
}
</style>
