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
		<template #item="{ active, game, onClick }">
			<GameListItem :active="active" :game="game" @click="onClick" />
		</template>
	</WidgetUnstyledGameList>
</template>

<script lang="ts" setup>
import WidgetUnstyledGameList from '@/Components/Widget/Unstyled/WidgetUnstyledGameList.vue';
import GameListItem from './GameListItem.vue';
import { twMerge } from 'tailwind-merge';
</script>
