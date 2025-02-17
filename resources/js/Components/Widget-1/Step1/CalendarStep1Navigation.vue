<template>
	<div class="!mt-8 lg:grid lg:grid-cols-11">
		<TippyWrapper
			v-bind="{ ...(!routingStore.nextStepAvailable && { content: $t('calendar.fill-all-fields') }) }"
			class="ml-auto w-fit lg:col-span-3 lg:col-start-9 lg:w-full">
			<WidgetButton
				:disabled="!routingStore.nextStepAvailable"
				@click="routingStore.nextStep"
				:wrap="false"
				fill>
				{{ $t('calendar.next') }}
			</WidgetButton>
		</TippyWrapper>
	</div>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import TippyWrapper from '@/Components/TippyWrapper.vue';
import { useRoutingStore } from '@/Stores/routing';

const routingStore = useRoutingStore();
</script>
