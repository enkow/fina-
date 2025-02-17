<template>
	<div class="!mt-8 flex justify-between">
		<WidgetButton color="green" class="!w-fit" @click="routingStore.previousStep">
			<ArrowRightIcon class="rotate-180" />
			<span class="hidden sm:inline">{{ $t('calendar.come-back') }}</span>
		</WidgetButton>
		<WidgetButton v-if="routingStore.stepIndex !== 2" class="!w-fit" @click="routingStore.nextStep">
			<span class="hidden sm:inline">{{ $t('calendar.continue') }}</span>
			<ArrowRightIcon />
		</WidgetButton>
	</div>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import ArrowRightIcon from '../Icons/ArrowRightIcon.vue';
import { useRoutingStore } from '@/Stores/routing';

const routingStore = useRoutingStore();
</script>
