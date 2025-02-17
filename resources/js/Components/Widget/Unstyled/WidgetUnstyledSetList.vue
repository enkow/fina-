<template>
	<ul :class="$props.class">
		<li v-for="set in widgetStore.availableSets" :class="itemClass">
			<slot
				name="item"
				:set="set"
				:price="formatPrice(set.price)"
				:currency="widgetStore.getCurrencySymbol()"
				:min="0"
				:max="set.available || 0" />
		</li>
	</ul>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { formatPrice } from '@/Utils';

const widgetStore = useWidgetStore();

defineProps<{
	class?: string;
	itemClass?: string;
}>();

widgetStore.reloadSets();
</script>
