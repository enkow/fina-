<template>
	<ul :class="typeof listClass === 'string' ? listClass : listClass?.({ games })">
		<li v-for="game in games" :class="itemClass">
			<slot
				name="item"
				:game="game"
				:active="widgetStore.form.game_id === game.id"
				:on-click="
					() => {
						widgetStore.selectGame(game);
						routingStore.nextStep();
					}
				" />
		</li>
	</ul>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';
import { Game } from '@/Types/models';
import { useWidgetEnabledGames } from '@/Composables/widget/useWidgetEnabledGames';

defineProps<{
	listClass?: ((data: { games: Game[] }) => string) | string;
	itemClass?: string;
}>();

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();
const games = useWidgetEnabledGames();
</script>
