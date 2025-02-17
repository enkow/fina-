<template>
	<div class="items-center space-x-3">
		<div class="legend-color h-4.5 w-6"></div>
		<p class="text-xs font-light">
			<slot />
		</p>
	</div>
</template>

<style scoped>
.brand-base .legend-color {
	@apply bg-brand-base;
}

.brand-dark .legend-color {
	@apply bg-brand-dark;
}
</style>
