<template>
	<div class="container">
		<slot></slot>
	</div>
</template>

<style scoped>
.container {
	@apply h-12 cursor-pointer rounded-md border border-brand-base bg-brand-light pl-4 text-base font-semibold leading-12 text-brand-base transition-all;
	@apply hover:bg-brand-base hover:text-white;

	&.brand {
		@apply cursor-default bg-transparent font-light text-gray-7;
	}

	&.info {
		@apply border-info-base bg-info-light text-info-base hover:bg-info-base hover:text-white;
	}

	&.disabled {
		@apply cursor-default border-gray-2 bg-gray-2 text-gray-2;
	}
}
</style>
