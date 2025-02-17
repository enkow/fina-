<template>
	<WidgetColorPaletteProvider v-slot="{ colors }">
		<div :style="colors">
			<div class="flex h-screen w-full items-center justify-center bg-ui-green-300 p-3">
				<article
					class="flex h-full w-full flex-col rounded-2.5xl bg-white p-6.5 md:h-150 md:max-w-190 md:rounded-5xl md:px-7.5 md:pb-7.5 md:pt-0">
					<Header />
					<main
						:class="
							twMerge(
								'flex max-h-[calc(100vh-204px)] flex-1 flex-col gap-y-7 md:h-full md:max-h-[calc(100%-117px)] md:justify-center',
								routingStore.stepIndex === 0 && 'justify-center',
							)
						">
						<slot />
					</main>
					<Footer :navigation="withNavigation" />
				</article>
			</div>
		</div>
	</WidgetColorPaletteProvider>
</template>

<script lang="ts" setup>
import WidgetColorPaletteProvider from '@/Components/Widget/WidgetColorPaletteProvider.vue';
import Header from '@/Partials/Layouts/Widget-2/Header/Header.vue';
import Footer from '@/Partials/Layouts/Widget-2/Footer/Footer.vue';
import { useRoutingStore } from '@/Stores/routing';
import { twMerge } from 'tailwind-merge';

withDefaults(
	defineProps<{
		withNavigation?: boolean;
	}>(),
	{
		withNavigation: true,
	},
);

const routingStore = useRoutingStore();
</script>
