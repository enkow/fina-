<template>
	<ul v-if="selectedSets.length > 0" :class="$props.class">
		<li v-for="set in selectedSets" :class="listClass">
			<slot name="item" :set="set" />
		</li>
	</ul>
</template>

<script lang="ts" setup>
import { useWidgetSelectedSets } from '@/Composables/useWidgetSelectedSets';

defineProps<{
	class?: string;
	listClass?: string;
}>();

const { selectedSets } = useWidgetSelectedSets();
</script>
