<template>
	<Widget3Layout :with-reservation-details="true">
		<div class="text-widget w-full">
			<h2 class="text-widget">
				{{ $t('widget.choose-set') }}
			</h2>
			<div class="max-h-66 space-y-2.5 overflow-y-auto">
				<div
					v-for="set in widgetStore.availableSets"
					class="border-widget flex h-25 w-full space-x-2 overflow-hidden rounded-md border-[3px] md:h-20 md:space-x-6">
					<div class="w-30 flex-none md:w-44">
						<img :src="'/images/set-images/' + set.photo" class="hidden select-none md:block w-full h-full object-scale-down" />
						<img :src="'/images/set-images/' + set.mobile_photo" class="block select-none md:hidden w-full h-full object-scale-down" />
					</div>
					<div class="max-w-80 pt-3">
						<p class="text-sm font-extrabold uppercase">{{ set.name }}</p>
						<p class="text-xxs">{{ set.description }}</p>
					</div>
					<div class="flex w-30 flex-grow flex-col items-end justify-between pr-2.5">
						<div class="pt-3">
							<p class="text-sm font-extrabold uppercase">
								{{ formattedFloat(set.price / 100, 2) }}
								{{ widgetStore.getCurrencySymbol() }}
							</p>
						</div>
						<div class="flex items-center space-x-3 pb-2">
							<NumberInput
								:label-width="10"
								:max="set.available"
								:min="0"
								:step="1"
								:value="set['selected']"
								@update="(newValue) => (set['selected'] = newValue)" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</Widget3Layout>
</template>

<script lang="ts" setup>
import Widget3Layout from '@/Layouts/Widget3Layout.vue';
import { useWidgetStore } from '@/Stores/widget';
import { useNumber } from '@/Composables/useNumber';
import NumberInput from '@/Components/Widget-3/Icons/NumberInput.vue';

const widgetStore = useWidgetStore();
const { formattedFloat } = useNumber();
</script>
