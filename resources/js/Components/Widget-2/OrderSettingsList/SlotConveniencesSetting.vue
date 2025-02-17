<template>
	<template v-if="active">
		<li v-for="{ model, label } in data" class="ml-auto">
			<WidgetCheckbox v-model="model.status" color="light-green" size="sm" :checked="model.status" uppercase>
				<template #label>{{ label }}</template>
			</WidgetCheckbox>
		</li>
	</template>
</template>

<script lang="ts" setup>
import WidgetCheckbox from '@/Components/Widget/Ui/WidgetCheckbox.vue';
import { useWidgetSlotConveniencesSetting } from '@/Composables/widget/useWidgetSlotConveniencesSetting';

const { active, data } = useWidgetSlotConveniencesSetting();
</script>
