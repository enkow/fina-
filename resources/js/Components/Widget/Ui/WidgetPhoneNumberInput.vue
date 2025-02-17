<template>
	<div
		class="flex h-13 items-center rounded-2xl bg-white pr-5 focus-within:ring-2 focus-within:ring-ui-green">
		<div class="flex h-full">
			<WidgetLocaleDropdown
				:countries="countries"
				:active-locale="activeCountry.locale"
				:on-country-click="(country) => (activeCountry = country)">
				<template #button>
					<MenuButton
						class="flex h-full w-25 flex-1 items-center gap-x-2.5 rounded-l-2xl border-r-1.5 border-black/20 px-5 focus:outline-none">
						<WidgetLocaleImage :locale="activeCountry.code.toLowerCase()" />
						{{ activeCountry.code }}
					</MenuButton>
				</template>
			</WidgetLocaleDropdown>
		</div>
		<p :class="twMerge('ml-3 mr-1.5 w-9', activeCountry.dialing_code?.length === 3 && 'w-11')">
			+{{ activeCountry.dialing_code }}
		</p>
		<input
			type="text"
			class="w-full flex-1 border-0 p-0 text-lg text-ui-black ring-0 placeholder:text-ui-black/60 focus:ring-0"
			placeholder="000 000 000"
			:value="modelValue"
			@input="isInputElement($event.target) && $emit('update:modelValue', $event.target.value)" />
	</div>
</template>

<script lang="ts" setup>
import WidgetLocaleDropdown from '@/Components/Widget/Ui/WidgetLocaleDropdown.vue';
import WidgetLocaleImage from '@/Components/Widget/Ui/WidgetLocaleImage.vue';
import { MenuButton } from '@headlessui/vue';
import { ref } from 'vue';
import { isInputElement } from '@/Utils';
import { Country } from '@/Types/models';
import { twMerge } from 'tailwind-merge';

const { countries } = defineProps<{
	modelValue?: string;
	color?: 'green' | 'white';
	countries: Country[];
}>();

const activeCountry = ref(countries[0]);
</script>
