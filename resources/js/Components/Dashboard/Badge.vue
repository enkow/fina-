<template>
	<div>
		<slot></slot>
	</div>
</template>

<style scoped>
div {
	@apply flex h-8.5 items-center justify-center rounded-md px-6 text-sm font-semibold uppercase text-white;

	&.small {
		@apply h-6.5 pt-1.5 text-xs;
	}

	&.black {
		@apply bg-black;
	}

	&.info {
		@apply bg-info-base;
	}

	&.info-accent {
		@apply bg-info-light text-info-base;
	}

	&.info-dark {
		@apply bg-info-dark;
	}

	&.warning {
		@apply bg-warning-base;
	}

	&.warning-accent {
		@apply bg-warning-light text-warning-base;
	}

	&.danger {
		@apply bg-danger-base;
	}

	&.danger-accent {
		@apply bg-danger-light text-danger-base;
	}

	&.brand {
		@apply bg-brand-base;
	}

	&.brand-accent {
		@apply bg-brand-light text-brand-base;
	}
}
</style>
