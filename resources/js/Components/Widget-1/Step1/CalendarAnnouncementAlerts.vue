<template>
	<WidgetAlert v-for="{ content_top, content_bottom } in gameAnnouncements" :color="color">
		<template #title>{{ $t('calendar.announcement') }}</template>
		<template #description>{{ source === 'top' ? content_top : content_bottom }}</template>
	</WidgetAlert>
</template>

<script lang="ts" setup>
import WidgetAlert from '@/Components/Widget/Ui/WidgetAlert.vue';
import { useWidgetStore } from '@/Stores/widget';
import { usePage } from '@inertiajs/vue3';
import { Announcement } from '@/Types/models';
import { computed } from 'vue';

withDefaults(
	defineProps<{
		color?: 'primary' | 'white';
		source: 'top' | 'bottom';
	}>(),
	{ color: 'primary' },
);

const widgetStore = useWidgetStore();
const page = usePage<{ announcements: Announcement[] }>();

const announcements = page.props.announcements.filter(({ type }) => type === 2);

const gameAnnouncements = computed(() =>
	announcements.filter(({ game_id }) => widgetStore.form.game_id === game_id),
);
</script>
