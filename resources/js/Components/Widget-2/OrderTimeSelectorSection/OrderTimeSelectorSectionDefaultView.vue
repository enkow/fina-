<template>
	<SectionTitle>{{ $t('widget-2.select-an-hour') }}</SectionTitle>
	<WidgetUnstyledTimeSelectorList
		:blocks="blocks"
		class="widget-2-scrollbar grid -translate-y-2 grid-cols-5 gap-2 overflow-auto pt-2 text-xs font-medium xs:grid-cols-6 sm:grid-cols-8 md:grid-cols-11">
		<template #item="{ active, discount, time, onClick }">
			<button
				type="button"
				@click="onClick"
				:class="
					twJoin(
						'relative w-full rounded-lg border-2 border-ui-green-200 py-1.5 text-center transition-colors duration-200',
						active && 'border-ui-green-500 bg-ui-green text-white',
					)
				">
				{{ time }}
				<DiscountIcon
					v-if="discount"
					:class="twJoin('absolute -top-2.5 right-0 text-black', active && 'text-ui-green-950')" />
			</button>
		</template>
	</WidgetUnstyledTimeSelectorList>
</template>

<script lang="ts" setup>
import WidgetUnstyledTimeSelectorList from '@/Components/Widget/Unstyled/WidgetUnstyledTimeSelector/WidgetUnstyledTimeSelectorList.vue';
import SectionTitle from '../SectionTitle.vue';
import DiscountIcon from '@/Components/Widget-2/Icons/DiscountIcon.vue';
import { twJoin } from 'tailwind-merge';

defineProps<{
	blocks: string[];
}>();
</script>
