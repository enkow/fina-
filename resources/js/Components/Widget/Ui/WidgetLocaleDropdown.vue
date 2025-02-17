<template>
	<WidgetDropdown>
		<template #button="{ open }">
			<slot name="button" :open="open" />
		</template>
		<template #items>
			<MenuItem v-for="country in countries">
				<button
					@click="() => props.onCountryClick?.(country)"
					:class="
						twMerge(
							'flex w-max items-center gap-x-2 text-ui-text transition-colors hover:text-ui-green/70',
							country.locale === activeLocale && 'text-ui-green',
						)
					">
					<WidgetLocaleImage :locale="country.code.toLowerCase()" />
					{{ capitalize(widgetStore.getCountryLocaleCode(country)) }}
				</button>
			</MenuItem>
		</template>
	</WidgetDropdown>
</template>

<script lang="ts" setup>
import WidgetDropdown from '@/Components/Widget/Ui/WidgetDropdown.vue';
import WidgetLocaleImage from './WidgetLocaleImage.vue';
import { capitalize } from 'vue';
import { MenuItem } from '@headlessui/vue';
import { Country } from '@/Types/models';
import { twMerge } from 'tailwind-merge';
import { useWidgetStore } from '@/Stores/widget';

const props = defineProps<{
	countries: Country[];
	activeLocale: string;
	onCountryClick?: (country: Country) => void;
}>();

const widgetStore = useWidgetStore();
</script>
