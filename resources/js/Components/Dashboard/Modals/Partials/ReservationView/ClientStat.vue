<template>
	<div>
		<p class="text-sm">{{ value }}</p>
		<p class="text-xs">{{ name }}</p>
	</div>
</template>

<script lang="ts" setup>
const props = defineProps<{
	name?: String;
	value?: String;
}>();
</script>
