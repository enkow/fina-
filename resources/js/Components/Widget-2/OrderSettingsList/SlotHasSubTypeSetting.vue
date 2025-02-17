<template>
	<OrderSettingsListItem v-if="active">
		<WidgetSelect :items="items" :value="value" :on-change="onChange" class="col-span-3" compact />
	</OrderSettingsListItem>
</template>

<script lang="ts" setup>
import OrderSettingsListItem from './OrderSettingsListItem.vue';
import WidgetSelect from '@/Components/Widget/Ui/WidgetSelect.vue';
import { useWidgetSlotHasSubTypeSetting } from '@/Composables/widget/useWidgetSlotHasSubTypeSetting';

const { active, items, value, onChange } = useWidgetSlotHasSubTypeSetting();
</script>
