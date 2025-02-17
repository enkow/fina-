<template>
	<div class="h-15 rounded-2.5xl bg-cover md:h-30" :style="{ 'background-image': `url('${photo}')` }">
		<button
			type="button"
			:class="
				twMerge(
					'flex h-full w-full items-center justify-center rounded-2.5xl border-3 border-transparent bg-ui-black/60 transition-colors duration-200 hover:border-ui-green hover:bg-ui-black/70',
					active && 'border-ui-green bg-ui-black/80',
				)
			"
			@click="onClick">
			<p class="overflow-hidden text-ellipsis whitespace-nowrap px-2 font-bold text-white md:text-2xl">
				{{ game.name }}
			</p>
		</button>
	</div>
</template>

<script lang="ts" setup>
import { Game } from '@/Types/models';
import { twMerge } from 'tailwind-merge';
import { useWidgetGamePhoto } from '@/Composables/widget/useWidgetGamePhoto';

const props = defineProps<{
	active?: boolean;
	onClick?: () => void;
	game: Game;
}>();

const photo = useWidgetGamePhoto(props.game);
</script>
