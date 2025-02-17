<template>
	<Popper
		:show="show"
		:class="twMerge('relative !m-0 !block h-6.5 !border-0 text-xs [&>div]:h-full', fill && 'w-full')"
		v-click-away="() => (show = false)">
		<button
			type="button"
			@click="show = !show"
			:class="
				twMerge(
					'flex h-full w-full items-center justify-between border-b border-ui-green-500 text-ui-black/60',
					Boolean(activeItem) && 'text-ui-black',
				)
			">
			{{ activeItem?.label ?? placeholder }}
			<DropdownIcon
				:class="twMerge('text-ui-black transition-transform duration-200', show && 'rotate-180')" />
		</button>
		<template #content>
			<ul class="space-y-3 rounded-lg bg-white p-4.5 shadow-widget-2-select">
				<li
					v-for="{ label, value } in items"
					:class="
						twMerge(
							'flex cursor-pointer items-center justify-between text-ui-black/60',
							activeItem?.value === value && 'text-ui-black',
						)
					"
					@click="handleItemClick(value)">
					{{ label }}
					<CheckIconBold v-if="activeItem?.value === value" class="text-ui-green" />
				</li>
			</ul>
		</template>
	</Popper>
</template>

<script lang="ts" setup>
import Popper from 'vue3-popper';
import CheckIconBold from '../Widget/Icons/CheckIconBold.vue';
import DropdownIcon from '../Widget/Icons/DropdownIcon.vue';
import { ref } from 'vue';
import { computed } from 'vue';
import { twMerge } from 'tailwind-merge';

const props = defineProps<{
	modelValue?: string | null;
	value?: string;
	placeholder?: string;
	fill?: boolean;
	onChange?: (value: string) => void;
	items: Item[];
}>();
const emit = defineEmits(['update:modelValue']);

const show = ref(false);

const activeItem = computed(() =>
	props.items.find(({ value }) => props.modelValue === value || props.value === value),
);

const handleItemClick = (value: string) => {
	show.value = false;

	if (props.value) {
		props.onChange?.(value);
	} else {
		emit('update:modelValue', value);
	}
};

interface Item {
	readonly label: string;
	readonly value: string;
}
</script>
