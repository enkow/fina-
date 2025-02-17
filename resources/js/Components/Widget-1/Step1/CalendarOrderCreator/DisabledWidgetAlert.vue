<template>
	<WidgetAlert>
		<template #title>{{ $t('calendar.pay-attention') }}</template>
		<template #description>{{ $t('calendar.disabled-widget-info') }}</template>
	</WidgetAlert>
</template>

<script lang="ts" setup>
import WidgetAlert from '@/Components/Widget/Ui/WidgetAlert.vue';
</script>
