<template>
	<Widget3Layout>
		<template #header>
			<h2
				v-if="widgetStore.specialOffers.length"
				class="text-widget mb-1 flex items-center space-x-1 px-8 sm:mb-2">
				<p>{{ $t('widget.choose-special-offer') }}</p>
				<DiscountIcon />
			</h2>
			<div class="hidden md:block">
				<div v-if="widgetStore.specialOffers.length > 3" class="ml-5.25 w-[calc(100%-42px)]">
					<Carousel :breakpoints="carouselBreakpoints" :items-to-show="1" class="mx-auto" snapAlign="start">
						<Slide v-for="specialOffer in widgetStore.specialOffers" :key="specialOffer.id">
							<SpecialOfferItem :special-offer="specialOffer" />
						</Slide>

						<template #addons>
							<navigation v-if="widgetStore.specialOffers.length > 1" />
							<pagination v-if="widgetStore.specialOffers.length > 1" />
						</template>
					</Carousel>
				</div>
				<div
					v-else
					class="mx-auto ml-4 flex h-24 w-full flex-wrap justify-center space-y-3 overflow-x-hidden px-3.25 sm:w-179 sm:flex-nowrap sm:space-x-6 sm:space-y-0"
					:class="{
						'overflow-y-scroll sm:overflow-y-hidden': widgetStore.specialOffers.length > 1,
						'overflow-y-hidden': widgetStore.specialOffers.length <= 1,
					}">
					<SpecialOfferItem
						v-for="specialOffer in widgetStore.specialOffers"
						:special-offer="specialOffer"
						class="!w-full" />
				</div>
			</div>
			<div class="block h-24 md:hidden">
				<div class="ml-4 w-[calc(100%-32px)]">
					<Carousel :breakpoints="carouselBreakpoints" :items-to-show="1" class="mx-auto" snapAlign="start">
						<Slide v-for="specialOffer in widgetStore.specialOffers" :key="specialOffer.id">
							<SpecialOfferItem :special-offer="specialOffer" />
						</Slide>

						<template #addons>
							<navigation v-if="widgetStore.specialOffers.length > 1" />
							<pagination v-if="widgetStore.specialOffers.length > 1" />
						</template>
					</Carousel>
				</div>
			</div>
		</template>

		<div
			:class="{
				'-mt-14': widgetStore.specialOffers.length === 0,
			}">
			<h2 class="text-widget mb-1 px-3 sm:mb-2 sm:px-0">{{ $t('widget.choose-details') }}</h2>
			<div class="flex h-80 w-full items-start overflow-x-hidden sm:items-stretch sm:space-x-5.5 md:h-72">
				<div
					:class="{ 'w-full': !isSidebarNeeded }"
					class="border-widget dp__wrapper hidden max-h-[290px] overflow-hidden rounded-md border border-[3px] sm:block md:max-h-[300px] md:w-[458px] md:min-w-[458px]">
					<div>
						<Datepicker
							v-model="widgetStore.date"
							:allowed-dates="widgetStore.availableDates"
							v-bind="{
								...(widgetStore.generalTranslations?.['week-day-short'] && {
									dayNames: widgetStore.generalTranslations['week-day-short'],
								}),
							}"
							:enable-time-picker="false"
							:month-change-on-scroll="false"
							auto-apply
							format="yyyy-MM-dd"
							inline
							:locale="widgetStore.currentLocale"
							model-type="yyyy-MM-dd">
							<template #month-year="{ month, year, months, years, updateMonthYear, handleMonthYearChange }">
								<div class="flex w-full justify-between">
									<div class="icons flex items-center space-x-4">
										<span
											class="custom-icon cursor-pointer"
											@click="handleMonthYearChange(false)"
											v-if="month !== dayjs(widgetStore.availableDates[0]).month()">
											<svg
												fill="none"
												height="11"
												viewBox="0 0 8 11"
												width="8"
												xmlns="http://www.w3.org/2000/svg">
												<path
													d="M7.95695 1.75L3.06564 5.5L7.95695 9.25L6.97869 10.75L0.130859 5.5L6.97869 0.25L7.95695 1.75Z"
													fill="#0F066A" />
											</svg>
										</span>
										<span class="custom-icon cursor-pointer opacity-0" v-else>
											<svg
												fill="none"
												height="11"
												viewBox="0 0 8 11"
												width="8"
												xmlns="http://www.w3.org/2000/svg">
												<path
													d="M7.95695 1.75L3.06564 5.5L7.95695 9.25L6.97869 10.75L0.130859 5.5L6.97869 0.25L7.95695 1.75Z"
													fill="#0F066A" />
											</svg>
										</span>
										<p class="text-widget w-24 text-center text-sm font-bold uppercase">
											{{ $t(`main.month-name.${month + 1}`) }}
										</p>
										<span
											class="custom-icon cursor-pointer"
											@click="handleMonthYearChange(true)"
											v-if="
												month !==
												dayjs(widgetStore.availableDates[widgetStore.availableDates.length - 1]).month()
											">
											<svg
												fill="none"
												height="11"
												viewBox="0 0 9 11"
												width="9"
												xmlns="http://www.w3.org/2000/svg">
												<path
													d="M0.217368 9.25L5.10867 5.5L0.217367 1.75L1.19563 0.250001L8.04346 5.5L1.19563 10.75L0.217368 9.25Z"
													fill="#0F066A" />
											</svg>
										</span>
										<span class="custom-icon cursor-pointer opacity-0" v-else>
											<svg
												fill="none"
												height="11"
												viewBox="0 0 9 11"
												width="9"
												xmlns="http://www.w3.org/2000/svg">
												<path
													d="M0.217368 9.25L5.10867 5.5L0.217367 1.75L1.19563 0.250001L8.04346 5.5L1.19563 10.75L0.217368 9.25Z"
													fill="#0F066A" />
											</svg>
										</span>
									</div>

									<div class="text-widget text-sm font-bold">
										{{ year }}
									</div>
								</div>
							</template>
						</Datepicker>
					</div>
				</div>
				<div
					v-if="isSidebarNeeded"
					class="border-widget flex min-h-[300px] flex-grow select-none flex-col justify-normal space-y-2.5 rounded-md border border-[3px] px-5 py-2 pt-4 sm:h-[290px] sm:min-h-[290px] sm:justify-center sm:py-0 md:h-72 md:min-h-72">
					<SimpleDatepicker
						v-model="widgetStore.date"
						:allowed-dates="widgetStore.availableDates"
						:day-names="Object.values(widgetStore.props['dayShortNames'])"
						:enable-time-picker="false"
						:month-change-on-scroll="false"
						auto-apply
						format="yyyy-MM-dd"
						:locale="widgetStore.currentLocale"
						model-type="yyyy-MM-dd"
						:inputClasses="['opacity-60']"
						class="!w-full sm:hidden md:mr-5 md:!w-1/2 lg:!w-50"
						input-with-icon
						position="left">
						<template #month-year="{ month, year, months, years, updateMonthYear, handleMonthYearChange }">
							<div class="flex w-full justify-center">
								<div class="icons flex items-center space-x-4">
									<span
										class="custom-icon cursor-pointer"
										@click="handleMonthYearChange(false)"
										v-if="month !== dayjs(widgetStore.availableDates[0]).month()">
										<svg
											fill="none"
											height="11"
											viewBox="0 0 8 11"
											width="8"
											xmlns="http://www.w3.org/2000/svg">
											<path
												d="M7.95695 1.75L3.06564 5.5L7.95695 9.25L6.97869 10.75L0.130859 5.5L6.97869 0.25L7.95695 1.75Z"
												fill="#0F066A" />
										</svg>
									</span>
									<span class="custom-icon cursor-pointer opacity-0" v-else>
										<svg
											fill="none"
											height="11"
											viewBox="0 0 8 11"
											width="8"
											xmlns="http://www.w3.org/2000/svg">
											<path
												d="M7.95695 1.75L3.06564 5.5L7.95695 9.25L6.97869 10.75L0.130859 5.5L6.97869 0.25L7.95695 1.75Z"
												fill="#0F066A" />
										</svg>
									</span>
									<p class="text-widget w-24 text-center text-sm font-semibold uppercase">
										{{ $t(`main.month-name.${month + 1}`) }}
									</p>
									<span
										class="custom-icon cursor-pointer"
										@click="handleMonthYearChange(true)"
										v-if="
											month !==
											dayjs(widgetStore.availableDates[widgetStore.availableDates.length - 1]).month()
										">
										<svg
											fill="none"
											height="11"
											viewBox="0 0 9 11"
											width="9"
											xmlns="http://www.w3.org/2000/svg">
											<path
												d="M0.217368 9.25L5.10867 5.5L0.217367 1.75L1.19563 0.250001L8.04346 5.5L1.19563 10.75L0.217368 9.25Z"
												fill="#0F066A" />
										</svg>
									</span>
									<span class="custom-icon cursor-pointer opacity-0" v-else>
										<svg
											fill="none"
											height="11"
											viewBox="0 0 9 11"
											width="9"
											xmlns="http://www.w3.org/2000/svg">
											<path
												d="M0.217368 9.25L5.10867 5.5L0.217367 1.75L1.19563 0.250001L8.04346 5.5L1.19563 10.75L0.217368 9.25Z"
												fill="#0F066A" />
										</svg>
									</span>
								</div>
							</div>
						</template>
					</SimpleDatepicker>
					<div class="space-y-2.5" v-if="durationSetting.active.value">
						<p class="text-widget text-sm font-extrabold uppercase">
							{{ durationSetting.label.value }}
						</p>
						<NumberInput
							:label="durationSetting.durationLabel.value"
							:max="durationSetting.max.value"
							:min="durationSetting.min.value"
							:step="durationSetting.step.value"
							:value="durationSetting.model.value.duration"
							@update="(newValue) => (durationSetting.model.value.duration = newValue)" />
					</div>
					<div class="space-y-2.5">
						<div v-if="widgetStore.showingStatuses['pricePerPerson']" class="space-y-2.5">
							<div class="flex items-center space-x-3">
								<p class="text-widget text-sm font-extrabold uppercase">
									{{ widgetStore.gameFeatures['price_per_person'][0].translations['person-count'] }}
								</p>
								<div
									v-if="
										widgetStore.settings[widgetStore.getSettingKey('price_per_person', 'price_per_person')]
											.value
									"
									class="flex items-center space-x-1">
									<div
										class="icon-wrapper"
										v-html="widgetStore.gameFeatures['price_per_person'][0].data.icon"></div>
									<p
										class="text-widget text-sm font-bold uppercase"
										v-if="
											widgetStore.settings[
												widgetStore.getSettingKey('price_per_person', 'price_per_person_type')
											].value === 1
										">
										{{
											formatAmount(
												widgetStore.settings[
													widgetStore.getSettingKey('price_per_person', 'price_per_person')
												].value,
												widgetStore.club.country.currency,
											)
										}}
									</p>
								</div>
							</div>
							<NumberInput
								:max="widgetStore.gameDateSlotPeopleMaxLimit * widgetStore.form.slots_count"
								:min="widgetStore.gameDateSlotPeopleMinLimit"
								:step="1"
								:value="
									widgetStore.form.features[widgetStore.gameFeatures.price_per_person[0].id]['person_count']
								"
								@update="
									(newValue) =>
										(widgetStore.form.features[widgetStore.gameFeatures.price_per_person[0].id][
											'person_count'
										] = newValue)
								" />
						</div>
						<div v-if="widgetStore.showingStatuses['slotsCount']">
							<p class="text-widget text-sm font-extrabold uppercase">
								{{ widgetStore.gameTranslations['slots-quantity'] }}
							</p>
							<NumberInput
								:max="widgetStore.specialOfferSlotsCount ?? widgetStore.gameDateWidgetSlotsMaxLimit"
								:min="widgetStore.specialOfferSlotsCount ?? widgetStore.gameDateWidgetSlotsMinLimit"
								:step="1"
								:value="widgetStore.form.slots_count"
								@update="(newValue) => (widgetStore.form.slots_count = newValue)" />
						</div>
						<div v-if="widgetStore.showingStatuses['slotConveniences']">
							<div v-for="convenience in widgetStore.gameFeatures['slot_has_convenience']">
								<div
									v-if="widgetStore.availableGameSlotsConveniences?.includes(convenience.id)"
									class="flex items-center justify-between space-x-2">
									<InputLabel class="cursor-pointer select-none" for="band_status">
										{{ convenience.translations['reservation-checkbox-label'] }}
									</InputLabel>
									<Checkbox
										id="band_status"
										v-model="widgetStore.form['features'][convenience.id]['status']"
										:checked="widgetStore.form['features'][convenience.id]['status']"
										class="text-widget h-4 w-4" />
								</div>
							</div>
						</div>
					</div>
					<div v-if="widgetStore.showingStatuses['slotHasType']" v-click-away="closeSlotHasTypeSelectInput">
						<Popper :show="slotHasTypeSelectExpanded" class="w-full !border-r-0">
							<div
								class="flex w-full items-center justify-between"
								@click="slotHasTypeSelectExpanded = !slotHasTypeSelectExpanded">
								<p
									v-if="
										widgetStore.form.features[widgetStore.gameFeatures['slot_has_type'][0].id]['name'] ===
										null
									"
									class="text-widget text-sm font-extrabold uppercase">
									{{ widgetStore.gameFeatures['slot_has_type'][0].translations['widget-select-name'] }}
								</p>
								<p v-else class="text-widget text-sm font-extrabold uppercase">
									{{
										widgetStore.gameFeatures['slot_has_type'][0]['translations'][
											'type-' +
												widgetStore.form.features[widgetStore.gameFeatures['slot_has_type'][0].id]['name']
										]
									}}
								</p>
								<div>
									<ChevronDownIcon class="-mr-2.25 cursor-pointer" />
								</div>
							</div>
							<template #content>
								<ul class="detail-select">
									<li
										v-for="option in widgetStore.gameSlotTypes"
										:class="{
											active:
												widgetStore.form.features[widgetStore.gameFeatures['slot_has_type'][0].id]['name'] ===
												option,
										}"
										class="w-full"
										@click="
											() => {
												widgetStore.selectType(option);
												closeSelectInputs();
											}
										">
										{{ widgetStore.gameFeatures['slot_has_type'][0]['translations']['type-' + option] }}
									</li>
								</ul>
							</template>
						</Popper>
					</div>
					<div
						v-if="
							widgetStore.showingStatuses['slotHasSubType'] && widgetStore.slotHasSubtypeOptions.length > 1
						"
						v-click-away="closeSlotHasSubtypeSelectInput">
						<Popper :show="slotHasSubtypeSelectExpanded" class="w-full !border-r-0">
							<div
								class="flex w-full items-center justify-between"
								@click="slotHasSubtypeSelectExpanded = !slotHasSubtypeSelectExpanded">
								<p
									v-if="
										widgetStore.form.features[widgetStore.gameFeatures['slot_has_subtype'][0].id]['name'] ===
										null
									"
									class="text-widget text-sm font-extrabold uppercase">
									{{ widgetStore.gameFeatures['slot_has_subtype'][0].translations['widget-select-name'] }}
								</p>
								<p v-else class="text-widget text-sm font-extrabold uppercase">
									{{ widgetStore.form.features[widgetStore.gameFeatures['slot_has_subtype'][0].id]['name'] }}
								</p>
								<div>
									<ChevronDownIcon class="-mr-2.25 cursor-pointer" />
								</div>
							</div>
							<template #content>
								<ul class="detail-select">
									<li
										v-for="option in widgetStore.slotHasSubtypeOptions"
										:class="{
											active:
												widgetStore.form.features[widgetStore.gameFeatures['slot_has_subtype'][0].id][
													'name'
												] === option.subtype,
										}"
										class="w-full"
										@click="selectSubtype(option)">
										{{
											widgetStore.gameFeatures['slot_has_subtype'][0]['translations'][
												'type-' + option.subtype
											]
										}}
									</li>
								</ul>
							</template>
						</Popper>
					</div>
					<div
						v-if="widgetStore.showingStatuses['slotHasSubslots'] && widgetStore.gameParentSlots.length > 1"
						v-click-away="closeSlotHasSubslotsSelectInput">
						<Popper :show="slotHasSubslotsSelectExpanded" class="w-full !border-r-0">
							<div
								class="flex w-full items-center justify-between"
								@click="slotHasSubslotsSelectExpanded = !slotHasSubslotsSelectExpanded">
								<p
									v-if="
										widgetStore.form.features[widgetStore.gameFeatures['slot_has_parent'][0].id][
											'parent_slot_id'
										] === null
									"
									class="text-widget text-sm font-extrabold uppercase">
									{{ widgetStore.gameFeatures['slot_has_parent'][0].translations['choose-parent-slot'] }}
								</p>
								<p v-else class="text-widget text-sm font-extrabold uppercase">
									{{
										widgetStore.gameParentSlots.find(
											(parentSlot) =>
												parentSlot.id ===
												widgetStore.form.features[widgetStore.gameFeatures['slot_has_parent'][0].id][
													'parent_slot_id'
												],
										).name
									}}
								</p>
								<div>
									<ChevronDownIcon class="-mr-2.25 cursor-pointer" />
								</div>
							</div>
							<template #content>
								<ul class="detail-select">
									<li
										v-for="singleParentSlot in widgetStore.gameParentSlots"
										:class="{
											active:
												widgetStore.form.features[widgetStore.gameFeatures['slot_has_parent'][0].id][
													'parent_slot_id'
												] === singleParentSlot.id,
										}"
										class="w-full"
										@click="selectParentSlot(singleParentSlot.id)">
										{{ singleParentSlot.name }}
									</li>
								</ul>
							</template>
						</Popper>
					</div>
					<div
						v-if="widgetStore.showingStatuses['bookSingularSlotByCapacity']"
						:class="{
							'opacity-0':
								widgetStore.gameFeatures['slot_has_parent'].length &&
								!widgetStore.form.features[widgetStore.gameFeatures['slot_has_parent'][0].id][
									'parent_slot_id'
								],
						}"
						v-click-away="closeBookSingularSlotByCapacitySelectInput">
						<Popper :show="bookSingularSlotByCapacitySelectExpanded" class="w-full !border-r-0">
							<div
								class="flex w-full items-center justify-between"
								@click="bookSingularSlotByCapacitySelectExpanded = !bookSingularSlotByCapacitySelectExpanded">
								<p class="text-widget text-sm font-extrabold uppercase">
									{{
										widgetStore.gameFeatures['book_singular_slot_by_capacity'][0].translations[
											'widget-capacity-label'
										]
									}}{{
										widgetStore.form.features[widgetStore.gameFeatures.book_singular_slot_by_capacity[0].id][
											'capacity'
										]
											? ': ' +
											  widgetStore.form.features[
													widgetStore.gameFeatures.book_singular_slot_by_capacity[0].id
											  ]['capacity']
											: ''
									}}
								</p>
								<div>
									<ChevronDownIcon class="-mr-2.25 cursor-pointer" />
								</div>
							</div>
							<template #content>
								<ul class="detail-select">
									<li
										v-for="availableGameSlotCapacity in [
											...new Set(widgetStore.availableGameSlotsCapacities),
										]"
										:class="{
											active:
												widgetStore.form.features[
													widgetStore.gameFeatures.book_singular_slot_by_capacity[0].id
												]['capacity'] === availableGameSlotCapacity,
										}"
										class="w-full"
										@click="selectSlotCapacity(availableGameSlotCapacity)">
										{{ availableGameSlotCapacity }}
									</li>
								</ul>
							</template>
						</Popper>
					</div>
					<div v-if="widgetStore.showingStatuses['personAsSlot']" class="space-y-2.5">
						<div class="flex items-center space-x-3">
							<p class="text-widget text-sm font-extrabold uppercase">
								{{ widgetStore.gameFeatures['person_as_slot'][0].translations['slots-quantity'] }}
							</p>
						</div>
						<NumberInput
							:max="
								widgetStore.gameFeatures['slot_has_parent'].length &&
								widgetStore.form.features[widgetStore.gameFeatures['slot_has_parent'][0].id]['parent_slot_id']
									? widgetStore.gameDateWidgetSlotsMaxLimit
									: widgetStore.form.slots_count
							"
							:min="
								widgetStore.gameFeatures['slot_has_parent'].length &&
								!widgetStore.form.features[widgetStore.gameFeatures['slot_has_parent'][0].id][
									'parent_slot_id'
								]
									? widgetStore.form.slots_count
									: 1
							"
							:step="1"
							:value="widgetStore.form.slots_count"
							@update="(newValue) => (widgetStore.form.slots_count = newValue)" />
					</div>
				</div>
			</div>
		</div>
	</Widget3Layout>
