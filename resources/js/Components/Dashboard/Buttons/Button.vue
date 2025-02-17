<template>
	<Link v-if="type === 'link'" :href="href" class="button">
		<slot></slot>
	</Link>
	<span v-else-if="type === 'span'">
		<slot></slot>
	</span>
	<a v-else-if="type === 'a'" :type="type" :href="href" class="button" :target="target">
		<slot></slot>
	</a>
	<button v-else :type="type" class="button">
		<slot></slot>
	</button>
</template>

<style scoped>
button,
.button {
	@apply flex h-12 items-center justify-center rounded-md bg-brand-base px-8.25 py-[7px] font-semibold leading-5 tracking-wider text-white shadow-none transition-all duration-150;

	&:not(.disabled):not(:disabled) {
		@apply hover:bg-brand-dark hover:shadow-[0_4px_4px_rgba(0,0,0,0.25)] active:!bg-opacity-75;
	}

	&.disabled,
	&:disabled {
		@apply cursor-not-allowed opacity-60 shadow-none grayscale;
	}

	svg {
		@apply -mt-1 mr-1 inline-block;
	}

	/*
    SIZES
   */

	&.lg {
		@apply h-13 w-auto px-25 text-lg;
	}

	&.sm {
		@apply h-[38px] px-7 text-xs;
	}

	&.xs {
		@apply !h-7.25 w-7.25 !px-0;
	}

	/*
    COLORS
   */

	&.brand-light {
		@apply bg-brand-light text-brand-base hover:!bg-brand-base hover:text-white;
	}

	&.grey {
		@apply bg-gray-3 hover:!bg-gray-4;
	}

	&.warning {
		@apply bg-warning-base hover:!bg-warning-dark;
	}

	&.warning-light {
		@apply bg-warning-light text-warning-base hover:!bg-warning-base hover:text-white;
	}

	&.info {
		@apply bg-info-base hover:!bg-info-dark;
	}

	&.info-light {
		@apply bg-info-light text-info-base hover:!bg-info-base hover:text-white;
	}

	&.danger {
		@apply bg-danger-base hover:!bg-danger-dark;
	}

	&.danger-light {
		@apply bg-danger-light text-danger-base hover:!bg-danger-base hover:text-white;
	}
}
</style>

<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';

const props = withDefaults(
	defineProps<{
		type?: string;
		href?: string;
		target?: string;
	}>(),
	{
		type: 'button',
		href: '#',
	},
);
</script>
