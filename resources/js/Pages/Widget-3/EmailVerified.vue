<template>
	<Widget3Layout :without-navigation="true" :without-logout="true">
		<div class="text-widget mx-auto flex h-100 w-100 flex-col items-center justify-center text-center">
			<p class="widget-header">{{ $t('widget.success-header') }}</p>
			{{ $t('widget.account-verified-successfully') }}
		</div>
	</Widget3Layout>
</template>

<script lang="ts" setup>
import Widget3Layout from '@/Layouts/Widget3Layout.vue';
</script>
