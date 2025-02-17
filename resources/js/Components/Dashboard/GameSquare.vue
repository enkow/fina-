<template>
	<div>
		<slot></slot>
	</div>
</template>

<style scoped>
div {
	@apply flex h-9 w-9 items-center justify-center rounded-md border-gray-3 bg-brand-base bg-transparent p-0 text-gray-3 text-white transition-all;
}

div:deep(svg) {
	@apply w-6;
}
</style>
