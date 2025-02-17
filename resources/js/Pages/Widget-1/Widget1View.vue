<template>
	<Widget1Layout>
		<WidgetRouting :steps="steps" v-slot="{ step }">
			<component :is="step" />
		</WidgetRouting>
	</Widget1Layout>
</template>

<script lang="ts" setup>
import Widget1Layout from '@/Layouts/Widget1Layout.vue';
import WidgetRouting from '@/Components/Widget/WidgetRouting.vue';
import Step1 from './Step1.vue';
import Step2 from './Step2.vue';
import Step3 from './Step3.vue';
import Step4 from './Step4.vue';
import { computed, watch } from 'vue';
import { Step } from '@/Types/routing';
import { useWidgetStore } from '@/Stores/widget';
import { hasTime } from '@/Utils';

const widgetStore = useWidgetStore();

const steps = computed<Step[]>(() => [
	{
		component: Step1,
		available:
			Boolean(widgetStore.form.game_id) &&
			(Boolean(widgetStore.gameFeatures.person_as_slot.length) || Boolean(widgetStore.form.slots_count)) &&
			(!widgetStore.isSlotSelectionEnabled ||
				!widgetStore.showingStatuses.slotHasType ||
				Boolean(widgetStore.form.features[widgetStore.gameFeatures.slot_has_type[0]?.id]?.name)) &&
			(!widgetStore.isSlotSelectionEnabled ||
				!widgetStore.showingStatuses.slotHasSubType ||
				Boolean(widgetStore.form.features[widgetStore.gameFeatures.slot_has_subtype[0]?.id]?.name)) &&
			(!widgetStore.showingStatuses.pricePerPerson ||
				widgetStore.form.features[widgetStore.gameFeatures.price_per_person[0].id].person_count > 0) &&
			(hasTime(widgetStore.form.start_at) || widgetStore.showingStatuses.fullDayReservations) &&
			(widgetStore.gameFeatures.slot_has_parent.length === 0 ||
				!!widgetStore.form.features[widgetStore.gameFeatures.slot_has_parent[0].id]['parent_slot_id']) &&
			(widgetStore.gameFeatures.book_singular_slot_by_capacity.length === 0 ||
				!!widgetStore.form.features[widgetStore.gameFeatures.book_singular_slot_by_capacity[0].id][
					'capacity'
				]),
	},
	{ component: Step2, hidden: widgetStore.availableSets.length === 0 },
	{ component: Step3 },
	{ component: Step4 },
]);

widgetStore.reloadSets();
</script>
