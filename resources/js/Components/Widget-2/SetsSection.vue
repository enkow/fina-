<template>
	<section class="flex flex-col overflow-auto">
		<SectionTitle>{{ $t('widget-2.choose-addons') }}</SectionTitle>
		<WidgetUnstyledSetList
			id="sets"
			class="widget-2-scrollbar grid gap-x-5 gap-y-2 overflow-auto pb-0.5 pl-0.5 md:h-55 md:grid-cols-3 md:gap-y-5">
			<template #item="{ set, price, currency, min, max }">
				<div class="flex gap-x-2.5 gap-y-3 md:flex-col">
					<img
						:src="`/images/set-images/${set.photo}`"
						class="aspect-square h-22 rounded-2xl object-cover md:h-32 md:w-full md:rounded-b-none" />
					<div class="flex flex-1 flex-col">
						<div class="flex items-center justify-between text-sm">
							<p class="font-bold text-ui-black">
								{{ set.name }}
							</p>
							<p class="font-medium text-ui-green md:text-lg">{{ price }} {{ currency }}</p>
						</div>
						<div class="mt-auto grid grid-cols-3 xs:grid-cols-5 md:mt-2.5 md:block">
							<WidgetNumberInput
								v-if="typeof set.selected === 'number'"
								class="col-span-3 xxs:col-span-2 xs:col-span-4"
								:min="min"
								:max="max"
								v-model="set.selected"
								compact />
						</div>
					</div>
				</div>
			</template>
		</WidgetUnstyledSetList>
	</section>
</template>

<script lang="ts" setup>
import WidgetUnstyledSetList from '@/Components/Widget/Unstyled/WidgetUnstyledSetList.vue';
import WidgetNumberInput from '@/Components/Widget/Ui/WidgetNumberInput.vue';
import SectionTitle from './SectionTitle.vue';
</script>

<style>
/* add styles only for Firefox */
@-moz-document url-prefix() {
	#sets {
		@apply pr-3 scrollbar-thin;
	}
}
</style>
