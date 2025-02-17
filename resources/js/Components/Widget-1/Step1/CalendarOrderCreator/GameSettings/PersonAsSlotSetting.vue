<template>
	<WidgetInputWrapper v-if="active">
		<template #label>
			<AdultIcon class="opacity-30" />
			{{ label }}
		</template>
		<template #default="{ id }">
			<WidgetNumberInput :id="id" :min="min" :max="max" v-model="model.slots_count" />
		</template>
	</WidgetInputWrapper>
</template>

<script lang="ts" setup>
import WidgetNumberInput from '@/Components/Widget/Ui/WidgetNumberInput.vue';
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import AdultIcon from '@/Components/Widget-1/Icons/AdultIcon.vue';
import { useWidgetPersonAsSlotSetting } from '@/Composables/widget/useWidgetPersonAsSlotSetting';

const { active, label, min, max, model } = useWidgetPersonAsSlotSetting();
</script>
