<script lang="ts" setup>
const props = withDefaults(
	defineProps<{
		value?: string;
		required?: boolean;
	}>(),
	{
		required: false,
	},
);
</script>
<template>
	<label class="block flex text-sm font-normal text-gray-7">
		<span v-if="value">{{ value }}</span>
		<span v-else><slot /></span>

		<span v-if="required" class="ml-0.25 font-semibold text-danger-base">*</span>
	</label>
</template>
