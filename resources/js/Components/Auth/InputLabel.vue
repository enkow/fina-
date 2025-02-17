<script setup>
defineProps({
	value: String,
});
</script>

<template>
	<label class="block text-sm font-medium text-gray-7">
		<span v-if="value">{{ value }}</span>
		<span v-else><slot /></span>
	</label>
</template>
