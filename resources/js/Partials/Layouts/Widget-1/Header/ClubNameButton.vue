<template>
	<button type="button" class="font-black text-ui-green" @click="widgetStore.fullFormReset">
		{{ widgetStore.club.name }}
	</button>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
