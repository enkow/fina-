<template>
	<component v-if="!routingStore.isFirstStep" :is="as || 'button'" @click="routingStore.previousStep">
		<slot />
	</component>
</template>

<script lang="ts" setup>
import { useRoutingStore } from '@/Stores/routing';

defineProps<{
	as?: any;
}>();

const routingStore = useRoutingStore();
</script>
