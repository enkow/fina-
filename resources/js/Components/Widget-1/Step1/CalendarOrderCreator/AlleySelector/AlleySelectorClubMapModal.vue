<template>
	<WidgetModal :is-open="isOpen" @close="onClose" size="lg">
		<a :href="mapUrl" target="_blank" aria-label="Maximize club map" class="mx-auto mb-3 block w-fit">
			<ZoomIcon />
		</a>
		<WidgetModalTitle>
			{{ $t('calendar.club-map') }}
		</WidgetModalTitle>
		<img :src="mapUrl" alt="Club map" />
	</WidgetModal>
</template>

<script lang="ts" setup>
import WidgetModal from '@/Components/Widget/Ui/WidgetModal/WidgetModal.vue';
import WidgetModalTitle from '@/Components/Widget/Ui/WidgetModal/WidgetModalTitle.vue';
import ZoomIcon from '@/Components/Widget-1/Icons/ZoomIcon.vue';

defineProps<{
	mapUrl: string;
	isOpen: boolean;
	onClose: () => void;
}>();
</script>
