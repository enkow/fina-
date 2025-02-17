<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.games.statistics.main', { game: game }),
				label: $t('statistics.weekly-statistics'),
			},
		]">
		<div class="col-span-12 space-y-4 px-3 py-8 xl:col-span-9 xl:px-10">
			<div class="flex w-full flex-wrap">
				<div class="flex w-full flex-col gap-6 xl:flex-row">
					<div class="hidden w-full grid-cols-8 gap-x-3 gap-y-3 md:grid">
						<div class="col-span-4">
							<SimpleSelect
								:searchable="true"
								:filterable="true"
								v-model="fromTimeFilter"
								:options="fromOptions"
								class="margins-sm w-full" />
						</div>
						<div class="col-span-4">
							<SimpleSelect
								:searchable="true"
								:filterable="true"
								v-model="toTimeFilter"
								:options="toOptions"
								class="margins-sm w-full" />
						</div>
						<div class="col-span-4 xl:flex xl:flex-col xl:justify-center">
							<RangeDatepicker
								v-model="dateRange"
								:inputClasses="['opacity-60']"
								class="w-full"
								input-with-icon
								position="left" />
						</div>
						<div class="col-span-4 xl:flex xl:flex-col xl:justify-center">
							<SimpleSelect
								v-model="status"
								:options="statusOptions"
								class="margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white"
								placeholder="Typ" />
						</div>
						<div class="col-span-4 xl:flex xl:flex-col xl:justify-end">
							<GameFilter
								:game="game"
								:custom-route-attributes="currentQueryArray"
								custom-route="club.games.statistics.weekly"
								table-name="reservations" />
						</div>
						<div class="col-span-4 xl:flex xl:flex-col xl:justify-end">
							<SimpleSelect
								v-model="paymentType"
								:options="paymentTypeOptions"
								class="min-w-50 margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white"
								placeholder="Typ" />
						</div>
					</div>
					<div>
						<WideHelper float="left" class="h-full">
							<template #mascot>
								<Mascot :type="19" class="-ml-2" />
							</template>
							<template #button>
								<Button
									:href="usePage().props.generalTranslations['help-special-offers-link']"
									class="grey w-full sm:w-fit"
									type="link">
									{{ $t('help.learn-more') }}
								</Button>
							</template>
							{{ usePage().props.generalTranslations['help-special-offers-content'] }}
						</WideHelper>
					</div>
				</div>
				<div class="mt-3 w-full space-y-3 md:hidden">
					<ExportDropdown class="!w-full" />
					<RangeDatepicker
						v-model="dateRange"
						:inputClasses="['opacity-60']"
						input-with-icon
						position="left" />
					<SimpleSelect
						v-model="status"
						:options="statusOptions"
						class="margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white w-full"
						placeholder="Typ" />
					<GameFilter
						:game="game"
						:custom-route-attributes="currentQueryArray"
						class="!w-full"
						custom-route="club.games.statistics.weekly"
						table-name="reservations" />
					<SimpleSelect
						v-model="paymentType"
						:options="paymentTypeOptions"
						class="min-w-50 margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white"
						placeholder="Typ" />
				</div>
				<h2 class="mb-8 mt-7">{{ $t('statistics.average-turnover') }}</h2>
				<div class="flex w-full space-x-2">
					<div class="grid w-full grid-cols-1 gap-x-5.5 gap-y-4 md:grid-cols-4 2xl:grid-cols-6">
						<div
							v-for="(day, index) in statistics.offline"
							class="relative h-18 w-full overflow-hidden rounded-md bg-brand-base pl-6">
							<div class="absolute -top-6 left-0">
								<svg
									class="-ml-10 opacity-40"
									fill="none"
									height="135"
									viewBox="0 0 115 135"
									width="115"
									xmlns="http://www.w3.org/2000/svg">
									<ellipse
										cx="49.9657"
										cy="59.5"
										fill="url(#paint0_linear_1630_15505)"
										rx="64.587"
										ry="82.5"
										transform="rotate(-180 49.9657 59.5)" />
									<defs>
										<linearGradient
											id="paint0_linear_1630_15505"
											gradientUnits="userSpaceOnUse"
											x1="49.9657"
											x2="49.9657"
											y1="-23"
											y2="142">
											<stop stop-color="white" />
											<stop offset="1" stop-color="white" stop-opacity="0" />
										</linearGradient>
									</defs>
								</svg>
							</div>
							<div class="flex h-full w-full flex-wrap items-center text-white">
								<p v-if="paymentType === 1" class="w-full text-2xl font-semibold">
									{{ formatAmount(countDaysOfWeekInRange(index) ? (statistics.online[index]['amount'] / countDaysOfWeekInRange(index)) : 0) }}
								</p>
								<p v-else-if="paymentType === 2" class="w-full text-2xl font-semibold">
									{{ formatAmount(countDaysOfWeekInRange(index) ? (statistics.offline[index]['amount'] / countDaysOfWeekInRange(index)) : 0) }}
								</p>
								<p v-else class="w-full text-2xl font-semibold">
									{{ formatAmount(countDaysOfWeekInRange(index) ? ((statistics.online[index]['amount'] / countDaysOfWeekInRange(index)) + (statistics.offline[index]['amount'] / countDaysOfWeekInRange(index))) : 0) }}
								</p>
								<p class="-mt-6 w-full text-base font-medium capitalize">
									{{ $t(`main.week-day.${index}`) }}
								</p>
							</div>
						</div>
					</div>
				</div>
				<div class="flex w-full justify-end space-x-11 pt-8">
					<div v-if="paymentType !== 2" class="flex items-center space-x-2">
						<div class="h-4.5 w-4.5 bg-brand-dark"></div>
						<p class="text-xs font-light">
							{{ $t('statistics.online-turnover') }}
						</p>
					</div>
					<div v-if="paymentType !== 1" class="flex items-center space-x-2">
						<div class="h-4.5 w-4.5 bg-brand-base"></div>
						<p class="text-xs font-light">
							{{ $t('statistics.offline-turnover') }}
						</p>
					</div>
				</div>
				<div class="!mt-20 w-full overflow-hidden">
					<div
						class="w-[calc(100%+40px)] md:w-[calc(100%+100px)] xl:w-[calc(100%+70px)] 2xl:w-[calc(100%+100px)]">
						<WeeklyTurnoverChart :payment-type="paymentType" :statistics="statistics" />
					</div>
				</div>

				<h2 class="mb-8 mt-7">
					{{ $t('statistics.reserved-hours-statistics') }}
				</h2>
				<div class="flex w-full space-x-2">
					<div class="grid w-full grid-cols-1 gap-x-5.5 gap-y-4 md:grid-cols-4 2xl:grid-cols-6">
						<div
							v-for="(day, index) in statistics.offline"
							class="relative h-18 w-full overflow-hidden rounded-md bg-brand-base pl-6">
							<div class="absolute -top-6 left-0">
								<svg
									class="-ml-10 opacity-40"
									fill="none"
									height="135"
									viewBox="0 0 115 135"
									width="115"
									xmlns="http://www.w3.org/2000/svg">
									<ellipse
										cx="49.9657"
										cy="59.5"
										fill="url(#paint0_linear_1630_15505)"
										rx="64.587"
										ry="82.5"
										transform="rotate(-180 49.9657 59.5)" />
									<defs>
										<linearGradient
											id="paint0_linear_1630_15505"
											gradientUnits="userSpaceOnUse"
											x1="49.9657"
											x2="49.9657"
											y1="-23"
											y2="142">
											<stop stop-color="white" />
											<stop offset="1" stop-color="white" stop-opacity="0" />
										</linearGradient>
									</defs>
								</svg>
							</div>
							<div class="flex h-full w-full flex-wrap items-center text-white">
								<p v-if="paymentType === 1" class="w-full text-2xl font-semibold">
                  {{ Math.round(countDaysOfWeekInRange(index) ? (statistics.online[index]['hours'] / countDaysOfWeekInRange(index)) : 0) }}
								</p>
								<p v-else-if="paymentType === 2" class="w-full text-2xl font-semibold">
                  {{ Math.round(countDaysOfWeekInRange(index) ? (statistics.offline[index]['hours'] / countDaysOfWeekInRange(index)) : 0) }}
								</p>
								<p v-else class="w-full text-2xl font-semibold">
                  {{ Math.round(countDaysOfWeekInRange(index) ? ((statistics.online[index]['hours'] / countDaysOfWeekInRange(index)) + (statistics.offline[index]['hours'] / countDaysOfWeekInRange(index))) : 0) }}
								</p>
								<p class="-mt-6 w-full text-base font-medium capitalize">
									{{ $t(`main.week-day.${index}`) }}
								</p>
							</div>
						</div>
					</div>
				</div>

				<div class="flex w-full justify-end space-x-11 pt-8">
					<div v-if="paymentType !== 2" class="flex items-center space-x-2">
						<div class="h-4.5 w-4.5 bg-brand-dark"></div>
						<p class="text-xs font-light">
							{{ $t('statistics.online-turnover') }}
						</p>
					</div>
					<div v-if="paymentType !== 1" class="flex items-center space-x-2">
						<div class="h-4.5 w-4.5 bg-brand-base"></div>
						<p class="text-xs font-light">
							{{ $t('statistics.offline-turnover') }}
						</p>
					</div>
				</div>

				<div
					v-if="
						!game.features.find((feature) => feature.type === 'fixed_reservation_duration') &&
						!game.features.find((feature) => feature.type === 'full_day_reservations')
					"
					class="!mt-20 w-full overflow-hidden">
					<div
						class="w-[calc(100%+40px)] md:w-[calc(100%+100px)] xl:w-[calc(100%+70px)] 2xl:w-[calc(100%+100px)]">
						<WeeklyHoursChart :payment-type="paymentType" :statistics="statistics" />
					</div>
				</div>
			</div>
		</div>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import WideHelper from '@/Components/Dashboard/Help/WideHelper.vue';
