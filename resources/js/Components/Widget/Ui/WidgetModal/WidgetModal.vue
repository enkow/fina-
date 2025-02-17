<template>
	<WidgetColorPaletteProvider v-slot="{ colors }">
		<TransitionRoot :show="isOpen" as="template">
			<Dialog as="div" @close="onClose" class="absolute z-10" :style="colors">
				<TransitionChild
					as="template"
					enter="transiton-opacity duration-200"
					enter-from="opacity-0"
					enter-to="opacity-1"
					leave="transiton-opacity duration-200"
					leave-from="opacity-1"
					leave-to="opacity-0">
					<div class="fixed inset-0 bg-black/50" />
				</TransitionChild>
				<div class="fixed inset-0 overflow-y-auto">
					<div class="flex min-h-full items-center justify-center p-2">
						<TransitionChild
							as="template"
							enter="transiton-all duration-200"
							enter-from="opacity-0 -translate-y-7"
							enter-to="opacity-1 translate-y-0"
							leave="transiton-all duration-200"
							leave-from="opacity-1 translate-y-0"
							leave-to="opacity-0 translate-y-6">
							<DialogPanel
								class="relative w-full rounded-3xl bg-white text-lg shadow-lg"
								:class="sizes[size]">
								<button
									v-if="closeButton"
									type="button"
									class="absolute right-3 top-3 text-ui-black transition-colors hover:text-ui-green focus:outline-none"
									@click="onClose">
									<CloseIcon />
								</button>
								<slot />
							</DialogPanel>
						</TransitionChild>
					</div>
				</div>
			</Dialog>
		</TransitionRoot>
	</WidgetColorPaletteProvider>
</template>

<script setup lang="ts">
import WidgetColorPaletteProvider from '@/Components/Widget/WidgetColorPaletteProvider.vue';
import CloseIcon from '@/Components/Widget-2/Icons/CloseIcon.vue';
import { TransitionRoot, Dialog, DialogPanel, TransitionChild } from '@headlessui/vue';

const sizes = {
	md: 'max-w-md p-6 md:p-10',
	lg: 'max-w-3xl p-3 md:p-10',
} as const;

withDefaults(
	defineProps<{
		size?: keyof typeof sizes;
		closeButton?: boolean;
		isOpen: boolean;
		onClose: () => void;
	}>(),
	{ size: 'md' },
);
</script>
