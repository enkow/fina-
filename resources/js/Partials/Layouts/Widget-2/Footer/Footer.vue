<template>
	<footer
		:class="
			twMerge(
				'mt-9 flex h-9 items-center justify-center md:relative md:mt-3 md:h-12 md:flex-col md:justify-center',
				navigation && 'flex-row-reverse justify-between',
			)
		">
		<FooterNavigation v-if="navigation" />
		<p
			class="text-xs text-ui-black/60 md:absolute md:left-1/2 md:top-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:text-sm">
			{{ $t('widget-2.system-delivered-by') }}
			<a :href="homepageUrl" target="_blank" class="text-ui-green underline">{{ appName }}</a>
		</p>
	</footer>
</template>

<script lang="ts" setup>
import { twMerge } from 'tailwind-merge';
import FooterNavigation from './FooterNavigation.vue';

const appName = import.meta.env.VITE_APP_NAME.toLowerCase();
const homepageUrl = import.meta.env.VITE_HOMEPAGE_URL;

defineProps<{
	navigation?: boolean;
}>();
</script>
