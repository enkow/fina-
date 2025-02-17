<template>
	<WidgetUnstyledNavigation
		v-slot="{ nextButtonDisabled }"
		class="flex justify-end gap-x-2 md:w-full md:justify-start md:gap-x-5">
		<div class="hidden md:block">
			<WidgetUnstyledNavigationFirstStepButton
				:as="WidgetIconButton"
				radius="xs"
				variant="outline"
				:label="$t('widget-2.start-over')"
				:content="$t('widget-2.start-over')"
				v-tippy>
				<StartOverIcon :class="ICON_STYLES" />
			</WidgetUnstyledNavigationFirstStepButton>
		</div>
		<WidgetUnstyledNavigationPreviousStepButton
			:as="WidgetIconButton"
			radius="xs"
			variant="outline"
			:label="$t('widget-2.previous-step')"
			:content="$t('widget-2.previous-step')"
			v-tippy>
			<ArrowRightIcon :class="twJoin(ICON_STYLES, 'rotate-180')" />
		</WidgetUnstyledNavigationPreviousStepButton>
		<TippyWrapper
			:content="nextButtonDisabled ? $t('widget-2.choose-all-required-data') : $t('widget-2.next-step')"
			class="md:ml-auto">
			<WidgetUnstyledNavigationNextStepButton
				v-if="routingStore.stepsNumber - routingStore.stepIndex > 2"
				radius="xs"
				:as="WidgetIconButton"
				:label="$t('widget-2.next-step')"
				:disabled="nextButtonDisabled">
				<ArrowRightIcon :class="ICON_STYLES" />
			</WidgetUnstyledNavigationNextStepButton>
		</TippyWrapper>
	</WidgetUnstyledNavigation>
</template>

<script lang="ts" setup>
import WidgetUnstyledNavigation from '@/Components/Widget/Unstyled/WidgetUnstyledNavigation/WidgetUnstyledNavigation.vue';
import WidgetUnstyledNavigationFirstStepButton from '@/Components/Widget/Unstyled/WidgetUnstyledNavigation/WidgetUnstyledNavigationFirstStepButton.vue';
import WidgetUnstyledNavigationPreviousStepButton from '@/Components/Widget/Unstyled/WidgetUnstyledNavigation/WidgetUnstyledNavigationPreviousStepButton.vue';
import WidgetUnstyledNavigationNextStepButton from '@/Components/Widget/Unstyled/WidgetUnstyledNavigation/WidgetUnstyledNavigationNextStepButton.vue';
import TippyWrapper from '@/Components/TippyWrapper.vue';
import StartOverIcon from '@/Components/Widget-2/Icons/StartOverIcon.vue';
import ArrowRightIcon from '@/Components/Widget-2/Icons/ArrowRightIcon.vue';
import WidgetIconButton from '@/Components/Widget/Ui/WidgetIconButton.vue';
import { twJoin } from 'tailwind-merge';
import { useRoutingStore } from '@/Stores/routing';

const ICON_STYLES = 'w-4 md:w-full';

const routingStore = useRoutingStore();
</script>

<style>
.tippy-box {
	@apply rounded-lg bg-[#e3ebdf] p-3 text-xs text-ui-black/60;
}

.tippy-arrow::before {
	@apply text-[#e3ebdf];
}
</style>
