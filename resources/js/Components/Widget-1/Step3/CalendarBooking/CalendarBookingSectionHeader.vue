<template>
	<div
		:class="
			twMerge('mb-4 flex items-center justify-between', priceBeforeDiscount && priceBeforeDiscount !== price && 'items-start')
		">
		<div class="font-bold text-ui-black">
			<p>{{ title }}</p>
			<p v-if="subTitle">{{ subTitle }}</p>
		</div>
		<div class="text-end">
			<p v-if="price !== priceBeforeDiscount && priceBeforeDiscount" class="line-through">
				{{ formatPrice(priceBeforeDiscount) }} {{ widgetStore.getCurrencySymbol() }}
			</p>
			<p class="text-xl font-medium text-ui-green md:text-2xl">
				{{ formatPrice(price) }} {{ widgetStore.getCurrencySymbol() }} 
			</p>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { formatPrice } from '@/Utils';
import { twMerge } from 'tailwind-merge';

defineProps<{
	title: string;
	subTitle?: string;
	price: number;
	priceBeforeDiscount?: number;
}>();

const widgetStore = useWidgetStore();
</script>
