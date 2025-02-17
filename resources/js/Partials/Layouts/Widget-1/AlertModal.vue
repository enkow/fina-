<template>
	<WidgetModal :is-open="Boolean(widgetStore.alertContent)" @close="widgetStore.closeAlert()" close-button>
		<p class="flex flex-col text-center text-base text-ui-black/80">
			<span v-for="line in lines">
				{{ [line.trim(), ...(lines.length > 1 ? ['.'] : [])].join('') }}
			</span>
		</p>
	</WidgetModal>
</template>

<script lang="ts" setup>
import WidgetModal from '@/Components/Widget/Ui/WidgetModal/WidgetModal.vue';
import { useWidgetStore } from '@/Stores/widget';
import { ref, watch, computed } from 'vue';

const widgetStore = useWidgetStore();

const content = ref('');
const lines = computed(() => content.value.split('.').filter(Boolean));

watch(
	() => widgetStore.alertContent,
	(value) => {
		if (content.value.length === 0 || value.length > 0) {
			content.value = value;
		}
	},
	{ immediate: true },
);
</script>
