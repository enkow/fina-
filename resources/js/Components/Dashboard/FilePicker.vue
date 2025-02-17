<template>
	<div class="relative h-42 w-full rounded-md border border-dashed border-gray-3">
		<input
			id="file"
			:key="fileInputKey"
			class="absolute left-0 top-0 z-50 h-full w-full text-[100px] opacity-0"
			title=" "
			type="file"
			@input="emit('update', $event.target.files[0])"
			v-on:change="handleFileUpload()"
			v-if="!disabled" />
		<label
			class="absolute left-0 top-1/2 block w-full -translate-y-1/2 text-center text-sm text-gray-3/70"
			for="file">
			<DocumentIcon class="h-14 w-10 text-gray-3" />
			<slot />
		</label>
	</div>
</template>

<style scoped>
input[type='file']::-webkit-file-upload-button {
	cursor: pointer;
}

input[type='file']::file-selector-button {
	@apply !h-10;
}
</style>

<script lang="ts" setup>
import { Ref, ref } from 'vue';
import DocumentIcon from '@/Components/Dashboard/Icons/DocumentIcon.vue';

const props = defineProps<{
	disabled?: boolean;
}>();
const emit = defineEmits(['update']);

const fileInputKey: Ref<number> = ref(0);

function handleFileUpload() {
	fileInputKey.value++;
}
</script>