</template>

<script lang="ts" setup>
import '@vuepic/vue-datepicker/dist/main.css';
import Widget3Layout from '@/Layouts/Widget3Layout.vue';
import Datepicker from '@vuepic/vue-datepicker';
import 'vue3-carousel/dist/carousel.css';
import { Carousel, Navigation, Pagination, Slide } from 'vue3-carousel';
import NumberInput from '@/Components/Widget-3/Icons/NumberInput.vue';
import InputLabel from '@/Components/Widget-3/InputLabel.vue';
import Checkbox from '@/Components/Widget-3/Checkbox.vue';
import { useWidgetStore } from '@/Stores/widget';
import SpecialOfferItem from '@/Components/Widget-3/Icons/SpecialOfferItem.vue';
import Popper from 'vue3-popper';
import ChevronDownIcon from '@/Components/Dashboard/Icons/ChevronDownIcon.vue';
import { computed, onMounted, ref } from 'vue';
import { useNumber } from '@/Composables/useNumber';
import { useWidgetDurationSetting } from '@/Composables/widget/useWidgetDurationSetting';
import DiscountIcon from '@/Components/Widget-3/Icons/DiscountIcon.vue';
import SimpleDatepicker from '@/Components/Widget-3/SimpleDatepicker.vue';
import dayjs from 'dayjs';
import { removeTime } from '@/Utils';

