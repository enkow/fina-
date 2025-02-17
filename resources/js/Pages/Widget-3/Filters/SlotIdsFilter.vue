<template>
	<Popper class="w-[calc(100%+10px)] !border-r-0">
		<button class="w-full">
			<TextInput v-model="selectedSlotsLabel" class="!mx-0 w-full cursor-pointer uppercase" readonly="" />
		</button>
		<template #content>
			<div
				class="border-widget -mt-2 w-[200px] space-y-1 rounded border-[3px] bg-white pb-1 text-xs sm:w-[224px]">
				<div class="text-widget bg-grey-2 px-2 font-bold">
					{{ widgetStore.gameTranslations['any-slot-short'] }}
				</div>
				<div>
					<p
						v-for="slot in slots"
						:class="{
							'bg-widget text-white': widgetStore.form.slot_ids.includes(slot.id),
						}"
						class="lounge-item cursor-pointer px-4 py-1 font-medium uppercase hover:text-white"
						@click="toggleSlotId(slot.id)">
						{{ widgetStore.gameTranslations['slot-singular-short'] }}
						{{ slot.name }}
					</p>
				</div>
			</div>
		</template>
	</Popper>
</template>

<style scoped>
.border-widget {
	border-color: v-bind(widgetColor);
}

.bg-widget {
	background: v-bind(widgetColor);
}

.lounge-item:hover {
	background: v-bind(widgetColor);
}
</style>

<script lang="ts" setup>
import { Slot } from '@/Types/models';
import axios from 'axios';
import { wTrans } from 'laravel-vue-i18n';
import { useWidgetStore } from '@/Stores/widget';
import Popper from 'vue3-popper';
import { capitalize, computed, ref } from 'vue';
import TextInput from '@/Components/Widget-3/TextInput.vue';

const widgetStore: any = useWidgetStore();
const widgetColor: string = widgetStore.widgetColor;

let slots = ref<Slot[] | null>(null);
let dataToSend = JSON.parse(JSON.stringify(widgetStore.form));
dataToSend['club'] = widgetStore.club.slug;
axios.get(route('widget.slots', dataToSend)).then((response: { data: Slot[] }) => {
	slots.value = response.data;
});
let selectedSlotsLabel = computed<string>(() => {
	if (
		widgetStore.form.slot_ids.length === 0 ||
		(widgetStore.form.slot_ids.length === 1 && widgetStore.form.slot_ids[0] === null)
	) {
		return widgetStore.gameTranslations['choose-slot'];
	}
	if (widgetStore.form.slot_ids.length > 3) {
		return wTrans('widget.many-slot-selected-label').value;
	}

	let slotNames: string[] = [];
	widgetStore.form.slot_ids.forEach((slotId: number) => {
		if (slotId) {
			slotNames.push(
				capitalize(widgetStore.gameTranslations['slot-singular-short']) +
					' ' +
          slots.find((slot: Slot) => slot.id === slotId).name
			);
		}
	});
	return slotNames.join(', ');
});

function toggleSlotId(slotId: number) {
	if (widgetStore.form.slot_ids.length === 1 && widgetStore.form.slot_ids[0] === null) {
		widgetStore.form.slot_ids = [];
	}

	if (
		!widgetStore.form.slot_ids.includes(slotId) &&
		widgetStore.form.slots_count > 1 &&
		widgetStore.form.slots_count === widgetStore.form.slot_ids.length
	) {
		widgetStore.alertContent = widgetStore.gameTranslations['all-slots-selected-alert'];
	} else if (
		!widgetStore.form.slot_ids.includes(slotId) &&
		widgetStore.form.slots_count === 1 &&
		widgetStore.form.slots_count === widgetStore.form.slot_ids.length
	) {
		widgetStore.form.slot_ids = [slotId];
	} else if (widgetStore.form.slot_ids.includes(slotId)) {
		for (const key in widgetStore.form.slot_ids) {
			if (widgetStore.form.slot_ids[key] === slotId) {
				delete widgetStore.form.slot_ids[key];
			}
		}
	} else {
		widgetStore.form.slot_ids.push(slotId);
	}
	let newArr: number[] = [];
	widgetStore.form.slot_ids.forEach((slotId: number) => {
		newArr.push(slotId);
	});
	widgetStore.form.slot_ids = newArr;

	widgetStore.reloadPriceTriggerer++;
}
</script>
