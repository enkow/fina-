<template>
	<div>
		<slot></slot>
	</div>
</template>
<style scoped>
div {
	@apply flex h-7 w-7 items-center justify-center rounded-md text-xxs font-bold transition-all hover:shadow-sm;
}

div.brand {
	@apply bg-brand-light text-brand-base hover:bg-brand-base/20;
}

div.danger {
	@apply bg-danger-light text-danger-base hover:bg-danger-base/20;
}
</style>
