<template>
	<div class="container w-full rounded-md bg-gray-3/15 pb-4 pt-7.5">
		<div class="m-auto max-w-85">
			<div
				class="mx-auto w-full max-w-[90%] rounded-md bg-white p-4 px-5 text-center text-sm leading-6 text-gray-7/70">
				<slot></slot>
			</div>
			<CloudDots class="dots mb-4 ml-[50%] mt-3 text-white"></CloudDots>
			<div class="mascot m-auto flex max-w-53 justify-center">
				<slot name="mascot"></slot>
			</div>
			<div class="button-container">
				<slot name="button"></slot>
			</div>
		</div>
	</div>
</template>

<script lang="ts" setup>
import CloudDots from '@/Components/Dashboard/Icons/CloudDots.vue';
</script>

<style scoped>
.small.container {
	@apply pt-3;

	.dots {
		@apply mb-0 mb-1 ml-[55%] mt-1 w-8;
	}

	.mascot {
		@apply -mt-6 w-40;
	}
}

.mascot {
	@apply mb-3;
}

.button-container {
	@apply w-full px-3;

	&:deep(button) {
		@apply w-full;
	}
}
</style>
