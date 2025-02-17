<template>
	<div class="overflow relative col-span-12 bg-white pb-3 shadow sm:rounded-lg md:h-69.5 md:pb-0">
		<div class="left-10 top-16.5 w-full p-5 md:absolute md:w-92 md:p-0">
			<h2 class="mb-5.5 text-xl font-bold">
				{{ $t('help.how-can-we-help-you') }}
			</h2>
			<Popper
				:show="searchResultsShowing"
				@click="searchInputClicked"
				@focusout="searchResultsShowing = false"
				class="!m-0 w-[calc(100%)] !border-0 md:w-92">
				<SearchInput v-model="search" class="w-full" />
				<template #content>
					<div
						class="-mt-1 w-[calc(100vw-56px)] select-none rounded border bg-gray-1 py-2 text-black shadow-md md:w-92">
						<div
							v-for="searchResult in searchResults"
							class="flex w-full cursor-pointer flex-wrap items-center justify-between px-2 py-1.5 text-xs hover:bg-brand-base hover:text-white"
							@click="goToHelpItem(searchResult)">
							<p class="block w-full font-medium">{{ searchResult.title }}</p>
							<p class="block w-full">
								{{ truncateString(searchResult.description, 100) }}
							</p>
						</div>
					</div>
				</template>
			</Popper>
		</div>
		<Mascot :type="16" class="absolute right-0 top-2 hidden h-46 w-54 md:block xl:right-2" />
		<ul class="bottom-0 left-10 mx-5 md:absolute md:m-0">
			<li v-for="helpSection in helpSections" :class="{ active: helpSection.id === activeHelpSection.id }">
				<Link :href="route('club.help-sections.show', { help_section: helpSection })" class="block w-full">
					{{ helpSection.title }}
				</Link>
			</li>
		</ul>
	</div>
</template>
<script lang="ts" setup>
import SearchInput from '@/Components/Dashboard/SearchInput.vue';
import { Link, router } from '@inertiajs/vue3';
import { HelpItem, HelpSection } from '@/Types/models';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { ref } from 'vue';
import Popper from 'vue3-popper';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';

const props = defineProps<{
	helpSections: HelpSection[];
	activeHelpSection: HelpSection;
}>();

const searchResultsShowing = ref<boolean>(false);
const searchResults = ref<HelpItem[]>([]);
const search = ref<string>('');

function truncateString(str, maxLength): string {
	if ((str?.length ?? 0) <= maxLength) {
		return str;
	}

	const trimmedStr = str.substr(0, maxLength);
	const lastSpaceIndex = trimmedStr.lastIndexOf(' ');

	if (lastSpaceIndex > -1) {
		return trimmedStr.substr(0, lastSpaceIndex) + '...';
	}

	return trimmedStr + '...';
}

function goToHelpItem(helpItem: HelpItem): void {
	searchResultsShowing.value;
	router.visit(
		route('club.help-sections.help-items.show', {
			help_section: helpItem.help_section_id,
			help_item: helpItem.id,
		}),
	);
}

function searchInputClicked(): void {
	if (search.value.length) {
		searchResultsShowing.value = true;
	}
}

watchDebounced(
	search,
	() => {
		axios
			.get(
				route('club.help-sections.help-items.search', {
					helpSection: props.activeHelpSection.id,
					search: search.value,
				}),
			)
			.then(function (response: any) {
				searchResults.value = response.data;
				if (search.value.length) {
					searchResultsShowing.value = true;
				} else {
					searchResultsShowing.value = false;
				}
			});
	},
	{ debounce: 500, maxWait: 1000 },
);
</script>
<style scoped>
ul {
	li {
		@apply block rounded-md px-3 py-2 text-gray-4 transition-all md:mr-14 md:inline-block md:rounded-b-none md:rounded-t-md md:p-0 md:pb-5;

		&.active {
			@apply border-brand-base bg-brand-base text-white md:bg-transparent md:text-brand-base;
		}
	}
}
</style>
