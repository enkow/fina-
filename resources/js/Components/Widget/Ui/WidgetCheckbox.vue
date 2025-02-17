<template>
	<label class="flex w-fit cursor-pointer items-center gap-x-3 text-xs text-ui-black/60 md:text-base">
		<input type="checkbox" class="peer hidden" v-bind="$attrs" v-model="proxyChecked" :value="value" />
		<button
			type="button"
			:class="
				twMerge(
					'pointer-events-none flex shrink-0 items-center justify-center rounded-md border-2 text-xs transition-colors duration-100 [&>svg]:opacity-0 peer-checked:[&>svg]:opacity-100',
					colors[color],
					sizes[size],
					error && 'border-ui-red',
				)
			">
			<CheckIcon
				:class="
					twMerge('transition-opacity duration-100', size === 'sm' && 'w-2.5', size === 'md' && 'w-3.5')
				" />
		</button>
		<div :class="twMerge('[&>a]:underline', uppercase && 'uppercase', size === 'sm' && 'text-xs')">
			<slot name="label" />
		</div>
	</label>
</template>

<script lang="ts" setup>
import CheckIcon from '../Icons/CheckIcon.vue';
import { computed } from 'vue';
import { twMerge } from 'tailwind-merge';

const { checked } = withDefaults(
	defineProps<{
		checked?: boolean;
		value?: boolean;
		error?: boolean;
		uppercase?: boolean;
		color?: keyof typeof colors;
		size?: keyof typeof sizes;
	}>(),
	{
		checked: false,
		value: false,
		color: 'green',
		size: 'md',
	},
);

const colors = {
	green: 'border-ui-green text-white peer-checked:bg-ui-green',
	'light-green': 'border-ui-green-200 text-ui-green-950 peer-checked:bg-ui-green-200',
} as const;

const sizes = {
	sm: 'h-5 w-5',
	md: 'h-6 w-6',
} as const;

const emit = defineEmits(['update:checked']);

const proxyChecked = computed({
	get() {
		return checked;
	},

	set(value) {
		emit('update:checked', value);
	},
});
</script>
