<template>
	<WidgetInputWrapper>
		<template #label>
			<img class="w-6 h-6 opacity-30 object-scale-down" src="@as/images/amount.png" alt="" />
			{{ widgetStore.gameTranslations['slots-quantity'] }}
		</template>
		<template #default="{ id }">
			<WidgetNumberInput
				v-model="widgetStore.form.slots_count"
				:id="id"
				:min="widgetStore.specialOfferSlotsCount ?? 1"
				:max="widgetStore.specialOfferSlotsCount ?? widgetStore.gameDateWidgetSlotsMaxLimit" />
		</template>
	</WidgetInputWrapper>
</template>

<script lang="ts" setup>
import WidgetNumberInput from '@/Components/Widget/Ui/WidgetNumberInput.vue';
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
