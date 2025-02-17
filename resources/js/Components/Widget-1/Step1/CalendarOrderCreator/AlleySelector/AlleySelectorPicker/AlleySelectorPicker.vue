<template>
	<ol class="grid grid-cols-3 gap-5 xs:grid-cols-5 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-11">
		<li v-for="slot in slots" class="flex-1">
			<AlleySelectorPickerButton
				:slot="slot"
				:selected="isSlotSelected(slot)"
                :message="!isSlotSelected(slot) ? isMaxSlotExceedMessage : null"
				@click="handleSlotClick(slot)" />
            </li>
	</ol>
</template>

<script lang="ts" setup>
import AlleySelectorPickerButton from './AlleySelectorPickerButton.vue';
import { watch } from 'vue';
import { computed } from 'vue';
import { useWidgetStore } from '@/Stores/widget';
import { Slot } from '@/Types/models';
import { wTrans } from 'laravel-vue-i18n';

const widgetStore = useWidgetStore();

const isSlotSelected = (slot: Slot) => widgetStore.form.slot_ids.includes(slot.id);

const handleSlotClick = (slot: Slot) => {
	if (isSlotSelected(slot)) {
		widgetStore.form.slot_ids = widgetStore.form.slot_ids.filter((id: number) => id !== slot.id);
		widgetStore.form.slots_count--;
	} else {
        if (isMaxSlotExceed.value) return;

		widgetStore.form.slot_ids.push(slot.id);
		widgetStore.form.slots_count++;
	}
};

watch(
	[
		widgetStore.form.features[widgetStore.gameFeatures.slot_has_subtype[0]?.id],
		...widgetStore.gameFeatures.slot_has_convenience.map(
			({ id }: { id: number }) => widgetStore.form.features[id],
		),
	],
	() => {
		widgetStore.resetSlots();
	},
);

const slots = computed(() => widgetStore.slots.sort((a, b) => a.id - b.id));

const isMaxSlotExceed = computed(
    () => {
        const maxSlots = widgetStore.specialOfferSlotsCount ?? widgetStore.gameDateWidgetSlotsMaxLimit;

        return maxSlots <= widgetStore.form.slots_count && maxSlots !== 0;
    }
);

const isMaxSlotExceedMessage = computed(() => {
    
    return isMaxSlotExceed.value? wTrans('slot.max-slot-exceeded').value: null;
})
</script>
