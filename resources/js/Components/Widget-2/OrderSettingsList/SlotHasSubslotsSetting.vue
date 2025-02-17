<template>
	<OrderSettingsListItem v-if="active">
		<WidgetSelect :value="value" :items="items" @change="onChange" class="col-span-3" compact />
	</OrderSettingsListItem>
</template>

<script lang="ts" setup>
import WidgetSelect from '@/Components/Widget/Ui/WidgetSelect.vue';
import OrderSettingsListItem from './OrderSettingsListItem.vue';
import { useWidgetSlotHasSubslotsSetting } from '@/Composables/useWidgetSlotHasSubslotsSetting';

const { active, items, value, onChange } = useWidgetSlotHasSubslotsSetting();
</script>
