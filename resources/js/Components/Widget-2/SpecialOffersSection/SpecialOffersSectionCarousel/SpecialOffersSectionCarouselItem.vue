<template>
	<div
		@click="onClick"
		:class="
			twMerge(
				'flex h-full cursor-pointer flex-col justify-center space-y-1 rounded-2xl border-2 border-transparent bg-ui-green-50 p-6 transition-colors duration-200',
				active && 'border-ui-green bg-ui-green-300',
			)
		">
		<h2 class="my-0 text-sm font-bold text-ui-black">{{ specialOffer.name }}</h2>
		<p class="text-xs text-ui-black/60" v-if="specialOffer.description">{{ specialOffer.description }}</p>
	</div>
</template>

<script lang="ts" setup>
import { SpecialOffer } from '@/Types/models';
import { twMerge } from 'tailwind-merge';

defineProps<{
	active?: boolean;
	onClick?: () => void;
	specialOffer: SpecialOffer;
}>();
</script>
