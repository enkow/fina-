<template>
	<WidgetUnstyledStepper
		class="flex gap-x-2 md:gap-x-3.75"
		step-class="w-6 h-6 md:w-11.5 md:h-11.5 flex items-center justify-center rounded-full text-xs md:text-lg font-medium md:font-bold border-2 text-ui-green-950 bg-white border-ui-green"
		:active-step-class="ACTIVE_CLASS"
		:complete-step-class="ACTIVE_CLASS">
		<template #complete-icon>
			<CheckIcon class="w-3 md:w-full" />
		</template>
	</WidgetUnstyledStepper>
</template>

<script lang="ts" setup>
import WidgetUnstyledStepper from '@/Components/Widget/Unstyled/WidgetUnstyledStepper/WidgetUnstyledStepper.vue';
import CheckIcon from '@/Components/Widget-2/Icons/CheckIcon.vue';

const ACTIVE_CLASS = 'text-white bg-ui-green border-transparent';
</script>
