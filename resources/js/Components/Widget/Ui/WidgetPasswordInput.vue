<template>
	<WidgetInput
		:type="showPassword ? 'text' : 'password'"
		:color="color"
		:placeholder="placeholder"
		:model-value="modelValue"
		@update:modelValue="$emit('update:modelValue', $event)">
		<template #rightSection>
			<button
				type="button"
				:aria-label="`${showPassword ? 'Hide' : 'Show'} password`"
				@click="() => (showPassword = !showPassword)">
				<EyeIcon class="h-6 w-6" />
			</button>
		</template>
	</WidgetInput>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import WidgetInput from '@/Components/Widget/Ui/WidgetInput.vue';
import EyeIcon from '@/Components/Widget-3/Icons/EyeIcon.vue';

const showPassword = ref(false);

withDefaults(
	defineProps<{
		modelValue?: string;
		color?: 'green' | 'white';
		placeholder?: string;
	}>(),
	{
		color: 'green',
	},
);
</script>
