<template>
	<li
		class="flex items-center gap-3.5 text-center text-sm font-bold transition-colors md:flex-col md:text-lg lg:flex-row lg:justify-center"
		:class="[
			disabled ? 'text-ui-black/15' : isActive || isComplete ? 'text-ui-green' : 'text-ui-black/40',
			...(!isActive && !isComplete && currentStep + 1 === step ? ['ml-auto md:ml-0'] : []),
		]">
		<div
			class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full transition-colors md:h-13 md:w-13"
			:class="
				disabled
					? 'bg-ui-black/10 text-white'
					: isActive || isComplete
					? 'bg-ui-green text-white'
					: 'border-2 border-ui-black/40'
			">
			<CheckIcon v-if="isComplete" />
			<span v-else>{{ step }}</span>
		</div>
		<span :class="isActive ? 'inlne' : 'hidden md:inline'">{{ name }}</span>
	</li>
</template>

<script lang="ts" setup>
import CheckIcon from '@/Components/Widget/Icons/CheckIcon.vue';

const { currentStep } = defineProps<{
	disabled?: boolean;
	step: number;
	currentStep: number;
	name: string;
	isActive: boolean;
	isComplete: boolean;
}>();
</script>
