<template>
	<WidgetUnstyledSelectedSetList
		:class="
			twMerge(
				'flex gap-x-4 text-xs font-bold text-ui-black md:py-2.5',
				border && 'border-t-2 border-white/20',
			)
		">
		<template #item="{ set: { name, selected } }">
			{{ selected }}
			<span class="font-normal text-ui-black/60">x</span>
			{{ name }}
		</template>
	</WidgetUnstyledSelectedSetList>
</template>

<script lang="ts" setup>
import WidgetUnstyledSelectedSetList from '@/Components/Widget/Unstyled/WidgetUnstyledSelectedSetList.vue';
import { twMerge } from 'tailwind-merge';

defineProps<{
	border?: boolean;
}>();
</script>
