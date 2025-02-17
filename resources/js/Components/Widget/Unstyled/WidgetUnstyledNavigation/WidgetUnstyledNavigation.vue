<template>
	<div>
		<slot :nextButtonDisabled="!routingStore.nextStepAvailable" />
	</div>
</template>

<script lang="ts" setup>
import { useRoutingStore } from '@/Stores/routing';

const routingStore = useRoutingStore();
</script>
