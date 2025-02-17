<template>
	<button>
		<slot></slot>
	</button>
</template>

<style scoped>
button {
	@apply h-6 w-36 rounded-md bg-info-light px-0 text-center text-xxs font-normal tracking-tight text-info-base transition-all;
	@apply hover:bg-info-base/50 hover:font-bold hover:text-white;

	&.active {
		@apply bg-info-base font-bold text-white;
	}
}
</style>
