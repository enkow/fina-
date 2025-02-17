<template>
	<template v-if="widgetStore.form.game_id && widgetStore.hasSetMap()">
		<WidgetButton
			class="mt-8 !w-fit lg:mx-auto"
			@click="
				() => {
					widgetStore.showMap();
					isOpen = true;
				}
			">
			{{ $t('calendar.look-at-the-club-map') }}
			<ArrowRightIcon />
		</WidgetButton>
		<AlleySelectorClubMapModal
			:map-url="widgetStore.displayedClubMapUrl"
			:is-open="isOpen"
			@close="isOpen = false" />
	</template>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import ArrowRightIcon from '@/Components/Widget-1/Icons/ArrowRightIcon.vue';
import AlleySelectorClubMapModal from './AlleySelectorClubMapModal.vue';
import { ref } from 'vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
const isOpen = ref(false);
</script>
