<template>
	<div class="md:px-8">
		<h2
			:class="{ 'md:ml-44': !widgetStore.specialOffer }"
			:style="{ color: widgetStore.widgetColor }"
			class="px-2 md:px-0">
			{{ $t('widget.reservation-details') }}
		</h2>

		<div class="flex text-white md:space-x-4">
			<div
				:style="{
					backgroundColor: widgetStore.form.special_offer_id ? widgetStore.widgetColor : 'transparent',
				}"
				:class="{
					'!h-28.5': desktopAddonLabel.length,
				}"
				class="hidden !h-24 w-52 items-center justify-center rounded-none md:flex md:rounded-md">
				<div class="space-y-0.5 text-center">
					<p class="text-sm font-extrabold">
						{{ widgetStore.specialOffer?.name }}
					</p>
					<p class="text-xs font-bold">
						{{ widgetStore.specialOffer?.description }}
					</p>
				</div>
			</div>
			<div
				:style="{ backgroundColor: widgetStore.widgetColor }"
				class="flex w-full flex-wrap rounded-none md:rounded-md">
				<div class="flex h-full flex-1 items-center justify-center">
					<div
						:class="{
							'!pb-0 md:!pb-4': widgetStore.form.special_offer_id && !mobileAddonLabel.length,
							'md:!pb-2 md:!pt-2': desktopAddonLabel.length,
						}"
						class="flex w-full flex-wrap pb-2 pt-2 font-semibold md:pb-4 md:pt-4">
						<div
							v-if="sectionShowingStatuses[0]"
							:class="[sectionWidthClass, { 'border-r-2': hasSectionAfter(0) }]"
							class="reservation-details__section">
							<p class="text-2xl font-bold xs:text-3xl">
								{{ priceShowing ? (widgetStore.finalPrice / 100).toFixed(2) : '---' }}
							</p>
							<p v-if="priceShowing" class="text-bg -mb-0.75 xs:-mb-1.5 xs:text-xl">
								{{ widgetStore.currencySymbols[widgetStore.club.country.currency] }}
							</p>
							<div
								v-if="widgetStore.showingStatuses['pricePerPerson'] && pricePerPerson > 0"
								class="flex w-full items-center justify-center space-x-1 text-xs xs:text-sm md:justify-center">
								<div
									class="icon-wrapper"
									v-if="widgetStore.gameFeatures['price_per_person'][0].data.icon"
									v-html="widgetStore.gameFeatures['price_per_person'][0].data.icon"></div>

								<p class="uppercase">
									({{ (pricePerPerson / 100).toFixed(2)
									}}{{ widgetStore.currencySymbols[widgetStore.club.country.currency] }})
								</p>
							</div>
						</div>
						<div
							v-if="sectionShowingStatuses[1]"
							:class="[sectionWidthClass, { 'border-r-2': hasSectionAfter(1) }]"
							class="reservation-details__section text-xs xs:text-base xs:text-sm">
							<div class="flex w-full items-center space-x-1 sm:space-x-2">
								<CalendarIcon class="min-w-5" />
								<p>{{ dayjs(widgetStore.form.start_at ?? widgetStore.date).format('DD.MM.YYYY') }}</p>
							</div>
							<div class="flex w-full items-center">
								<ClockIcon class="mr-1 min-w-5 sm:mr-2.5" />
								<p
									class="mr-1"
									:class="{
										'!text-xs': widgetStore.start_at && widgetStore.form.duration % 60 !== 0,
									}">
									<template v-if="!widgetStore.showingStatuses['fullDayReservations']">
										{{ widgetStore.durationLabel }}
									</template>
									<template
										v-if="
											widgetStore.form.start_at &&
											widgetStore.isStartAtRequired &&
											widgetStore.datetimeBlocks.length
										">
										{{
											(!widgetStore.showingStatuses['fullDayReservations'] ? ' (' : '') +
											dayjs(widgetStore.form.start_at).format('HH:mm') +
											(!widgetStore.showingStatuses['fullDayReservations'] ? ')' : '')
										}}
									</template>
								</p>
							</div>
						</div>
						<div
							v-if="sectionShowingStatuses[2] === true"
							:class="[sectionWidthClass, { 'border-r-2': hasSectionAfter(2) }]"
							class="reservation-details__section text-xs xs:text-base xs:text-sm">
							<div
								v-if="widgetStore.showingStatuses['slotsCount']"
								class="flex w-full items-center justify-start space-x-1">
								<p>{{ widgetStore.form.slots_count }}</p>
								<p class="text-xxs">x</p>
								<p class="uppercase">
									{{ slotNameLabel }}
								</p>
							</div>
							<div
								v-if="widgetStore.showingStatuses['pricePerPerson']"
								class="flex w-full items-center justify-start space-x-3">
								<p class="uppercase">
									{{
										widgetStore.form['features'][widgetStore.gameFeatures.price_per_person[0].id][
											'person_count'
										]
									}}
								</p>
								<PeopleUnfilledIcon class="w-4" />
							</div>
							<div
								v-if="widgetStore.showingStatuses['slotHasSubType']"
								class="flex w-full items-center justify-start space-x-1">
								<p class="uppercase">
									{{
										widgetStore.gameFeatures['slot_has_type'][0]['translations'][
											'type-' +
												widgetStore.form['features'][widgetStore.gameFeatures.slot_has_type[0].id]['name']
										]
									}}
									{{
										widgetStore.gameFeatures['slot_has_subtype'][0]['translations'][
											'type-' +
												widgetStore.form['features'][widgetStore.gameFeatures.slot_has_subtype[0].id]['name']
										]
									}}
								</p>
							</div>
							<div
								v-else-if="widgetStore.showingStatuses['slotHasType']"
								class="flex w-full items-center justify-start space-x-1">
								<p class="uppercase">
									{{
										widgetStore.gameFeatures['slot_has_type'][0]['translations'][
											'type-' +
												widgetStore.form['features'][widgetStore.gameFeatures.slot_has_type[0].id]['name']
										]
									}}
								</p>
							</div>
							<div
								v-if="widgetStore.showingStatuses['slotHasSubslots']"
								class="flex w-full items-center justify-start space-x-3">
								<p class="uppercase">
									{{
										widgetStore.gameParentSlots.find(
											(parentSlot) =>
												parentSlot.id ===
												widgetStore.form['features'][widgetStore.gameFeatures.slot_has_parent[0].id]
													.parent_slot_id,
										)?.name
									}}
								</p>
							</div>
							<div
								v-if="widgetStore.showingStatuses['bookSingularSlotByCapacity']"
								class="flex w-full items-center justify-start space-x-2">
								<p class="uppercase">
									{{
										widgetStore.form['features'][
											widgetStore.gameFeatures.book_singular_slot_by_capacity[0].id
										].capacity
									}}
								</p>
								<PeopleUnfilledIcon class="w-4" />
							</div>
							<div
								v-if="widgetStore.showingStatuses['personAsSlot']"
								class="flex w-full items-center justify-start space-x-2">
								<p class="uppercase">
									{{ widgetStore.form.slots_count }}
								</p>
								<PeopleUnfilledIcon class="w-4" />
							</div>
						</div>
						<div v-if="mobileAddonLabel.length" class="block w-full px-2.5 md:hidden">
							<div class="mt-3 w-full border-t-2 py-2 pb-0 text-center text-xs font-extrabold">
								{{ mobileAddonLabel }}
							</div>
						</div>
						<div v-if="desktopAddonLabel.length" class="hidden w-full px-2.5 md:block">
							<div class="mt-2 w-full border-t-2 py-2 pb-0 text-center text-xs font-extrabold">
								{{ desktopAddonLabel }}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.icon-wrapper:deep(svg) {
	@apply h-5 w-5;
	fill: white !important;
}

