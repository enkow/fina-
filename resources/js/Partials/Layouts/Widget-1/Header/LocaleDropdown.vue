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
			<MenuButton class="flex w-11 md:w-13 items-center justify-between text-ui-black md:w-18 hover:text-ui-green transition-colors">
				<PlanetIcon class="text-ui-green" />
				{{ capitalize(widgetStore.getCountryLocaleCode(widgetStore.currentCountry)) }}
				<DropdownIcon
					class="hidden transition-transform duration-200 md:block"
					:class="{ 'rotate-180': open }" />
			</MenuButton>
		</template>
	</WidgetLocaleDropdown>
</template>

<script lang="ts" setup>
import WidgetLocaleDropdown from '@/Components/Widget/Ui/WidgetLocaleDropdown.vue';
import { MenuButton } from '@headlessui/vue';
import { capitalize } from 'vue';
import PlanetIcon from '@/Components/Widget-1/Icons/PlanetIcon.vue';
import DropdownIcon from '@/Components/Widget/Icons/DropdownIcon.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
