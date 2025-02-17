<template>
	<slot :blocks="blocks" :back="() => routingStore.previousStep()" />
</template>

<script lang="ts" setup>
import { computed } from 'vue';
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();

const blocks = computed(() => widgetStore.datetimeBlocks);
</script>
