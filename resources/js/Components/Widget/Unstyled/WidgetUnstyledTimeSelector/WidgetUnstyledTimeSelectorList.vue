<template>
	<ol :class="$props.class">
		<li v-for="block in blocks" :class="itemClass">
			<slot
				name="item"
				:block="block"
				:time="dayjs(block).format('HH:mm')"
				:active="widgetStore.form.start_at === block"
				:discount="
					(widgetStore.specialOffer &&
						widgetStore.isStartAtInSpecialOfferTimeRanges(
							widgetStore.specialOffer,
							block,
							true,
							widgetStore.form.duration,
						)) ||
					((widgetStore.specialOffer === null || widgetStore.specialOffer.active_by_default === true) &&
						widgetStore.getActiveByDefaultSpecialOfferForGivenTimeBlock(block) !== null)
				"
				:on-click="() => widgetStore.selectDatetime(block)" />
		</li>
	</ol>
</template>

<script lang="ts" setup>
import dayjs from 'dayjs';
import { useWidgetStore } from '@/Stores/widget';

defineProps<{
	class?: string;
	itemClass?: string;
	blocks: string[];
}>();

const widgetStore = useWidgetStore();
</script>
