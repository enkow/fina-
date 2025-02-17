<template>
	<SimpleSelect
		v-model="game"
		:options="userGameOptions"
		:placeholder="capitalize($t('main.game'))"
		class="game-select margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate dropdown-info-base open-indicator-info-dark open-indicator-hover-white min-w-[140px]" />
</template>

<script lang="ts" setup>
import { Game, SelectOption } from '@/Types/models';
import { router, usePage } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { onMounted, Ref, ref, watch } from 'vue';
import { useQueryString } from '@/Composables/useQueryString';

const { capitalize } = useString();
const { queryArray } = useQueryString();

const props = defineProps<{
	games?: Game[];
	tableName: string;
	customRoute?: string;
	customRouteAttributes?: Record<string, string>;
	game?: Game;
}>();

let longestGameNameLength: number = 0;
let userGameOptions: Array<SelectOption> = [];
const games: Game[] = props.games ?? usePage().props.user.club?.games ?? {};
for (const game of Object.values(games)) {
	longestGameNameLength = Math.max(usePage().props.gameNames[game.id].length, longestGameNameLength);
	userGameOptions.push({
		code: game.id.toString(),
		label: usePage().props.gameNames[game.id],
	});
}

const gameSelectMinWidth = ref<string>('100pxx');

onMounted(() => {
	gameSelectMinWidth.value = `${longestGameNameLength * 10 + 50}px`;
});

const { queryValue } = useQueryString();

const game: Ref<string> = ref(
	(!props.game ? null : String(props.game?.id)) ??
		queryValue(window.location.search, `filters[${props.tableName}][game]`) ??
		props.games?.[0].id,
);

watch(game, async (newValue) => {
	const queryParams = new URLSearchParams(window.location.search);
	let customRouteAttributes: Record<string, string> = props.customRouteAttributes ?? {};

	if (!props.game) {
		queryParams.set(`filters[${props.tableName}][game]`, String(newValue));
		queryParams.delete('page');
	} else {
		customRouteAttributes['game'] = String(newValue);
	}

	const url: string = props.game
		? route(props.customRoute, customRouteAttributes)
		: window.location.pathname + '?' + queryParams.toString();

	router.visit(url, { preserveScroll: true });
});
</script>
<style scoped>
.game-select {
	min-width: v-bind(gameSelectMinWidth);
}
</style>