import { computed, Ref, ref, watch } from 'vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import RangeDatepicker from '@/Components/Dashboard/RangeDatepicker.vue';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import { router } from '@inertiajs/vue3';
import ExportDropdown from '@/Components/Dashboard/ExportDropdown.vue';
import { Game, SelectOption } from '@/Types/models';
import GameFilter from '@/Components/Dashboard/GameFilter.vue';
import { useDateRangeFilter } from '@/Composables/useDateRangeFilter';
import { useQueryString } from '@/Composables/useQueryString';
import { useNumber } from '@/Composables/useNumber';
import WeeklyTurnoverChart from '@/Pages/Club/Statistics/Charts/WeeklyTurnoverChart.vue';
import WeeklyHoursChart from '@/Pages/Club/Statistics/Charts/WeeklyHoursChart.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { usePage } from '@inertiajs/vue3';
import { wTrans } from 'laravel-vue-i18n';

interface chartDataEntry {
	weekDay: number;
	amount: number;
	count: number;
	hours: number;
}

interface chartData {
	[date: string]: chartDataEntry;
}

const props = defineProps<{
	game: Game;
	statistics: {
		online: chartData;
		offline: chartData;
	};
}>();

const { paymentTypeOptions, fromOptions, toOptions } = useSelectOptions();
const { dateRange } = useDateRangeFilter(null);
const { queryArray, queryUrl, queryValue } = useQueryString();
const { formatAmount } = useNumber();

