<template>
	<div class="flex h-5 w-5 p-0">
		<div :style="left" class="h-full w-1/2 rounded-l"></div>
		<div :style="right" class="h-full w-1/2 rounded-r"></div>
	</div>
</template>

<script lang="ts" setup>
const props = withDefaults(
	defineProps<{
		left?: Object;
		right?: Object;
	}>(),
	{
		left: {
			background: '#000',
		},
		right: {
			background: '#000',
		},
	},
);
</script>
