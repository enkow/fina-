<template>
	<WidgetInputWrapper v-if="active">
		<template #label>
			<AdultIcon class="opacity-30" />
			{{ label }}
		</template>
		<template #default="{ id }">
			<WidgetNumberInput :id="id" :min="min" :max="max" v-model="model.person_count" />
		</template>
	</WidgetInputWrapper>
</template>

<script lang="ts" setup>
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import WidgetNumberInput from '@/Components/Widget/Ui/WidgetNumberInput.vue';
import AdultIcon from '@/Components/Widget-1/Icons/AdultIcon.vue';
import { useWidgetStore } from '@/Stores/widget';
import { watch } from 'vue';
import { useWidgetPricePerPersonSetting } from '@/Composables/widget/useWidgetPricePerPersonSetting';

const { active, label, min, max, model } = useWidgetPricePerPersonSetting();
const widgetStore = useWidgetStore();

watch(
	() => model.value?.person_count,
	() => {
		widgetStore.reloadPriceTriggerer++;
	},
);
</script>
