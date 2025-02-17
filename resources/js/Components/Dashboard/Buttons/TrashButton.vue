<template>
	<button
		class="trash-button flex h-8.5 w-8.5 items-center justify-center rounded-md bg-danger-base text-white transition-colors">
		<TrashIcon class="m-0 h-4 w-4 p-0" />
	</button>
</template>
<style>
.trash-button:disabled {
	@apply cursor-not-allowed text-opacity-75 opacity-75;
}

.trash-button:not(:disabled) {
	@apply cursor-pointer hover:bg-danger-dark hover:shadow-[0_2px_2px_rgba(0,0,0,0.25)];
}
</style>
<script lang="ts" setup>
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
</script>
