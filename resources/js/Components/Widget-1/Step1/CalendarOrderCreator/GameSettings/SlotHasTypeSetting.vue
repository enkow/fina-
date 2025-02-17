<template>
	<WidgetSelect v-if="active" :items="items" :value="value" @change="onChange" />
</template>

<script lang="ts" setup>
import WidgetSelect from '@/Components/Widget/Ui/WidgetSelect.vue';
import { useWidgetSlotHasTypeSetting } from '@/Composables/widget/useWidgetSlotHasTypeSetting';

const { active, items, value, onChange } = useWidgetSlotHasTypeSetting();
</script>
