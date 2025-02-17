<template>
	<div class="input-group flex flex-col text-center">
		<InputLabel v-if="label" :value="label" class="mx-auto mb-6" for="panel_enabled" />
		<div
			:class="{
				'cursor-not-allowed': disabled,
				'cursor-pointer': !disabled,
			}"
			@click="toggleValue">
			<SuccessSquareIcon v-if="value" class="mx-auto" />
			<DangerSquareIcon v-else class="mx-auto" />
		</div>
	</div>
</template>

<script lang="ts" setup>
import { computed } from 'vue';

import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import SuccessSquareIcon from '@/Components/Dashboard/Icons/SuccessSquareIcon.vue';
import DangerSquareIcon from '@/Components/Dashboard/Icons/DangerSquareIcon.vue';

const props = defineProps<{
	label?: string;
	modelValue: Boolean;
	disabled?: Boolean;
}>();

const emit = defineEmits(['update:modelValue', 'toggle']);

const value = computed({
	get() {
		return props.modelValue;
	},
	set(value) {
		emit('update:modelValue', value);
	},
});

const toggleValue = () => {
	if (props.disabled) return;

	emit('toggle', !value.value);

	emit('update:modelValue', !value.value);
};
</script>
