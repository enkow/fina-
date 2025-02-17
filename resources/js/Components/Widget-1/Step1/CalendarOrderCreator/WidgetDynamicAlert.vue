<template>
	<WidgetAlert v-if="widgetStore.announcementContent">
		<template #title>{{ $t('calendar.pay-attention') }}</template>
		<template #description>{{ widgetStore.announcementContent }}</template>
	</WidgetAlert>
</template>

<script lang="ts" setup>
import WidgetAlert from '@/Components/Widget/Ui/WidgetAlert.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
