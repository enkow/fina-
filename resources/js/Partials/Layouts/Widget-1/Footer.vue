<template>
	<footer class="mt-16.5 text-center">
		{{ $t('calendar.system-delivered-by') }}
		<a :href="homepageUrl" target="_blank" class="text-ui-green underline">{{ appName }}</a>
	</footer>
</template>

<script lang="ts" setup>
const appName = import.meta.env.VITE_APP_NAME.toLowerCase();
const homepageUrl = import.meta.env.VITE_HOMEPAGE_URL;
</script>
