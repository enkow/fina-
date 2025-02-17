<template>
	<slot :colors="colors" />
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();

const colorPalette = widgetStore.props.calendarColors as WidgetColorPalette;

const colors = Object.fromEntries(
	Object.entries(colorPalette).map(([key, value]) => [`--ui-green-${key}`, value]),
);

type WidgetColorPalette = Record<50 | 75 | 100 | 200 | 300 | 350 | 400 | 500 | 600 | 700 | 800 | 900 | 950, string>;
</script>
