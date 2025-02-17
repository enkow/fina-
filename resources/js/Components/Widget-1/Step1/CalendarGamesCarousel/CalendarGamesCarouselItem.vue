<template>
	<div
		class="h-32 overflow-hidden rounded-3xl bg-cover md:h-52 md:rounded-10 lg:h-60"
		:class="disabled ? 'cursor-not-allowed grayscale' : 'cursor-pointer'"
		@click="() => !disabled && onClick?.(game)"
		:style="{
			backgroundImage: `url(${gamePhoto || defaultGamePhoto})`,
		}">
		<div
			:class="[
				'flex h-full flex-col justify-center space-y-3.5 rounded-3xl border-4 px-8 text-white transition-colors duration-200 md:rounded-10 text-center',
				active ? 'border-ui-green bg-ui-black/80' : 'border-transparent bg-ui-black/60',
			]">
			<h3 class="break-words text-xl font-bold md:text-2.75xl lg:text-3.5xl">
				{{ widgetStore.gameNames[game.id] }}
			</h3>
			<p v-if="description" class="md:text-lg">{{ description }}</p>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Game } from '@/Types/models';
import { useWidgetStore } from '@/Stores/widget';
import { useDynamicResource } from '@/Composables/useDynamicResource';
import { useWidgetGamePhoto } from '@/Composables/widget/useWidgetGamePhoto';

const { game } = defineProps<{
	active?: boolean;
	disabled?: boolean;
	onClick?: (game: Game) => void;
	game: Game;
}>();

const widgetStore = useWidgetStore();
const defaultGamePhoto = useDynamicResource(import('/resources/assets/images/widget-1/billiards.jpeg'));
const gamePhoto = useWidgetGamePhoto(game);

const { description } = (widgetStore.props.gamesTranslations as any)[widgetStore.currentLocale][game.id];
</script>
