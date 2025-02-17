<script lang="ts" setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = withDefaults(
	defineProps<{
		checked?: boolean;
		value?: boolean;
		disabled?: boolean;
		readonly?: boolean;
	}>(),
	{
		checked: false,
		value: false,
		disabled: false,
		readonly: false,
	},
);

const proxyChecked = computed({
	get() {
		return props.checked;
	},

	set(val) {
		emit('update:checked', val);
	},
});
</script>

<style scoped>
input {
	&.disabled-readable {
		@apply bg-gray-2 text-gray-3;
	}

	&.disabled:not(.disabled-readable) {
		@apply bg-gray-2 text-transparent;

		&::placeholder {
			@apply text-transparent;
		}
	}
}
</style>

<template>
	<input
		v-model="proxyChecked"
		:disabled="disabled || readonly"
		:value="value"
		:class="{ 'cursor-pointer': !disabled && !readonly, disabled: disabled, 'disabled-readable': readonly }"
		class="h-5 w-5 rounded border-gray-300 text-brand-base shadow-sm focus:border-gray-300 focus:shadow-transparent focus:ring-transparent focus:ring-offset-0"
		type="checkbox" />
</template>
