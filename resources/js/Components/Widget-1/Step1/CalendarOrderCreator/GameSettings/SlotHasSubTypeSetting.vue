<template>
	<WidgetSelect v-if="active" :items="items" :value="value" @change="onChange" />
</template>

<script lang="ts" setup>
import WidgetSelect from '@/Components/Widget/Ui/WidgetSelect.vue';
import { useWidgetSlotHasSubTypeSetting } from '@/Composables/widget/useWidgetSlotHasSubTypeSetting';

const { active, items, value, onChange } = useWidgetSlotHasSubTypeSetting();
</script>
