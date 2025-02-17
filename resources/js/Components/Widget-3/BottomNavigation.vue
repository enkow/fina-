<template>
	<WidgetUnstyledNavigation>
		<div class="hidden md:block">
			<div class="-mb-5 flex w-full justify-between px-8 pr-10">
				<div>
          <template v-if="firstStepButtonVisible">
            <StepButton
                v-if="widgetStore.fullLoader"
                class="!cursor-not-allowed !opacity-20"
                v-tippy>
              <DoubleChevronLeftIcon />
            </StepButton>
            <WidgetUnstyledNavigationFirstStepButton v-else :as="StepButton">
              <DoubleChevronLeftIcon />
            </WidgetUnstyledNavigationFirstStepButton>
          </template>
				</div>
				<div class="flex space-x-2">
          <template v-if="previousStepButtonVisible">
            <StepButton
                v-if="widgetStore.fullLoader"
                class="!cursor-not-allowed !opacity-20"
                v-tippy>
              <ChevronLeftIcon />
            </StepButton>
            <WidgetUnstyledNavigationPreviousStepButton v-else :as="StepButton">
              <ChevronLeftIcon />
            </WidgetUnstyledNavigationPreviousStepButton>
          </template>
          <template v-if="nextStepButtonVisible">
            <StepButton
                v-if="widgetStore.fullLoader || routingStore.nextStepAvailable === false"
                :content="$t('widget.fill-all-fields')"
                class="!cursor-not-allowed !opacity-20"
                v-tippy>
              <ChevronRightIcon />
            </StepButton>
            <WidgetUnstyledNavigationNextStepButton v-else :as="StepButton">
              <ChevronRightIcon />
            </WidgetUnstyledNavigationNextStepButton>
          </template>
				</div>
			</div>
			<div class="text-widget mx-auto block select-none pb-5 text-center text-xxs font-bold uppercase">
				<a
					target="_blank"
					:href="homepageUrl"
					v-html="$t('widget.powered-by-value', { url: homepageUrl, value: appName })"></a>
			</div>
		</div>
		<div class="block md:hidden">
			<div
				v-if="
					routingStore.stepIndex !== 0 &&
					!(routingStore.stepIndex === 5 && Boolean(widgetStore.reservationData))
				"
				class="mb-10 flex items-center justify-between px-3 xxs:px-5.25 md:px-8">
				<div>
					<a
						class="text-widget block select-none text-left text-xxs font-bold uppercase"
						target="_blank"
						:href="homepageUrl"
						v-html="$t('widget.powered-by-value', { value: appName })"></a>
				</div>
				<div class="flex space-x-2 pr-1.5">
          <template v-if="previousStepButtonVisible">
            <StepButton
                v-if="widgetStore.fullLoader"
                class="!cursor-not-allowed !opacity-20"
                v-tippy>
              <ChevronRightIcon />
            </StepButton>
            <WidgetUnstyledNavigationPreviousStepButton v-else :as="StepButton">
              <ChevronLeftIcon />
            </WidgetUnstyledNavigationPreviousStepButton>
          </template>
          <template v-if="nextStepButtonVisible">
            <StepButton
                v-if="!routingStore.nextStepAvailable || widgetStore.fullLoader"
                :content="$t('widget.fill-all-fields')"
                class="!cursor-not-allowed !opacity-20"
                v-tippy>
              <ChevronRightIcon />
            </StepButton>
            <WidgetUnstyledNavigationNextStepButton v-else :as="StepButton">
              <ChevronRightIcon />
            </WidgetUnstyledNavigationNextStepButton>
          </template>
				</div>
			</div>
			<div
				v-else-if="routingStore.stepIndex === 5 && Boolean(widgetStore.reservationData)"
				class="mb-10 flex w-full items-center justify-between px-5.25 md:px-8">
        <template>
          <StepButton
              v-if="widgetStore.fullLoader"
              class="!cursor-not-allowed !opacity-20"
              v-tippy>
            <ChevronRightIcon />
          </StepButton>
          <WidgetUnstyledNavigationFirstStepButton v-else :as="StepButton">
            <DoubleChevronLeftIcon />
          </WidgetUnstyledNavigationFirstStepButton>
        </template>
				<div>
					<a
						class="text-widget block select-none text-center text-xxs font-bold uppercase"
						target="_blank"
						:href="homepageUrl"
						v-html="$t('widget.powered-by-value', { value: appName })"></a>
				</div>
			</div>
			<div
				v-else
				class="text-widget absolute bottom-14 block w-full select-none text-center text-xxs font-bold uppercase md:bottom-7">
				<a :href="homepageUrl" target="_blank" v-html="$t('widget.powered-by-value', { value: appName })"></a>
			</div>
		</div>
	</WidgetUnstyledNavigation>
</template>

<script lang="ts" setup>
import StepButton from '@/Components/Widget-3/StepButton.vue';
import ChevronLeftIcon from '@/Components/Widget-3/Icons/ChevronLeftIcon.vue';
import DoubleChevronLeftIcon from '@/Components/Widget-3/Icons/DoubleChevronLeftIcon.vue';
import ChevronRightIcon from '@/Components/Widget-3/Icons/ChevronRightIcon.vue';
import WidgetUnstyledNavigation from '@/Components/Widget/Unstyled/WidgetUnstyledNavigation/WidgetUnstyledNavigation.vue';
import WidgetUnstyledNavigationFirstStepButton from '@/Components/Widget/Unstyled/WidgetUnstyledNavigation/WidgetUnstyledNavigationFirstStepButton.vue';
import WidgetUnstyledNavigationPreviousStepButton from '@/Components/Widget/Unstyled/WidgetUnstyledNavigation/WidgetUnstyledNavigationPreviousStepButton.vue';
import WidgetUnstyledNavigationNextStepButton from '@/Components/Widget/Unstyled/WidgetUnstyledNavigation/WidgetUnstyledNavigationNextStepButton.vue';
import { useWidgetStore } from '@/Stores/widget';
import { computed, onMounted, ref } from 'vue';
import { useRoutingStore } from '@/Stores/routing';

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();

const appName = ref<string>('');
const homepageUrl = computed<string>(() => {
	return import.meta.env.VITE_HOMEPAGE_URL;
});
onMounted(() => {
	appName.value = import.meta.env.VITE_APP_NAME;
});

const nextStepButtonVisible = computed(() => ![0, 4, 5].includes(routingStore.stepIndex));

const previousStepButtonVisible = computed(
	() => routingStore.stepIndex !== 0 && !(routingStore.stepIndex === 4 && widgetStore.reservationData),
);

const firstStepButtonVisible = computed(() => routingStore.stepIndex !== 0);
</script>
