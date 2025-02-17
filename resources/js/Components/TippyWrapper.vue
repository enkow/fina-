<template>
	<div v-if="content" v-tippy :content="content">
		<slot />
	</div>
	<div v-else>
		<slot />
	</div>
</template>

<script lang="ts" setup>
defineProps<{
	content?: string;
}>();
</script>
