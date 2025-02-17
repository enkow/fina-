<template>
	<div class="video rounded-md bg-gray-3/50">
		<Link
			:href="
				route('club.help-sections.help-items.show', {
					help_section: helpSection,
					help_item: helpItem,
				})
			">
			<div
				:style="{
					background: 'url(\'/images/help-item-thumbnails/' + helpItem.thumbnail + '\')',
				}"
				class="relative flex h-40 w-full items-center justify-center rounded-md !bg-cover">
				<div class="video-inactive absolute h-40 w-full bg-brand-base/50 transition-all"></div>
				<PlayIcon v-if="helpItem.video_url" class="absolute" />
			</div>
			<div class="mx-4 mb-3 mt-1.5 leading-6">
				<h3 class="font-bold text-gray-7">{{ helpItem.title }}</h3>
				<p class="mt-2.5 text-xxs text-gray-7">
					{{ helpItem.description }}
				</p>
			</div>
		</Link>
	</div>
</template>

<style scoped>
.video:hover .video-inactive {
	@apply opacity-0;
}
</style>
<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import PlayIcon from '@/Components/Dashboard/Icons/PlayIcon.vue';
import { HelpItem, HelpSection } from '@/Types/models';

const props = defineProps<{
	helpSection: HelpSection;
	helpItem: HelpItem;
}>();
</script>
