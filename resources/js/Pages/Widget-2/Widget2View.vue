<template>
	<Widget2Layout :with-navigation="![0, 5].includes(routingStore.stepIndex)">
		<WidgetRouting :steps="steps" v-slot="{ step }">
			<component :is="step" />
		</WidgetRouting>
	</Widget2Layout>
</template>

<script lang="ts" setup>
import Widget2Layout from '@/Layouts/Widget2Layout.vue';
import WidgetRouting from '@/Components/Widget/WidgetRouting.vue';
import GamesStep from './Steps/GamesStep.vue';
import DetailsStep from './Steps/DetailsStep.vue';
import TimeStep from './Steps/TimeStep.vue';
import SetsStep from './Steps/SetsStep.vue';
import SummaryStep from './Steps/SummaryStep.vue';
import StatusStep from './Steps/StatusStep.vue';
import { useRoutingStore } from '@/Stores/routing';
import { useWidgetStepsAvailability } from '@/Composables/useWidgetStepsAvailability';
import { Step } from '@/Types/routing';
import { computed } from 'vue';

const routingStore = useRoutingStore();

const { details, time, sets, gamesHidden, setsHidden, timeHidden } = useWidgetStepsAvailability();

const steps = computed<Step[]>(() => [
	{ component: GamesStep, hidden: gamesHidden.value },
	{ component: DetailsStep, available: details.value },
	{ component: TimeStep, available: time.value, hidden: timeHidden.value },
	{ component: SetsStep, available: sets.value, hidden: setsHidden.value },
	{ component: SummaryStep },
	{ component: StatusStep },
]);
</script>
