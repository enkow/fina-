<template>
	<section v-if="availableSettings" class="flex flex-col">
		<div class="grid grid-cols-1 items-end gap-x-10 gap-y-7 lg:grid-cols-3 order-2">
			<PricePerPersonSetting v-if="isSomeSlotIsSelected" :key="widgetStore.form.slots_count" />
			<PersonAsSlotSetting />
			<SlotHasTypeSetting />
			<SlotHasSubTypeSetting />
			<SlotHasSubslotsSetting />
			<BookSingularSlotByCapacitySetting />
		</div>
		<SectionTitle class="title">{{ $t('calendar.game-settings') }}</SectionTitle>
	</section>
</template>

<script lang="ts" setup>
import PricePerPersonSetting from './PricePerPersonSetting.vue';
import PersonAsSlotSetting from './PersonAsSlotSetting.vue';
import SectionTitle from '../SectionTitle.vue';
import SlotHasTypeSetting from './SlotHasTypeSetting.vue';
import SlotHasSubTypeSetting from './SlotHasSubTypeSetting.vue';
import SlotHasSubslotsSetting from './SlotHasSubslotsSetting.vue';
import BookSingularSlotByCapacitySetting from './BookSingularSlotByCapacitySetting.vue';
import { useWidgetStore } from '@/Stores/widget';
import { watch, computed } from 'vue';
import { useWidgetAvailableSettings } from '@/Composables/useWidgetAvailableSettings';

const widgetStore = useWidgetStore();
const availableSettings = useWidgetAvailableSettings([
	'pricePerPerson',
	'personAsSlot',
	'slotHasType',
	'slotHasSubType',
	'slotHasSubslots',
	'bookSingularSlotByCapacity',
]);

watch(
	() => widgetStore.form.game_id,
	() => {
		widgetStore.resetForm();
	},
);

const isSomeSlotIsSelected = computed<boolean>(() => {
	return widgetStore.form.slots_count > 0;
})
</script>

<style scoped>
.title {
 @apply hidden;
}

:not(:empty) + .title {
  @apply block;
}
</style>
