<template>
	<WidgetUnstyledGameList
		:list-class="
			({ games }) =>
				twMerge(
					'widget-2-scrollbar grid grid-cols-1 gap-y-3 overflow-auto md:mx-auto md:w-110 md:gap-3',
					games.length > 3 && 'md:w-full md:grid-cols-2 md:gap-6',
					games.length > 5 && 'md:grid-cols-3',
				)
		">
		<template #item="{ game, active, onClick }">
			<div
				class="h-15 rounded-2.5xl bg-cover md:h-30"
				:style="{ 'background-image': `url('/images/game-images/${game.photo}')` }">
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
	</WidgetUnstyledGameList>
</template>

<script lang="ts" setup>
import WidgetUnstyledGameList from '../Widget/Unstyled/WidgetUnstyledGameList.vue';
import { twMerge } from 'tailwind-merge';
</script>
