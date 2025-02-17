<template>
	<div
		:class="
			twMerge('flex items-center border-b border-ui-green-500 py-2 text-xs', error && 'border-widget-2-red')
		">
		<WidgetLocaleDropdown
			:countries="countries"
			:active-locale="activeCountry.locale"
			@country-click="(country) => (activeCountry = country)">
			<template #button="{ open }">
				<MenuButton class="flex w-18 items-center justify-between focus:outline-none">
					<WidgetLocaleImage :locale="activeCountry.code.toLowerCase()" />
					{{ activeCountry.code }}
					<DropdownIcon class="transition-transform duration-200" :class="{ 'rotate-180': open }" />
				</MenuButton>
			</template>
		</WidgetLocaleDropdown>
		<div class="ml-4 flex w-full items-center gap-x-1 border-l-2 border-black/20 pl-4">
			<p>+{{ activeCountry.dialing_code }}</p>
			<input
				type="text"
				placeholder="000 000 000"
				class="w-full border-0 bg-transparent p-0 text-xs placeholder:text-ui-black/60 focus:ring-0"
				:value="modelValue"
				@input="isInputElement($event.target) && $emit('update:modelValue', $event.target.value)" />
		</div>
	</div>
</template>

<script lang="ts" setup>
import WidgetLocaleDropdown from '../Widget/Ui/WidgetLocaleDropdown.vue';
import WidgetLocaleImage from '../Widget/Ui/WidgetLocaleImage.vue';
import DropdownIcon from '../Widget/Icons/DropdownIcon.vue';
import { MenuButton } from '@headlessui/vue';
import { ref } from 'vue';
import { isInputElement } from '@/Utils';
import { twMerge } from 'tailwind-merge';
import { Country } from '@/Types/models';

const { countries } = defineProps<{
	error?: boolean;
	modelValue: string;
	countries: Country[];
}>();

const activeCountry = ref(countries[0]);
</script>
