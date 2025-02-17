<template>
	<Popper
		:class="twMerge('relative !m-0 !block !border-0', compact && 'text-xs md:text-base')"
		:show="show"
		v-click-away="() => show && (show = false)">
		<button
			type="button"
			:class="
				twMerge(
					'flex h-16 w-full items-center justify-between rounded-2xl bg-ui-green-400 px-5 text-lg text-ui-black',
					compact && 'h-10 text-xs md:text-base',
				)
			"
			@click="show = !show">
			{{ activeItem?.label ?? value }}
			<DropdownIcon class="text-ui-green transition-transform" :class="{ 'rotate-180': show }" />
		</button>
		<template #content>
			<ul class="shadow-calendar-black/10 w-full space-y-3 rounded-lg bg-white p-4.5 shadow-md">
				<li
					v-for="{ value, label } in items"
					@click="
						() => {
							show = false;
							onChange?.(value);
						}
					"
					class="flex cursor-pointer items-center justify-between transition-colors"
					:class="props.value === value ? 'text-ui-black' : 'text-ui-black/60 hover:text-ui-green'">
					{{ label }}
					<CheckIconBold v-if="props.value === value" class="text-ui-green" />
				</li>
			</ul>
		</template>
	</Popper>
</template>

<script lang="ts" setup>
import Popper from 'vue3-popper';
import DropdownIcon from '../Icons/DropdownIcon.vue';
import CheckIconBold from '../Icons/CheckIconBold.vue';
import { twMerge } from 'tailwind-merge';
import { computed, ref } from 'vue';

const props = defineProps<{
	compact?: boolean;
	value: string;
	items: Item[];
	onChange?: (value: string) => void;
}>();

const show = ref(false);
const activeItem = computed(() => props.items.find(({ value }) => props.value === value));

interface Item {
	readonly value: string;
	readonly label: string;
}
</script>

<style>
.popper {
	@apply !inset-0;
}
</style>
