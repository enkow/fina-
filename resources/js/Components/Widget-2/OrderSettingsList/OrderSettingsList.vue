<template>
	<ul
		:class="
			twMerge(
				'mt-2 flex w-full flex-col justify-center gap-y-2 rounded-lg xxs:mt-5 xxs:mt-5 xxs:gap-y-5 md:mt-0 md:bg-ui-green-50 md:px-5 md:py-1',
				availability >= 4 && 'md:gap-y-3.5',
				availability >= 5 && 'md:gap-y-2',
			)
		">
		<DurationSetting />
		<PricePerPersonSetting />
		<SlotsCountSetting />
		<PersonAsSlotSetting />
		<SlotHasTypeSetting />
		<SlotHasSubTypeSetting />
		<SlotConveniencesSetting />
		<SlotHasSubslotsSetting />
		<BookSingularSlotByCapacitySetting />
	</ul>
</template>

<script lang="ts" setup>
import DurationSetting from './DurationSetting.vue';
import PricePerPersonSetting from './PricePerPersonSetting.vue';
import SlotsCountSetting from './SlotsCountSetting.vue';
import PersonAsSlotSetting from './PersonAsSlotSetting.vue';
import SlotHasTypeSetting from './SlotHasTypeSetting.vue';
import SlotHasSubTypeSetting from './SlotHasSubTypeSetting.vue';
import SlotConveniencesSetting from './SlotConveniencesSetting.vue';
import SlotHasSubslotsSetting from './SlotHasSubslotsSetting.vue';
import BookSingularSlotByCapacitySetting from './BookSingularSlotByCapacitySetting.vue';
import { useWidgetAvailableSettings } from '@/Composables/useWidgetAvailableSettings';
import { twMerge } from 'tailwind-merge';

const availability = useWidgetAvailableSettings();
</script>