const { formatAmount } = useNumber();
const widgetStore = useWidgetStore();
const durationSetting = useWidgetDurationSetting();
const widgetColor: string = widgetStore.widgetColor;
let isSidebarNeeded: boolean = Object.values(widgetStore.showingStatuses).some(Boolean);
const slotHasTypeSelectExpanded = ref<boolean>(false);
const slotHasSubtypeSelectExpanded = ref<boolean>(false);
const slotHasSubslotsSelectExpanded = ref<boolean>(false);
const bookSingularSlotByCapacitySelectExpanded = ref<boolean>(false);

function closeSelectInputs(): void {
	closeSlotHasTypeSelectInput();
	closeSlotHasSubtypeSelectInput();
	closeSlotHasSubslotsSelectInput();
	closeBookSingularSlotByCapacitySelectInput();
}

function closeSlotHasSubslotsSelectInput(): void {
	slotHasSubslotsSelectExpanded.value = false;
}

function closeSlotHasTypeSelectInput(): void {
	slotHasTypeSelectExpanded.value = false;
}

function closeSlotHasSubtypeSelectInput(): void {
	slotHasSubtypeSelectExpanded.value = false;
}

function closeBookSingularSlotByCapacitySelectInput(): void {
	bookSingularSlotByCapacitySelectExpanded.value = false;
}

