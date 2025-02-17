<template>
	<Popper
		:show="show"
		v-click-away="() => (show = false)"
		class="relative !m-0 !block w-full !border-0 text-xs">
		<button
			type="button"
			class="w-full border-b border-ui-green-500 py-2 text-start"
			:class="twMerge('text-ui-black/60', Boolean(activeItem) && 'text-ui-black')"
			@click="show = !show">
			{{ activeItem?.label ?? placeholder }}
		</button>
		<template #content>
			<div class="space-y-3 rounded-lg bg-white p-4.5 shadow-widget-2-select">
				<Widget2DeepSelectItem
					v-for="row in rows"
					:row="row"
					@item-click="onItemClick"
					:active-item="activeItem" />
			</div>
		</template>
	</Popper>
</template>

<script lang="ts" setup>
import Popper from 'vue3-popper';
import Widget2DeepSelectItem from './Widget2DeepSelectItem.vue';
import { ref } from 'vue';
import { computed } from 'vue';
import { twMerge } from 'tailwind-merge';
import type { Row } from './Widget2DeepSelect.types';

const props = defineProps<{
	onItemClick?: (value: string) => void;
	placeholder?: string;
	value: string;
	rows: Row[];
}>();

const show = ref(false);

const data = computed(() =>
	props.rows.reduce<[rowIndex: number, itemIndex: number]>(
		(prev, { items }, rowIndex) => {
			const itemIndex = items.findIndex(({ label, value }) => props.value === label || props.value === value);

			return itemIndex !== -1 ? [rowIndex, itemIndex] : prev;
		},
		[-1, -1],
	),
);

const activeItem = computed(() => props.rows[data.value[0]]?.items[data.value[1]]);
</script>
