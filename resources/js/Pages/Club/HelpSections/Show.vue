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
		]">
		<ContentContainer>
			<VideosHeader :active-help-section="helpSection" :help-sections="helpSections" />
			<div class="col-span-12">
				<Card>
					<h2 class="mb-3 px-1 text-xl font-bold leading-7 text-gray-7">
						{{ helpSection.title }}
					</h2>
					<div class="w-full px-1">
						<p class="mb-10 w-3/4 text-sm leading-6 text-gray-7/50">
							{{ helpSection.description }}
						</p>
					</div>
					<div class="grid grid-cols-4 gap-x-3 gap-y-4">
						<HelpVideo
							v-for="helpItem in helpSection.helpItems"
							:help-item="helpItem"
							:help-section="helpSection"
							class="col-span-4 sm:col-span-2 xl:col-span-1" />
					</div>
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import HelpVideo from '@/Components/Dashboard/Help/HelpVideo.vue';
import VideosHeader from '@/Components/Dashboard/VideosHeader.vue';
import { HelpSection } from '@/Types/models';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';

const props = defineProps<{
	helpSections: HelpSection[];
	helpSection: HelpSection;
}>();
</script>

<style scoped>
.video:hover .video-inactive {
	@apply opacity-0;
}
</style>
