<template>
	<article
		class="relative flex h-full flex-col overflow-hidden break-words rounded-10 border-3 bg-ui-green-400 transition-colors lg:flex-row lg:gap-x-4.5 lg:p-5 xl:gap-x-7 xl:p-8"
		:class="active ? 'border-ui-green' : 'border-transparent'">
		<img
		v-if="specialOffer.photo"
			:src="`/images/set-images/${specialOffer.photo}`"
			alt=""
			class="h-60 object-cover lg:h-auto lg:w-46 lg:rounded-4xl" />
		<div class="flex h-full flex-col p-6 lg:p-0">
			<div
				class="absolute right-6 top-6 mb-2.5 w-fit rounded-full bg-ui-green-950 px-6 py-1.5 text-lg font-medium text-white lg:relative lg:right-0 lg:top-0">
				-{{ specialOffer.value }}%
			</div>
			<h2 class="!my-0 block text-xl font-bold text-ui-black md:text-2xl">
				{{ specialOffer.name }}
			</h2>
			<p class="my-3 text-ui-black/60 md:text-lg">{{ specialOffer.description }}</p>
			<WidgetButton
				class="!mt-auto !w-fit"
				:color="active ? 'green' : 'dark-green'"
				@click="() => onSelect?.(specialOffer)">
				{{ active ? $t('calendar.chosen') : $t('calendar.choose') }}
				<CheckIcon class="w-3.5" v-if="active" />
				<AddIcon class="w-3.5" v-else />
			</WidgetButton>
		</div>
	</article>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import CheckIcon from '@/Components/Widget/Icons/CheckIcon.vue';
import AddIcon from '@/Components/Widget-1/Icons/AddIcon.vue';
import { SpecialOffer } from '@/Types/models';

defineProps<{
	active?: boolean;
	onSelect?: (specialOffer: SpecialOffer) => void;
	specialOffer: SpecialOffer;
}>();
</script>
