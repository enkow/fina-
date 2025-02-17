<template>
	<WidgetRouting :steps="steps" v-slot="{ step }">
		<component :is="step" />
	</WidgetRouting>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import Game from '@/Pages/Widget-3/Steps/Game.vue';
import Details from '@/Pages/Widget-3/Steps/Details.vue';
import Time from '@/Pages/Widget-3/Steps/Time.vue';
import Sets from '@/Pages/Widget-3/Steps/Sets.vue';
import Summary from '@/Pages/Widget-3/Steps/Summary.vue';
import Status from '@/Pages/Widget-3/Partials/Status.vue';
import WidgetRouting from '@/Components/Widget/WidgetRouting.vue';
import { watch, computed } from 'vue';
import { Step } from '@/Types/routing';
import { useWidgetStepsAvailability } from '@/Composables/useWidgetStepsAvailability';

const widgetStore = useWidgetStore();

const { details, time, sets, gamesHidden, setsHidden, timeHidden } = useWidgetStepsAvailability();

const steps = computed<Step[]>(() => [
	{ component: Game, hidden: gamesHidden.value },
	{ component: Details, available: details.value },
	{ component: Time, available: time.value, hidden: timeHidden.value },
	{ component: Sets, available: sets.value, hidden: setsHidden.value },
	{ component: Summary },
	{ component: Status },
]);

[widgetStore.form.features, computed(() => widgetStore.form.slots_count)].forEach((variable) => {
	watch(variable, () => {
		widgetStore.loadAvailableStartAtDatesTriggerer++;
	});
});
</script>

<style scoped>
root::-webkit-scrollbar-track {
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
	background-color: #f5f5f5;
}

root::-webkit-scrollbar {
	width: 6px;
	background-color: #f5f5f5;
}

root::-webkit-scrollbar-thumb {
	background-color: v-bind(widgetColor);
}
</style>