interface SubType {
	name: string;
	type: string;
}

function selectSubtype(subtype: SubType): void {
	closeSelectInputs();
	let featureId = widgetStore.gameFeatures.slot_has_subtype[0].id;
	widgetStore.form.features[featureId]['name'] = subtype?.['subtype'] ?? null;
}

function selectParentSlot(newParentSlot: any): void {
	closeSelectInputs();
	let featureId = widgetStore.gameFeatures['slot_has_parent'][0].id;
	widgetStore.form.features[featureId]['parent_slot_id'] = newParentSlot;
  if(widgetStore.gameFeatures['person_as_slot'].length) {
    let personAsSlotfeatureId = widgetStore.gameFeatures['person_as_slot']?.[0]?.id;
    widgetStore.form.features[personAsSlotfeatureId]['new_parent_slot_id'] = newParentSlot;
  }
}

function selectSlotCapacity(capacity: number): void {
	closeSelectInputs();
	let featureId = widgetStore.gameFeatures.book_singular_slot_by_capacity[0].id;
	widgetStore.form.features[featureId]['capacity'] = capacity;
}

onMounted(() => {
  widgetStore.currentStep = 'details';
	widgetStore.form.start_at = removeTime(widgetStore.form.start_at ?? widgetStore.date);
	widgetStore.priceLoadedStatus = false;
	widgetStore.priceLoadingStatus = true;
	if (
		widgetStore.form.special_offer_id &&
		widgetStore.club.specialOffers?.find(
			(specialOffer) => specialOffer.id === widgetStore.form.special_offer_id,
		)?.active_by_default === true
	) {
		widgetStore.form.special_offer_id = null;
	}
	widgetStore.reloadSets();
	if (widgetStore.gameSlotTypes?.length === 1) {
		widgetStore.selectType(widgetStore.gameSlotTypes[0]);
	}
});

