<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.help-sections.show', { help_section: helpSection }),
				label: $t('help.title'),
			},
			{
				href: route('club.help-sections.show', { help_section: helpSection }),
				label: helpSection.title,
			},
			{
				href: route('club.help-sections.help-items.show', {
					help_section: helpSection,
					help_item: helpItem,
				}),
				label: helpItem.title,
			},
		]">
		<ContentContainer>
			<VideosHeader :active-help-section="helpSection" :help-sections="helpSections" />
			<div class="col-span-12 overflow-hidden bg-white text-gray-7 shadow sm:rounded-lg">
				<div class="px-9.5 py-7">
					<h2 class="mb-6 w-full text-xl font-bold leading-7">
						{{ helpItem.title }}
					</h2>
					<div v-if="helpItem.video_url !== null" class="videowrapper">
						<iframe
							:src="helpItem.video_url"
							allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
							allowfullscreen
							frameborder="0"
							height="315"
							title="YouTube video player"
							width="560"></iframe>
					</div>
					<div class="mt-12 text-sm" v-html="helpItem.content"></div>
				</div>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import VideosHeader from '@/Components/Dashboard/VideosHeader.vue';
import { HelpItem, HelpSection } from '@/Types/models';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';

const props = defineProps<{
	helpSections: HelpSection[];
	helpSection: HelpSection;
	helpItem: HelpItem;
}>();
</script>

<style scoped>
.videowrapper {
	@apply relative float-none clear-both h-0 w-full pb-[56.25%] pt-6;

	iframe {
		@apply absolute left-0 top-0 h-full w-full;
	}
}
</style>