const currentQueryArray = computed<any>(() => {
	return queryArray(window.location.search);
});

const statusOptions = ref<SelectOption[]>([
	{ code: 1, label: wTrans('statistics.paid') },
	{ code: 2, label: wTrans('statistics.unpaid') },
	{ code: 3, label: wTrans('statistics.paid-and-unpaid') },
]);
const status = ref<number>(parseInt(queryValue(window.location.search, `filters[status]`)));

const paymentType = ref<number>(parseInt(queryValue(window.location.search, `filters[paymentType]`)));

const fromTimeFilter = ref<string>(queryValue(window.location.search, `filters[timeRange][from]`));

const toTimeFilter = ref<string>(queryValue(window.location.search, `filters[timeRange][to]`));

function createFilterWatcher<T>(key: string, refValue: Ref<T>) {
	watch(refValue, async () => {
		const currentQueryArray: Record<string, string> = queryArray(window.location.search);
		currentQueryArray[key] = String(refValue.value);
		router.visit(queryUrl(currentQueryArray), { preserveScroll: true });
	});
}

function countDaysOfWeekInRange(dayOfWeek: number): number {
  let start: Date = new Date(dateRange.value[0].format('YYYY-MM-DD'));
  let end: Date = new Date(dateRange.value[1].format('YYYY-MM-DD'));

  let adjustedDayOfWeek: number = dayOfWeek % 7;

  start.setDate(start.getDate() + (adjustedDayOfWeek - start.getDay() + 7) % 7);

  let count: number = 0;
  while (start <= end) {
    if (start.getDay() === adjustedDayOfWeek) {
      count++;
    }
    start.setDate(start.getDate() + 7);
  }
  return count;
}

createFilterWatcher<number>(`filters[paymentType]`, paymentType);
createFilterWatcher<string>(`filters[timeRange][from]`, fromTimeFilter);
createFilterWatcher<string>(`filters[timeRange][to]`, toTimeFilter);
createFilterWatcher<number>(`filters[status]`, status);
</script>

<style scoped>
.dp__main {
	@apply mb-0;
}

.filters-right:deep(.vs__dropdown-toggle) {
	@apply opacity-70;
}

.filters-right:deep(.vs__selected) {
	@apply !pr-0;
}
</style>
