<template>
	<div>
		<Popper>
			<div
				:class="[{ '!cursor-default': disabled }, previewClasses ?? {}]"
				:style="{
					backgroundColor: colorRef,
				}"
				class="h-9 w-9 cursor-pointer rounded-md" />
			<template #content>
				<ColorPicker
					:color="color"
					:colors-default="[]"
					:sucker-area="[]"
					:sucker-canvas="null"
					:sucker-hide="true"
					class="simple-colorpicker"
					theme="light"
					@changeColor="changeColor"
					v-if="!disabled" />
			</template>
		</Popper>
	</div>
</template>

<script lang="ts" setup>
import { ColorPicker } from 'vue-color-kit';
import 'vue-color-kit/dist/vue-color-kit.css';
import { ref, watch } from 'vue';
import Popper from 'vue3-popper';
import { usePage } from '@inertiajs/vue3';

const props = withDefaults(
	defineProps<{
		color: string;
		previewClasses?: Record<string, string>;
		disabled?: boolean;
	}>(),
	{
		disabled: false,
	},
);

const emit = defineEmits<{
	(e: 'updated', color: string): void;
}>();

let colorRef = ref<string>(props.color);

watch(
	() => props.color,
	(color) => {
		colorRef.value = color;
	},
);

function changeColor(c: { hex: string }) {
	colorRef.value = c.hex;
	emit('updated', colorRef.value);
}
</script>
<style>
.simple-colorpicker.hu-color-picker {
	.color-type:nth-last-of-type(2) {
		@apply hidden;
	}

	.color-type .name {
		@apply hidden;
	}

	.color-type .value {
		@apply rounded border border-solid border-gray-3 bg-white text-center text-sm !outline-none !outline-0;
	}

	.color-alpha {
		@apply hidden;
	}

	.color-show {
		@apply hidden;
	}
}
</style>
