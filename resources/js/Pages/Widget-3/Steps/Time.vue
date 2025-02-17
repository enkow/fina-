<template>
	<Widget3Layout :with-reservation-details="true">
		<div
			v-if="widgetStore.datetimeBlocks.length === 0"
			class="text-widget border-widget flex h-59 w-full flex-col items-center justify-center space-y-6 rounded-md border text-center text-base font-extrabold uppercase sm:border-[3px] md:text-xl">
			<p>
				{{ $t('widget.no-results') }}
			</p>
			<p>{{ $t('widget.try-changing-your-search-criteria') }}</p>
			<button
				type="button"
				class="flex cursor-pointer items-center space-x-3"
				@click="routingStore.previousStep()">
				<StepButton as="span">
					<ChevronLeftIcon />
				</StepButton>
				<p>{{ $t('widget.go-back') }}</p>
			</button>
		</div>
		<div v-else>
			<p
				v-if="
					widgetStore.gameFeatures.has_price_announcements_settings.length > 0 &&
					widgetStore.settings[
						widgetStore.getSettingKey('has_price_announcements_settings', 'price_non_zero_announcement')
					]?.value?.length > 0 &&
					widgetStore.price > 0 &&
					widgetStore.priceLoadedStatus
				"
				class="text-widget mb-6 mt-2 font-extrabold">
				{{
					widgetStore.settings[
						widgetStore.getSettingKey('has_price_announcements_settings', 'price_non_zero_announcement')
					].value
				}}
			</p>
			<p
				v-if="
					widgetStore.gameFeatures.has_price_announcements_settings.length > 0 &&
					widgetStore.settings[
						widgetStore.getSettingKey('has_price_announcements_settings', 'price_zero_announcement')
					]?.value?.length > 0 &&
					widgetStore.start_at !== null &&
					widgetStore.price === 0 &&
					widgetStore.priceLoadedStatus
				"
				class="text-widget mb-6 mt-2 font-extrabold">
				{{
					widgetStore.settings[
						widgetStore.getSettingKey('has_price_announcements_settings', 'price_zero_announcement')
					].value
				}}
			</p>
			<p v-if="widgetStore.announcementContent" class="text-widget mb-6 mt-2 font-extrabold">
				{{ widgetStore.announcementContent }}
			</p>

			<div>
				<h2 class="text-widget">
					{{ $t('widget.choose-time') }}
				</h2>
				<div
					id="style-3"
					:class="{
						'max-h-42': widgetStore.announcementContent,
						'max-h-56': !widgetStore.announcementContent,
					}"
					class="grid flex-1 grid-cols-3 gap-x-3 gap-y-4 overflow-y-auto xs:grid-cols-4 sm:grid-cols-5 sm:gap-x-5">
					<TimeBlock
						v-for="datetimeBlock in widgetStore.datetimeBlocks"
						:class="{
							active: datetimeBlock === widgetStore.form.start_at,
						}"
						:value="getDatetimeBlockValue(datetimeBlock)"
						:with-discount="
							(widgetStore.specialOffer &&
								widgetStore.isStartAtInSpecialOfferTimeRanges(
									widgetStore.specialOffer,
									datetimeBlock,
									true,
									widgetStore.form.duration,
								)) ||
							((widgetStore.specialOffer === null || widgetStore.specialOffer.active_by_default === true) &&
								widgetStore.getActiveByDefaultSpecialOfferForGivenTimeBlock(datetimeBlock) !== null)
						"
						@click="widgetStore.selectDatetime(datetimeBlock)" />
				</div>
			</div>
		</div>
	</Widget3Layout>
</template>

<script lang="ts" setup>
import Widget3Layout from '@/Layouts/Widget3Layout.vue';
import TimeBlock from '@/Components/Widget-3/TimeBlock.vue';
import StepButton from '@/Components/Widget-3/StepButton.vue';
import ChevronLeftIcon from '@/Components/Widget-3/Icons/ChevronLeftIcon.vue';
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';
import dayjs from 'dayjs';
import { onMounted } from 'vue';

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();
const widgetColor: string = widgetStore.widgetColor;

widgetStore.reloadSets();

function getDatetimeBlockValue(datetimeBlock: string) {
  let result = dayjs(datetimeBlock).format('HH:mm');
  if (result === '00:00') {
    result = '24:00';
  }
  return result;
}

onMounted(() => {
  widgetStore.currentStep = 'time';
  widgetStore.price = null;
  widgetStore.form.start_at = widgetStore.form.start_at.split(" ")[0];
  widgetStore.priceLoadedStatus = false;
  Object.keys(widgetStore.sets).forEach((setKey: any) => {
    widgetStore.sets[setKey].selected = 0;
  });
	widgetStore.reloadAnnouncementContent();
	// widgetStore.priceLoadedStatus = false;
	// widgetStore.priceLoadingStatus = true;
});
</script>
