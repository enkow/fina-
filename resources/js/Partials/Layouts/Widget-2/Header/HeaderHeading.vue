<template>
	<h1
		class="hidden h-full rounded-2.5xl bg-ui-green px-10 text-xl font-black text-white md:flex md:items-center md:justify-center">
		{{ game && routingStore.stepIndex > 0 ? game.name : $t('widget-2.book') }}
	</h1>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';
import { computed } from 'vue';

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();

const game = computed(() => widgetStore.club.games?.find(({ id }) => id === widgetStore.form.game_id));
</script>
