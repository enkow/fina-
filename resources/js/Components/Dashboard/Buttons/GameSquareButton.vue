<template>
	<Link v-if="type === 'link'" :href="href" class="button">
		<slot></slot>
	</Link>
	<button v-else :type="type" class="button">
		<slot></slot>
	</button>
</template>

<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';

const props = withDefaults(
	defineProps<{
		type: string;
		href: string;
	}>(),
	{
		type: 'submit',
		default: '#',
	},
);
</script>

<style scoped>
.button {
	@apply flex h-12 w-12 rounded-md border-2 border-solid border-gray-3 bg-transparent p-0 text-gray-3 transition-all;
	@apply hover:border-0 hover:bg-info-base hover:text-white;
	@apply active:border-0 active:bg-info-dark active:text-white active:text-opacity-75;

	&.active {
		@apply border-0 bg-info-base text-white;
	}
}

.button:deep(svg) {
	@apply w-8;
}
</style>
