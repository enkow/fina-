<template>
	<WidgetLocaleDropdown
		:countries="widgetStore.club.widget_countries"
		:active-locale="widgetStore.currentLocale"
		:on-country-click="
			({ locale, code }) => {
				widgetStore.setLocale(locale);
				widgetStore.currentCountry = code;
			}
		">
		<template #button="{ open }">
			<MenuButton class="flex items-center gap-x-2 text-xs md:gap-x-0 md:text-base">
				<PlanetIcon class="text-ui-green" />
				<span class="md:w-9">
					{{ capitalize(widgetStore.currentCountry.toLowerCase()) }}
				</span>
				<DropdownIcon
					:class="twJoin('hidden transition-transform duration-200 md:block', open && 'rotate-180')" />
			</MenuButton>
		</template>
	</WidgetLocaleDropdown>
</template>

<script lang="ts" setup>
import WidgetLocaleDropdown from '@/Components/Widget/Ui/WidgetLocaleDropdown.vue';
import PlanetIcon from '@/Components/Widget-1/Icons/PlanetIcon.vue';
import DropdownIcon from '@/Components/Widget/Icons/DropdownIcon.vue';
import { MenuButton } from '@headlessui/vue';
import { twJoin } from 'tailwind-merge';
import { useWidgetStore } from '@/Stores/widget';
import { capitalize } from 'vue';

const widgetStore = useWidgetStore();
</script>
