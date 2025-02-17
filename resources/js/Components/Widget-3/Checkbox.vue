<script lang="ts" setup>
import { computed } from 'vue';
import { useWidgetStore } from '@/Stores/widget';

const emit = defineEmits(['update:checked']);

const props = withDefaults(
	defineProps<{
		checked?: boolean;
		value?: boolean;
	}>(),
	{
		checked: false,
		value: false,
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

const widgetStore = useWidgetStore();
const widgetColor = widgetStore.widgetColor;
</script>

<template>
	<input
		v-model="proxyChecked"
		:style="{ color: widgetColor, borderColor: widgetColor }"
		:value="value"
		class="h-5 w-5 cursor-pointer rounded border-gray-400 shadow-sm focus:shadow-transparent focus:ring-transparent focus:ring-offset-0"
		type="checkbox" />
</template>
