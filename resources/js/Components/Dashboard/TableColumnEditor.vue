<template>
	<Popper placement="bottom-end" :show="showingDebounced">
		<slot>
			<button class="ml-3 h-12 rounded-md border border-gray-2 p-3 shadow" @click="toggle">
				<svg
					class="h-6 w-6"
					fill="none"
					stroke="currentColor"
					stroke-width="1.5"
					viewBox="0 0 24 24"
					xmlns="http://www.w3.org/2000/svg">
					<path
						d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
						stroke-linecap="round"
						stroke-linejoin="round" />
					<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
			</button>
		</slot>
		<template #content>
			<div class="rounded-md bg-white shadow">
				<draggable
					v-click-away="hide"
					:list="columnList"
					class="list-group w-full"
					ghost-class="ghost"
					item-key="name"
					@change="updatePreferences"
					@end="dragging = false"
					@start="dragging = true">
					<template #item="{ element }">
						<div class="flex justify-between space-x-10 border-b py-2">
							<div class="flex items-center">
								<svg
									class="h-5 w-5 cursor-pointer text-gray-4"
									fill="none"
									stroke="currentColor"
									stroke-width="1.5"
									viewBox="0 0 24 24"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"
										stroke-linecap="round"
										stroke-linejoin="round" />
								</svg>
								{{ Object.keys(header).includes(element.key) ? header[element.key] : element.key }}
							</div>
							<div class="pr-2">
								<Checkbox :checked="element.enabled" @change="toggleColumn($event, element.key)" />
							</div>
						</div>
					</template>
				</draggable>
			</div>
		</template>
	</Popper>
</template>

<script lang="ts" setup>
import Popper from 'vue3-popper';
import {computed, ref, watch} from 'vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import { router, usePage } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import { Dayjs } from 'dayjs';
import dayjs from 'dayjs';
import { refDebounced } from '@vueuse/core';
import { usePanelStore } from '@/Stores/panel';

const props = defineProps<{
	tableName: string;
	tableNamePreferencePostfix?: string;
	disabled?: Array<string>;
	header: {
		[key: string]: string;
	};
	items: Object;
}>();

const emit = defineEmits<{
	(e: 'update', reservationNumber: string): void;
	(e: 'cancel', reservationNumber: string): void;
	(e: 'close'): void;
}>();

const panelStore = usePanelStore();

const showing = ref<boolean>(false);
const showingDebounced = refDebounced(showing, 10);

const dragging = ref<boolean>(false);

const tablePreferenceName: string =
	props.tableName + (props.tableNamePreferencePostfix ? '_' + props.tableNamePreferencePostfix : '');

interface ColumnType {
	key: string;
	name?: string;
	enabled: boolean;
}

const columnList = computed<Array<ColumnType>>(() => {
  let result: Array<ColumnType> = [];
  for (let index in usePage().props.tablePreferences[tablePreferenceName]) {
    let item: ColumnType = usePage().props.tablePreferences[tablePreferenceName][index];
    if (props.disabled?.length === 0 || !props.disabled?.includes(item.key)) {
      result.push({
        key: item.key,
        name: Object.keys(props.header).includes(item.key) ? props.header[item.key] : item.key,
        enabled: item.enabled,
      });
    }
  }
  return result;
});

function toggleColumn(event: Event, elementKey: string): void {
	const target = event.target as HTMLInputElement;
	columnList.value.forEach((element, index) => {
		if (element.key === elementKey) {
			columnList.value[index].enabled = target.checked;
		}
	});
	updatePreferences();
}

async function updatePreferences() {
	let array: Array<ColumnType> = [];
	columnList.value.forEach((element, index) => {
		array.push({
			key: element.key,
			enabled: element.enabled,
		});
	});
	props.disabled?.forEach((element) => {
		array.push({
			key: element,
			enabled: true,
		});
	});

	router.put(
		route('table-preferences.update', { table_name: props.tableName }),
		{ data: array, tableNamePostfix: props.tableNamePreferencePostfix },
		{
			preserveState: true,
			preserveScroll: true,
			onSuccess: () => {
				panelStore.customTablePreferences[tablePreferenceName] = array;
			},
		},
	);
}

function hide() {
	showing.value = false;
}

function toggle() {
	setTimeout(() => {
		if (showing.value === false) {
			showing.value = !showingDebounced.value;
		}
	}, 1);
}
</script>
