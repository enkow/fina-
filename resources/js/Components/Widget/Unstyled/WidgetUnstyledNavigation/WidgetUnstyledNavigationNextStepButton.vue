<template>
	<component :is="as || 'button'" type="button" @click="handleClick" :disabled="disabled">
		<slot />
	</component>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';
import { hasTime } from '@/Utils';

defineProps<{
	as?: any;
	disabled?: boolean;
}>();

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();

function handleClick() {
	let interval = setInterval(() => {
		if (
			widgetStore.startAtDatesLastLoadingProcess
				? false
				: !hasTime(widgetStore.form.start_at) || widgetStore.price !== null
		) {
			widgetStore.fullLoader = false;
			routingStore.nextStep();
			clearInterval(interval);
		} else {
			widgetStore.fullLoader = true;
		}
	}, 100);
}
</script>
