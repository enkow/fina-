<template>
	<transition
		name="custom-classes-transition"
		enter-active-class="transition ease-out duration-250"
		enter-from-class="transform opacity-0"
		enter-to-class="transform opacity-100"
		leave-active-class="transition ease-out duration-100"
		leave-from-class="transform opacity-100"
		leave-to-class="transform opacity-0">
		<div v-if="widgetStore.fullLoader" class="absolute bottom-15 left-0 right-0 top-0 z-10">
			<div class="z-10 flex h-full w-full items-center justify-center bg-white">
				<WidgetUnstyledLoader />
			</div>
		</div>
	</transition>
</template>

<script setup lang="ts">
import WidgetUnstyledLoader from '@/Components/Widget/Unstyled/WidgetUnstyledLoader.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>

<style scoped lang="scss"></style>
