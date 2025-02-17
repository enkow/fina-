<template>
	<Popper class="w-[calc(100%+10px)] !border-r-0">
		<button class="w-full">
			<TextInput v-model="selectedSlotsLabel" class="cursor-pointer uppercase" readonly="" />
		</button>
		<template #content>
			<div
				class="border-widget -mt-2 w-[200px] space-y-1 rounded border-[3px] bg-white pt-1 text-xs sm:w-[224px]">
				<div class="text-widget bg-grey-2 px-2 font-bold">
					{{ widgetStore.gameTranslations['any-slot-short'] }}
				</div>
				<div v-for="key in Object.keys(slotsGroupedByLoungeCapacity).sort()">
					<div
						v-if="key !== '0-0'"
						class="text-widget mb-1 flex cursor-pointer justify-between px-2 font-bold uppercase"
						@click="toggleLoungeExpandedStatus(key)">
						<p v-if="key !== '0-0'">
							{{ widgetStore.gameFeatures.slot_has_lounge[0].translations['slots-capacity'] }}
							{{ key }}
						</p>
						<ChevronDownIcon class="h-2.5 w-2.5" />
					</div>
					<div v-if="slotsGroupedByLoungeExpandedStatuses[key] === true && key !== '0-0'">
						<p
							v-for="slot in slotsGroupedByLoungeCapacity[key]"
							:class="{
								'bg-widget text-white': widgetStore.form.slot_ids.includes(slot.id),
							}"
							class="lounge-item cursor-pointer px-4 py-1 font-medium uppercase hover:text-white"
							@click="toggleLoungeSlotId(slot.id)">
							{{ widgetStore.gameTranslations['slot-singular-short'] }}
							{{ slot.name }}
						</p>
					</div>
				</div>
				<div v-if="slotsGroupedByLoungeCapacity['0-0']">
					<div
						class="text-widget mb-1 flex cursor-pointer justify-between px-2 font-bold uppercase"
						@click="toggleLoungeExpandedStatus('0-0')">
						{{ widgetStore.gameFeatures.slot_has_lounge[0].translations['slots-others'] }}
						<ChevronDownIcon class="h-2.5 w-2.5" />
					</div>
					<div v-if="slotsGroupedByLoungeExpandedStatuses['0-0'] === true">
						<p
							v-for="slot in slotsGroupedByLoungeCapacity['0-0']"
							:class="{
								'bg-widget text-white': widgetStore.form.slot_ids.includes(slot.id),
							}"
							class="lounge-item cursor-pointer px-4 py-1 font-medium uppercase hover:text-white"
							@click="toggleLoungeSlotId(slot.id)">
							{{ widgetStore.gameTranslations['slot-singular-short'] }}
							{{ slot.name }}
						</p>
					</div>
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
import { Feature, Slot } from '@/Types/models';
import axios from 'axios';
import { wTrans } from 'laravel-vue-i18n';
import { useWidgetStore } from '@/Stores/widget';
import Popper from 'vue3-popper';
import ChevronDownIcon from '@/Components/Dashboard/Icons/ChevronDownIcon.vue';
import { computed, ref } from 'vue';
import TextInput from '@/Components/Widget-3/TextInput.vue';

const widgetStore = useWidgetStore();
const widgetColor: string = widgetStore.widgetColor;

let slotsGroupedByLoungeCapacity: { [key: string]: Slot[] } = {};
let slotsGroupedByLoungeExpandedStatuses = ref<{ [key: string]: boolean }>({});
let slots: Slot[] | null = null;
axios
	.get(
		route('widget.slots', {
			club: widgetStore.club.slug,
			game_id: widgetStore.form.game_id,
			start_at: widgetStore.form.start_at,
			duration: widgetStore.form.duration,
		}),
	)
	.then((response: { data: Slot[] }) => {
		slots = response.data;
		let others = [];
		response.data.forEach((slot: Slot) => {
			let slotFeatureData = JSON.parse(
				slot.features.find((feature: Feature) => feature.type === 'slot_has_lounge')?.pivot?.data ?? '{}',
			);

			let key = null;
			if (slotFeatureData.min && slotFeatureData.max) {
				key = `${slotFeatureData.min}-${slotFeatureData.max}`;
			} else {
				key = '0-0';
			}

			if (slotsGroupedByLoungeCapacity.hasOwnProperty(key)) {
				slotsGroupedByLoungeCapacity[key].push(slot);
				slotsGroupedByLoungeExpandedStatuses.value[key] = false;
			} else {
				slotsGroupedByLoungeCapacity[key] = [slot];
			}
		});
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
				widgetStore.gameTranslations['slot-singular-short'] +
					' ' +
					slots?.find((slot: Slot) => slot.id === slotId)?.name ?? 'x',
			);
		}
	});
	return slotNames.join(', ');
});
const keysArray = Object.keys(slotsGroupedByLoungeCapacity);
keysArray.sort();

function toggleLoungeSlotId(slotId: number) {
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
}

const sortedObj: { [key: string]: Slot[] } = {};
for (const key of keysArray) {
	sortedObj[key] = slotsGroupedByLoungeCapacity[key];
}
slotsGroupedByLoungeCapacity = sortedObj;

function toggleLoungeExpandedStatus(key: string) {
	slotsGroupedByLoungeExpandedStatuses.value[key] = !slotsGroupedByLoungeExpandedStatuses.value[key];
}
</script>
