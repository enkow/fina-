<template>
	<component v-if="!routingStore.isFirstStep" :is="as || 'button'" @click="widgetStore.firstStep">
		<slot />
	</component>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';

defineProps<{
	as?: any;
}>();

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();
</script>
