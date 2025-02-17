<template>
	<CalendarStepper />
	<div class="flex flex-col gap-10 lg:flex-row">
		<div class="flex-1 space-y-11.5">
			<CalendarOrderSets />
		</div>
		<CalendarOrderSetsSummary />
	</div>
	<CalendarOrderSummary />
	<CalendarNavigation />
</template>

<script lang="ts" setup>
import CalendarStepper from '@/Components/Widget-1/Shared/CalendarStepper/CalendarStepper.vue';
import CalendarOrderSets from '@/Components/Widget-1/Step2/CalendarOrderSets/CalendarOrderSets.vue';
import CalendarOrderSetsSummary from '@/Components/Widget-1/Step2/CalendarOrderSetsSummary/CalendarOrderSetsSummary.vue';
import CalendarOrderSummary from '@/Components/Widget-1/Shared/CalendarOrderSummary/CalendarOrderSummary.vue';
import CalendarNavigation from '@/Components/Widget-1/Shared/CalendarNavigation.vue';
</script>
