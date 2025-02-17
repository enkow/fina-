<template>
	<div class="relative w-full rounded-md bg-gray-3/15 p-3">
		<div
			:class="{ 'right-0': float === 'right' }"
			class="mascot-wrapper absolute bottom-0 hidden h-36 sm:block">
			<slot name="mascot" />
		</div>
		<CloudDotsHorizontal
			:class="{
				'left-14': float === 'left',
				'right-10 rotate-180': float === 'right',
			}"
			class="absolute top-12 ml-1 hidden w-55 text-white sm:block xl:top-12 2xl:top-12" />
		<div
			:class="{
				'sm:ml-50': float === 'left',
				'sm:ml-auto sm:mr-50': float === 'right',
			}"
			class="font-weight mb-4 w-full w-full rounded-md bg-white p-4 text-center text-sm leading-6 text-black/70 text-gray-7 sm:mb-0 sm:w-[calc(100%-200px)]">
			<slot />
		</div>
		<div
			:class="{
				'sm:ml-auto': float === 'left',
				'sm:mr-auto': float === 'right',
			}"
			class="button-container">
			<slot name="button" />
		</div>
	</div>
</template>

<script lang="ts" setup>
import CloudDotsHorizontal from '@/Components/Dashboard/Icons/CloudDotsHorizontal.vue';

const props = withDefaults(
	defineProps<{
		float?: string;
	}>(),
	{
		float: 'left',
	},
);
</script>

<style scoped>
.mascot-wrapper:deep(svg) {
	@apply h-full;
}

.button-container {
	@apply mt-3 flex w-full justify-end;

	&:deep(button) {
		@apply w-full sm:w-auto sm:text-left;
	}
}
</style>