.reservation-details__section {
	@apply flex h-16 flex-wrap items-center justify-center px-1 xs:px-2 sm:px-6;
}
</style>

<script lang="ts" setup>
import ClockIcon from '@/Components/Widget-3/Icons/ClockIcon.vue';
import CalendarIcon from '@/Components/Widget-3/Icons/CalendarIcon.vue';
import { useWidgetStore } from '@/Stores/widget';
import dayjs from 'dayjs';
import PeopleUnfilledIcon from '@/Components/Dashboard/Icons/PeopleUnfilledIcon.vue';
import { useNumber } from '@/Composables/useNumber';
import { computed } from 'vue';
import { SetModel } from '@/Types/models';
import {useRoutingStore} from "@/Stores/routing";

const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();
const { formatAmount } = useNumber();

const priceShowingMem = computed<boolean>(() => {
	if (!widgetStore.datetimeBlocks.includes(widgetStore.form.start_at)) {
		return false;
	}
	if (widgetStore.priceLoadedStatus === false && widgetStore.finalPrice === 0) {
		return false;
	}
	return true;
});

const pricePerPerson = computed(() => {
	if (
		widgetStore.settings[widgetStore.getSettingKey('price_per_person', 'price_per_person_type')].value === 2
	) {
		return 0;
	}
	return (
		widgetStore.form['features'][widgetStore.gameFeatures.price_per_person[0].id]['person_count'] *
		widgetStore.settings[widgetStore.getSettingKey('price_per_person', 'price_per_person')].value
	);
});

