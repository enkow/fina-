<template>
	<section v-if="widgetStore.specialOffers?.length">
		<SectionTitle>{{ $t('widget-2.choose-a-special-offer') }}</SectionTitle>
		<SpecialOffersSectionCarousel />
	</section>
</template>

<script lang="ts" setup>
import SectionTitle from '../SectionTitle.vue';
import SpecialOffersSectionCarousel from './SpecialOffersSectionCarousel/SpecialOffersSectionCarousel.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
