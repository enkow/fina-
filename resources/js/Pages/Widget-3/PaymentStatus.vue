<template>
	<Widget3Layout :without-navigation="true" :without-logout="true">
		<div class="mx-auto flex h-100 w-100 max-w-full flex-col items-center justify-center text-center">
			<p class="widget-header">{{ $t('widget.payment-status') }}</p>
			{{ $t('widget.payment-status-content') }}
		</div>
	</Widget3Layout>
</template>

<script lang="ts" setup>
import Widget3Layout from '@/Layouts/Widget3Layout.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
const widgetColor = widgetStore.widgetColor;
</script>