const priceShowing = computed<boolean>(() => {
	let priceOldShowing = priceShowing.value;
	return widgetStore.priceLoadingStatus === false ? priceShowingMem.value : priceOldShowing;
});

const mobileAddonLabel = computed<string>(() => {
	let items = [];
	if (widgetStore.specialOffer) {
		items.push(
			(widgetStore.specialOffer?.name + ' ' + (widgetStore.specialOffer?.description ?? '')).toUpperCase(),
		);
	}
	widgetStore.sets
		.filter((set: SetModel) => set.selected > 0)
		.forEach((set: SetModel) => {
			items.push(set.selected + 'x ' + set.name.toUpperCase());
		});
	return items.join(', ');
});

const desktopAddonLabel = computed<string>(() => {
	let items = [];
	widgetStore.sets
		.filter((set: SetModel) => set.selected > 0)
		.forEach((set: SetModel) => {
			items.push(set.selected + 'x ' + set.name.toUpperCase());
		});
	return items.join(', ');
});

const sectionShowingStatuses: {
	[index: number]: boolean;
} = {
	0: true,
	1: true,
	2:
		widgetStore.showingStatuses['slotsCount'] ||
		widgetStore.showingStatuses['pricePerPerson'] ||
		widgetStore.showingStatuses['slotHasSubtype'] ||
		widgetStore.showingStatuses['slotHasType'] ||
		widgetStore.showingStatuses['slotHasSubslots'] ||
		widgetStore.showingStatuses['bookSingularSlotByCapacity'],
};

const sectionsCount: number = Object.values(sectionShowingStatuses).filter((value) => value === true).length;

let sectionWidthClass: string = sectionsCount > 1 ? 'w-1/' + sectionsCount : 'w-full';

const slotNameLabel = computed<string>(() => {
	if (widgetStore.gameFeatures['slot_has_convenience'].length) {
		let firstConvenienceFeature = widgetStore.gameFeatures['slot_has_convenience'][0];
		if (
			widgetStore.form.features[firstConvenienceFeature.id]['status'] &&
			firstConvenienceFeature.translations['slot-with-convenience'].length
		) {
			return firstConvenienceFeature.translations['slot-with-convenience'];
		} else if (firstConvenienceFeature.translations['slot-without-convenience'].length) {
			return firstConvenienceFeature.translations['slot-without-convenience'];
		}
	}
	return widgetStore.gameTranslations['slot-singular-short'];
});

function hasSectionAfter(index: number): boolean {
	let result: boolean = false;
	Object.keys(sectionShowingStatuses).forEach((key) => {
		if (parseInt(key) > index && sectionShowingStatuses[key] === true) {
			result = true;
		}
	});
	return result;
}
</script>