let carouselBreakpoints: {
	[breakpoint: number]: {
		itemsToShow: number;
	};
} = {
	480: {
		itemsToShow: Math.min(2, widgetStore.specialOffers.length),
	},
	728: {
		itemsToShow: Math.min(3, widgetStore.specialOffers.length),
	},
};
</script>

<style scoped>
.icon-wrapper:deep(svg) {
	@apply h-5 w-5;
	fill: v-bind(widgetColor) !important;
}
</style>

<style>
.detail-select {
	@apply ml-3 w-[calc(100vw-52px)] rounded border border-[3px] bg-white sm:w-51.5;
	border-color: v-bind(widgetColor);

	li {
		@apply cursor-pointer px-5 py-0.25;

		&:hover,
		&.active {
			@apply text-white;
			background-color: v-bind(widgetColor);
		}

		&:active {
			@apply opacity-60;
		}
	}
}

.carousel__prev {
	margin-left: -20px;
}

.carousel__next {
	margin-right: -20px;
}

.dp__menu {
	min-width: 180px !important;
}

.carousel__pagination {
	@apply hidden;
}

.dp__calendar_item {
	@apply !mx-0.25 py-0.25 sm:!mx-1.25;
}

.dp__menu {
	@apply !h-56 py-1;
}

.dp__menu_inner {
	@apply my-auto;
}

.dp__wrapper {
	@apply px-6 pb-0 pt-1.5;

	.dp__outer_menu_wrap,
	.dp__instance_calendar,
	.dp__theme_light,
	.dp__calendar_header,
	.dp__calendar,
	.dp__calendar_row {
		@apply !w-full !border-0;
	}

	.dp__calendar_header_separator {
		@apply hidden;
	}

	.dp__calendar_header_item {
		@apply border-t;
		border-color: #ccc;
	}

	.dp__cell_inner {
		@apply mx-auto h-5 h-6 w-8 rounded-lg text-sm font-semibold;
		color: v-bind(widgetColor);
	}

	.dp__active_date {
		@apply text-white;
		background-color: v-bind(widgetColor);
	}

	.dp__cell_disabled {
		@apply bg-gray-9 text-gray-3;
	}

	.dp__today {
		border-color: v-bind(widgetColor);
	}
}
</style>
