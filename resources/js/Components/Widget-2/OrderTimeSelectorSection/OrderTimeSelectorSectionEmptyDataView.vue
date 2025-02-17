<template>
	<div :class="twMerge('space-y-3 md:space-y-4', selectedSets.length > 0 && 'mt-6')">
		<p class="font-bold text-ui-black md:text-lg">
			{{ $t('widget-2.empty-search-criteria') }}
		</p>
		<div class="md:w-fit">
			<WidgetButton size="compact-md" radius="sm" @click="onButtonClick" fill>
				<ArrowRightIcon class="rotate-180" />
				{{ $t('widget-2.back') }}
			</WidgetButton>
		</div>
	</div>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import ArrowRightIcon from '@/Components/Widget-2/Icons/ArrowRightIcon.vue';
import { twMerge } from 'tailwind-merge';
import { useWidgetSelectedSets } from '@/Composables/useWidgetSelectedSets';

defineProps<{
	onButtonClick?: () => void;
}>();

const { selectedSets } = useWidgetSelectedSets();
</script>
