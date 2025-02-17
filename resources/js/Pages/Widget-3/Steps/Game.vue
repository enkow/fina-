<template>
	<Widget3Layout>
		<div class="flex h-100 w-full flex-col items-center justify-center space-y-8 sm:space-y-10">
			<p
				v-for="game in widgetStore.club['games'].filter((gameModel) => gameModel.pivot.enabled_on_widget)"
				:class="{ active: widgetStore.form.game_id === game.id }"
				class="game"
				@click="
					() => {
						widgetStore.selectGame(game);
						routingStore.nextStep();
					}
				">
				{{ widgetStore.gameNames[game.id] }}
			</p>
		</div>
		<p class="text-widget pt-5 text-center text-sm font-semibold">
			{{ widgetStore.settings['widget_message'].value }}
		</p>
	</Widget3Layout>
</template>

<script lang="ts" setup>
import Widget3Layout from '@/Layouts/Widget3Layout.vue';
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();

const widgetColor: string = widgetStore.widgetColor;
</script>

<style scoped>
.game {
	@apply flex h-15 w-full max-w-[220px] cursor-pointer items-center justify-center rounded-md border-[3px] text-center text-xl font-semibold uppercase transition-all xs:w-60 xs:max-w-none sm:w-80 sm:text-2xl;
	border-color: v-bind(widgetColor);
	color: v-bind(widgetColor);

	&:hover,
	&.active {
		@apply text-white;
		background-color: v-bind(widgetColor);
	}

	&:active {
		@apply opacity-75;
	}
}
</style>
