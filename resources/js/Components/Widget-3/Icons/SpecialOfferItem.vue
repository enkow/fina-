<template>
	<div
		:class="{
			active: widgetStore.form.special_offer_id === specialOffer.id,
		}"
		:style="{ borderColor: widgetStore.widgetColor }"
		class="special-offers__item"
		@click="widgetStore.selectSpecialOffer(specialOffer)">
		<div class="space-y-0.5 text-center">
			<p class="text-sm font-extrabold uppercase">{{ specialOffer.name }}</p>
			<p
				v-if="specialOffer.description"
				v-for="i in Math.min(specialOffer.description.split('\n').length, 3)"
				class="text-xs font-bold uppercase">
				{{ specialOffer.description.split('\n')[i - 1] }}
			</p>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { SpecialOffer } from '@/Types/models';

const props = defineProps<{
	specialOffer: SpecialOffer;
}>();

const widgetStore = useWidgetStore();
const widgetColor = widgetStore.widgetColor;
</script>

<style scoped>
.special-offers__item {
	@apply mx-0.75 flex h-[96px] w-full cursor-pointer items-center justify-center rounded-md border-[3px] px-0.25 transition-all duration-100 md:w-[218px];
	color: v-bind(widgetColor);

	&.active {
		@apply text-white;
		background-color: v-bind(widgetColor);
	}
}
</style>
