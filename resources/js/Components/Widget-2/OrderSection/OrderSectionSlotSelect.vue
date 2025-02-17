<template>
	<WidgetUnstyledSlotsFilter v-slot="{ items, value, toggleSlotId }">
		<Widget2Select :items="items" :placeholder="value" :value="value" @change="toggleSlotId" fill />
	</WidgetUnstyledSlotsFilter>
</template>

<script lang="ts" setup>
import WidgetUnstyledSlotsFilter from '@/Components/Widget/Unstyled/WidgetUnstyledSlotsFilter.vue';
import Widget2Select from '../Widget2Select.vue';
</script>
