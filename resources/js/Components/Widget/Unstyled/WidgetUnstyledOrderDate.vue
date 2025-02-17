<template>
	<slot :date="dayjs(widgetStore.form.start_at ?? widgetStore.date).format('DD.MM.YYYY')" />
</template>

<script lang="ts" setup>
import dayjs from 'dayjs';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
