<template>
	<div class="space-y-2">
		<button type="button" class="flex w-full items-center justify-between" @click="show = !show">
			<p>{{ row.label }}</p>
			<DropdownIcon :class="twMerge('w-3 transition-transform duration-200', show && 'rotate-180')" />
		</button>
		<ul v-if="show" class="space-y-3 rounded-lg">
			<li
				v-for="{ label, value } in row.items"
				:class="
					twMerge(
						'flex cursor-pointer items-center justify-between text-ui-black/60',
						activeItem?.value === value && 'text-ui-black',
					)
				"
				@click="onItemClick?.(value)">
				{{ label }}
				<CheckIconBold v-if="activeItem?.value === value" class="w-3 text-ui-green" />
			</li>
		</ul>
	</div>
</template>

<script lang="ts" setup>
import DropdownIcon from '@/Components/Widget/Icons/DropdownIcon.vue';
import CheckIconBold from '@/Components/Widget/Icons/CheckIconBold.vue';
import { twMerge } from 'tailwind-merge';
import { ref } from 'vue';
import type { Row, Item } from './Widget2DeepSelect.types';

defineProps<{
	onItemClick?: (value: string) => void;
	activeItem?: Item;
	row: Row;
}>();

const show = ref(false);
</script>
