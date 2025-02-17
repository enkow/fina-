<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('admin.statistics.index'),
				label: $t('statistics.plural'),
			},
		]">
		<div class="flex w-full flex-wrap px-3 py-8 xl:px-10">
			<div class="hidden w-full grid-cols-6 gap-x-3 md:grid 2xl:hidden">
				<div class="col-span-2">
					<RangeDatepicker
						v-model="dateRange"
						:inputClasses="['opacity-60']"
						input-with-icon
						position="left" />
				</div>
				<div class="col-span-2">
					<ExportDropdown
						v-model="exportType"
						:custom-download-options="[{ code: 'xlsx', label: 'XLSX' }]"
						class="!w-full" />
				</div>
				<div class="col-span-2">
					<SimpleSelect
						v-model="status"
						:options="statusOptions"
						class="margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white !w-full"
						placeholder="Typ" />
				</div>
				<div class="col-span-2">
					<SimpleSelect
						v-model="paymentType"
						:options="paymentTypeOptions"
						class="min-w-50 margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white"
						placeholder="Typ" />
				</div>
				<div class="col-span-2">
					<SimpleSelect
						v-model="game"
						:options="gamesOptions"
						class="game-select margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white"
						placeholder="Typ" />
				</div>
				<div class="col-span-2">
					<SimpleSelect
						v-model="club"
						:options="clubs"
						class="min-w-50 margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white"
						placeholder="Typ" />
				</div>
			</div>
			<div class="block w-full space-y-3 md:hidden">
				<ExportDropdown
					v-model="exportType"
					:custom-download-options="[{ code: 'xlsx', label: 'XLSX' }]"
					class="!w-full" />
				<RangeDatepicker v-model="dateRange" :inputClasses="['opacity-60']" input-with-icon position="left" />
				<SimpleSelect
					v-model="game"
					:options="gamesOptions"
					class="game-select margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white"
					placeholder="Typ" />
				<SimpleSelect
					v-model="status"
					:options="statusOptions"
					class="margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white !w-full"
					placeholder="Typ" />
				<SimpleSelect
					v-model="paymentType"
					:options="paymentTypeOptions"
					class="min-w-50 margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white"
					placeholder="Typ" />
				<SimpleSelect
					v-model="club"
					:options="clubs"
					class="min-w-50 margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white"
					placeholder="Typ" />
			</div>
			<div class="hidden w-full justify-between 2xl:flex">
				<div>
					<RangeDatepicker
						v-model="dateRange"
						:inputClasses="['opacity-60']"
						class="!w-66"
						input-with-icon
						position="left" />
				</div>
				<div class="flex flex-wrap space-x-1.5">
					<ExportDropdown v-model="exportType" :custom-download-options="[{ code: 'xlsx', label: 'XLSX' }]" />
					<SimpleSelect
						v-model="game"
						:options="gamesOptions"
						class="game-select margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white"
						placeholder="Typ" />
					<SimpleSelect
						v-model="status"
						:options="statusOptions"
						class="margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white !w-66"
						placeholder="Typ" />
					<SimpleSelect
						v-model="paymentType"
						:options="paymentTypeOptions"
						class="margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white !w-60"
						placeholder="Typ" />
					<SimpleSelect
						v-model="club"
						:options="clubs"
						class="margins-sm toggle-center toggle-info-light toggle-hover-info-base toggle-border-none dropdown-separate open-indicator-info-dark open-indicator-hover-white w-60"
						placeholder="Typ" />
				</div>
			</div>
			<div class="mt-7 flex w-full space-x-2">
				<div
					:class="{
						'place-content-start': fixedReservationDurationValue === 24,
					}"
					class="xl:w-12/12 grid w-full grid-cols-1 gap-x-5 gap-y-5 md:grid-cols-3">
					<StatisticContainer v-if="status === 1">
						<template #icon>
							<HandWithCoins />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ $t('statistics.turnover') }}
								{{
									formatAmount(
										(paymentType !== 2 ? statistics.reservationsTurnoverPaid.online : 0) +
											(paymentType !== 1 ? statistics.reservationsTurnoverPaid.offline : 0),
									)
								}}
							</h2>
							<div v-if="paymentType === 3">
								online -
								{{ formatAmount(statistics.reservationsTurnoverPaid.online) }}
								<br />
								offline -
								{{ formatAmount(statistics.reservationsTurnoverPaid.offline) }}
							</div>
						</div>
					</StatisticContainer>

					<StatisticContainer v-if="!(fixedReservationDurationValue === 24) && status === 1">
						<template #icon>
							<ClockIcon class="h-10 w-10" />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ $t('statistics.hours') }}
								{{
									formatHoursAndMinutes(
										(paymentType !== 2 ? statistics.reservationsHoursPaid.online : 0) +
											(paymentType !== 1 ? statistics.reservationsHoursPaid.offline : 0),
									)
								}}
							</h2>
							<div v-if="paymentType === 3">
								online - {{ formatHoursAndMinutes(statistics.reservationsHoursPaid.online) }}
								<br />
								offline - {{ formatHoursAndMinutes(statistics.reservationsHoursPaid.offline) }}
							</div>
						</div>
					</StatisticContainer>

					<StatisticContainer v-if="status === 1">
						<template #icon>
							<HashtagInRectangleIcon />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ $t('statistics.count') }}
								{{
									(paymentType !== 2 ? statistics.reservationsCountPaid.online : 0) +
									(paymentType !== 1 ? statistics.reservationsCountPaid.offline : 0)
								}}
							</h2>
							<div v-if="paymentType === 3">
								online - {{ statistics.reservationsCountPaid.online }}
								<br />
								offline - {{ statistics.reservationsCountPaid.offline }}
							</div>
						</div>
					</StatisticContainer>
					<StatisticContainer v-if="status === 2">
						<template #icon>
							<HandWithCoins />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ $t('statistics.turnover') }}
								{{
									formatAmount(
										(paymentType !== 2 ? statistics.reservationsTurnoverUnpaid.online : 0) +
											(paymentType !== 1 ? statistics.reservationsTurnoverUnpaid.offline : 0),
									)
								}}
							</h2>
							<div v-if="paymentType === 3">
								online -
								{{ formatAmount(statistics.reservationsTurnoverUnpaid.online) }}
								<br />
								offline -
								{{ formatAmount(statistics.reservationsTurnoverUnpaid.offline) }}
							</div>
						</div>
					</StatisticContainer>

					<StatisticContainer v-if="!(fixedReservationDurationValue === 24) && status === 2">
						<template #icon>
							<ClockIcon class="h-10 w-10" />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ $t('statistics.hours') }}
								{{
									formatHoursAndMinutes(
										(paymentType !== 2 ? statistics.reservationsHoursUnpaid.online : 0) +
											(paymentType !== 1 ? statistics.reservationsHoursUnpaid.offline : 0),
									)
								}}
							</h2>
							<div v-if="paymentType === 3">
								online - {{ formatHoursAndMinutes(statistics.reservationsHoursUnpaid.online) }}
								<br />
								offline - {{ formatHoursAndMinutes(statistics.reservationsHoursUnpaid.offline) }}
							</div>
						</div>
					</StatisticContainer>

					<StatisticContainer v-if="status === 2">
						<template #icon>
							<HashtagInRectangleIcon />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ $t('statistics.count') }}
								{{
									(paymentType !== 2 ? statistics.reservationsCountUnpaid.online : 0) +
									(paymentType !== 1 ? statistics.reservationsCountUnpaid.offline : 0)
								}}
							</h2>
							<div v-if="paymentType === 3">
								online - {{ statistics.reservationsCountUnpaid.online }}
								<br />
								offline - {{ statistics.reservationsCountUnpaid.offline }}
							</div>
						</div>
					</StatisticContainer>
					<StatisticContainer v-if="status === 3">
						<template #icon>
							<HandWithCoins />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ $t('statistics.turnover') }}
								{{
									formatAmount(
										(paymentType !== 2 ? statistics.reservationsTurnoverUnpaid.online : 0) +
											(paymentType !== 1 ? statistics.reservationsTurnoverUnpaid.offline : 0) +
											((paymentType !== 2 ? statistics.reservationsTurnoverPaid.online : 0) +
												(paymentType !== 1 ? statistics.reservationsTurnoverPaid.offline : 0)),
									)
								}}
							</h2>
							<div v-if="paymentType === 3">
								online -
								{{
									formatAmount(
										statistics.reservationsTurnoverUnpaid.online + statistics.reservationsTurnoverPaid.online,
									)
								}}
								<br />
								offline -
								{{
									formatAmount(
										statistics.reservationsTurnoverUnpaid.offline +
											statistics.reservationsTurnoverPaid.offline,
									)
								}}
							</div>
						</div>
					</StatisticContainer>

					<StatisticContainer v-if="!(fixedReservationDurationValue === 24) && status === 3">
						<template #icon>
							<ClockIcon class="h-10 w-10" />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ $t('statistics.hours') }}
								{{
									formatHoursAndMinutes(
										(paymentType !== 2 ? statistics.reservationsHoursUnpaid.online : 0) +
											(paymentType !== 1 ? statistics.reservationsHoursUnpaid.offline : 0) +
											((paymentType !== 2 ? statistics.reservationsHoursPaid.online : 0) +
												(paymentType !== 1 ? statistics.reservationsHoursPaid.offline : 0)),
									)
								}}
							</h2>
							<div v-if="paymentType === 3">
								online -
								{{
									formatHoursAndMinutes(
										statistics.reservationsHoursUnpaid.online + statistics.reservationsHoursPaid.online,
									)
								}}
								<br />
								offline -
								{{
									formatHoursAndMinutes(
										statistics.reservationsHoursUnpaid.offline + statistics.reservationsHoursPaid.offline,
									)
								}}
							</div>
						</div>
					</StatisticContainer>

					<StatisticContainer v-if="status === 3">
						<template #icon>
							<HashtagInRectangleIcon />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ $t('statistics.count') }}
								{{
									(paymentType !== 2 ? statistics.reservationsCountUnpaid.online : 0) +
									(paymentType !== 1 ? statistics.reservationsCountUnpaid.offline : 0) +
									((paymentType !== 2 ? statistics.reservationsCountPaid.online : 0) +
										(paymentType !== 1 ? statistics.reservationsCountPaid.offline : 0))
								}}
							</h2>
							<div v-if="paymentType === 3">
								online -
								{{ statistics.reservationsCountUnpaid.online + statistics.reservationsCountPaid.online }}
								<br />
								offline -
								{{ statistics.reservationsCountUnpaid.offline + statistics.reservationsCountPaid.offline }}
							</div>
						</div>
					</StatisticContainer>

					<StatisticContainer>
						<template #icon>
							<HandWithCoinsRecuringIcon />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h3 class="text-[15px] font-semibold capitalize">
								{{ $t('statistics.average-male') }}
							</h3>
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ $t('statistics.turnover') }}
								{{
									formatAmount(
										(paymentType !== 1 ? statistics.averageReservationsTurnover.online : 0) +
											(paymentType !== 2 ? statistics.averageReservationsTurnover.offline : 0),
									)
								}}
							</h2>
							<div v-if="paymentType === 3">
								online -
								{{ formatAmount(statistics.averageReservationsTurnover.online) }}
								<br />
								offline -
								{{ formatAmount(statistics.averageReservationsTurnover.offline) }}
							</div>
						</div>
					</StatisticContainer>
					<StatisticContainer v-if="!(fixedReservationDurationValue === 24)">
						<template #icon>
							<ClockIcon class="h-10 w-10" />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h3 class="text-[15px] font-semibold capitalize">
								{{ $t('statistics.average-quantity') }}
							</h3>
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ $t('statistics.hours') }}
								{{
									formatHoursAndMinutes(
										(paymentType !== 1 ? statistics.averageReservationsHours.online : 0) +
											(paymentType !== 2 ? statistics.averageReservationsHours.offline : 0),
										2,
									)
								}}
							</h2>
							<div v-if="paymentType === 3">
								online -
								{{ formatHoursAndMinutes(statistics.averageReservationsHours.online, 2) }}
								<br />
								offline -
								{{ formatHoursAndMinutes(statistics.averageReservationsHours.offline, 2) }}
							</div>
						</div>
					</StatisticContainer>

					<StatisticContainer>
						<template #icon>
							<HashtagInRectangleIcon />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h3 class="text-[15px] font-semibold capitalize">
								{{ $t('statistics.average-female') }}
							</h3>
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ capitalize($t('main.quantity')) }}
								{{
									formattedFloat(
										(paymentType !== 1 ? statistics.averageReservationsCount.online : 0) +
											(paymentType !== 2 ? statistics.averageReservationsCount.offline : 0),
										2,
									).replace('.', ',')
								}}
							</h2>
							<div v-if="paymentType === 3">
								online -
								{{ formattedFloat(statistics.averageReservationsCount.online, 2).replace('.', ',') }}
								<br />
								offline -
								{{ formattedFloat(statistics.averageReservationsCount.offline, 2).replace('.', ',') }}
							</div>
						</div>
					</StatisticContainer>

					<StatisticContainer>
						<template #icon>
							<PeopleUnfilledIcon class="h-10 w-10" />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ statistics.allCustomersCount }}
							</h2>
							{{ $t('statistics.all-customers') }}
						</div>
					</StatisticContainer>

					<StatisticContainer v-if="!(fixedReservationDurationValue === 24)">
						<template #icon>
							<PersonWithPlusIcon />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ statistics.newCustomersCount }}
							</h2>
							{{ $t('statistics.new-customers') }}
						</div>
					</StatisticContainer>

					<StatisticContainer>
						<template #icon>
							<PersonRotatingIcon />
						</template>
						<div class="flex h-full flex-col justify-center">
							<h2 class="-mt-1 mb-0 text-lg font-semibold 2xl:text-xl">
								{{ statistics.allCustomersCount - statistics.newCustomersCount }}
							</h2>
							{{ $t('statistics.returning-customers') }}
						</div>
					</StatisticContainer>
				</div>
			</div>
			<div class="mt-10 flex w-full space-x-11 pt-4 md:hidden lg:pt-8">
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
			<div class="items-top mb-10 mt-3 flex w-full justify-between md:mt-15">
				<div class="chart-select relative pb-8 md:pb-0">
					<div class="absolute left-0 top-0.75" :class="[chartTypeLabelWidthClass]">
						<h2 class="text-2xl font-medium">
							{{ chartTypeLabel }}
						</h2>
					</div>
					<SimpleSelect
						v-model="chartType"
						:header="chartTypeLabel"
						:options="chartTypes"
						class="absolute left-0 top-0"
						:class="[chartTypeSelectWidthClass]" />
				</div>
				<div class="flex hidden w-full justify-end space-x-11 pt-4 md:flex lg:pt-8">
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
			</div>
			<div class="w-full overflow-hidden">
				<div class="w-[calc(100%+40px)] md:w-[calc(100%+100px)] xl:w-[calc(100%+140px)]">
					<MainTurnoverChart
						v-if="chartType === 'turnover'"
						:payment-type="paymentType"
						:statistics="statistics" />
					<MainHoursChart v-if="chartType === 'hours'" :payment-type="paymentType" :statistics="statistics" />
					<MainCountChart v-if="chartType === 'count'" :payment-type="paymentType" :statistics="statistics" />
				</div>
			</div>
		</div>
		<div class="statistics-details mx-3 space-y-5 xs:mx-5 md:mx-15">
			<AccordionTab class="statistics-details__container" :is-expanded="true">
				<template #title>{{ $t('statistics.turnover') }}</template>
				<div class="flex grid w-full gap-x-6.5 gap-y-3.5 md:grid-cols-2">
					<div>
						<AccordionTab class="statistics-details__single">
							<template #title>
								{{ $t('statistics.reservation-turnover') }}
							</template>
							<div class="space-y-3">
								<div
									v-for="item in statistics.detailed.turnover.general"
									:class="{ '!font-bold': item.label === null }"
									class="flex w-full justify-between text-sm font-medium">
									<div>{{ item.label ?? $t('main.sum').toUpperCase() }}</div>
									<div>{{ formatAmount(item.value) }}</div>
								</div>
							</div>
						</AccordionTab>
					</div>
					<div>
						<AccordionTab class="statistics-details__single">
							<template #title>
								{{ $t('statistics.canceled-reservation-turnover') }}
							</template>
							<div class="space-y-3">
								<div
									v-for="item in statistics.detailed.turnover.canceled"
									:class="{ '!font-bold': item.label === null }"
									class="flex w-full justify-between text-sm font-medium">
									<div>{{ item.label ?? $t('main.sum').toUpperCase() }}</div>
									<div>{{ formatAmount(item.value) }}</div>
								</div>
							</div>
						</AccordionTab>
					</div>
				</div>
			</AccordionTab>

			<AccordionTab class="statistics-details__container" :is-expanded="true">
				<template #title>{{ capitalize($t('main.hours')) }}</template>
				<div class="flex grid w-full gap-x-6.5 gap-y-3.5 md:grid-cols-2">
					<div>
						<AccordionTab class="statistics-details__single">
							<template #title>
								{{ $t('statistics.reservation-hours') }}
							</template>
							<div class="space-y-3">
								<div
									v-for="item in statistics.detailed.hours.general"
									:class="{ '!font-bold': item.label === null }"
									class="flex w-full justify-between text-sm font-medium">
									<div>{{ item.label ?? $t('main.sum').toUpperCase() }}</div>
									<div>
										{{ formatHoursAndMinutes(item.value) }}
									</div>
								</div>
							</div>
						</AccordionTab>
					</div>
					<div>
						<AccordionTab class="statistics-details__single">
							<template #title>
								{{ $t('statistics.canceled-reservation-hours') }}
							</template>
							<div class="space-y-3">
								<div
									v-for="item in statistics.detailed.hours.canceled"
									:class="{ '!font-bold': item.label === null }"
									class="flex w-full justify-between text-sm font-medium">
									<div>{{ item.label ?? $t('main.sum').toUpperCase() }}</div>
									<div>{{ formatHoursAndMinutes(item.value) }}</div>
								</div>
							</div>
						</AccordionTab>
					</div>
				</div>
			</AccordionTab>

			<AccordionTab class="statistics-details__container" :is-expanded="true">
				<template #title>{{ capitalize($t('main.quantity')) }}</template>
				<div class="flex grid w-full gap-x-6.5 gap-y-3.5 md:grid-cols-2">
					<div>
						<AccordionTab class="statistics-details__single">
							<template #title>
								{{ $t('statistics.reservations-count') }}
							</template>
							<div class="space-y-3">
								<div
									v-for="item in statistics.detailed.count.general"
									:class="{ '!font-bold': item.label === null }"
									class="flex w-full justify-between text-sm font-medium">
									<div>{{ item.label ?? $t('main.sum').toUpperCase() }}</div>
									<div>{{ item.value }}</div>
								</div>
							</div>
						</AccordionTab>
					</div>
					<div>
						<AccordionTab class="statistics-details__single">
							<template #title>
								{{ $t('statistics.canceled-reservations-count') }}
							</template>
							<div class="space-y-3">
								<div
									v-for="item in statistics.detailed.count.canceled"
									:class="{ '!font-bold': item.label === null }"
									class="flex w-full justify-between text-sm font-medium">
									<div>{{ item.label ?? $t('main.sum').toUpperCase() }}</div>
									<div>{{ item.value }}</div>
								</div>
							</div>
						</AccordionTab>
					</div>
				</div>
			</AccordionTab>
		</div>
	</PanelLayout>
</template>

<style scoped>
.chart-select:deep(.vs__dropdown-toggle) {
	@apply border-0;
}

.chart-select:deep(.vs__selected) {
	@apply hidden;
}

.chart-select:deep(.vs__dropdown-menu) {
	@apply w-80 rounded-md border-t border-solid border-t-brand-base;
}

.game-select {
	min-width: v-bind(gameSelectMinWidth);
}
</style>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { capitalize, computed, ComputedRef, onMounted, Ref, ref, watch } from 'vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import RangeDatepicker from '@/Components/Dashboard/RangeDatepicker.vue';
import StatisticContainer from '@/Components/Dashboard/StatisticContainer.vue';
import ClockIcon from '@/Components/Dashboard/Icons/ClockIcon.vue';
import MascotHelper from '@/Components/Dashboard/Help/MascotHelper.vue';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import { router, usePage } from '@inertiajs/vue3';
import ExportDropdown from '@/Components/Dashboard/ExportDropdown.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { Club, DiscountCode, Game, SelectOption, SpecialOffer } from '@/Types/models';
import GameFilter from '@/Components/Dashboard/GameFilter.vue';
import { useDateRangeFilter } from '@/Composables/useDateRangeFilter';
import { useQueryString } from '@/Composables/useQueryString';
import PeopleUnfilledIcon from '@/Components/Dashboard/Icons/PeopleUnfilledIcon.vue';
import { useNumber } from '@/Composables/useNumber';
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import HandWithCoins from '@/Components/Dashboard/Icons/HandWithCoinsIcon.vue';
import HashtagInRectangleIcon from '@/Components/Dashboard/Icons/HashtagInRectangleIcon.vue';
import HandWithCoinsRecuringIcon from '@/Components/Dashboard/Icons/HandWithCoinsRecuringIcon.vue';
import PersonWithPlusIcon from '@/Components/Dashboard/Icons/PersonWithPlusIcon.vue';
import PersonRotatingIcon from '@/Components/Dashboard/Icons/PersonRotatingIcon.vue';
import MainTurnoverChart from '@/Pages/Club/Statistics/Charts/MainTurnoverChart.vue';
import { wTrans } from 'laravel-vue-i18n';
import MainHoursChart from '@/Pages/Club/Statistics/Charts/MainHoursChart.vue';
import MainCountChart from '@/Pages/Club/Statistics/Charts/MainCountChart.vue';
import { useExport } from '@/Composables/useExport';

interface StatisticsDetailsCategoryTypeValue {
	label: string;
	value: number | string;
}

interface StatisticsDetailsCategory {
	general: Array<StatisticsDetailsCategoryTypeValue>;
	canceled: Array<StatisticsDetailsCategoryTypeValue>;
}

interface paymentTypeGrouped {
	offline: number;
	online: number;
}

interface chartDataEntry {
	amount: number;
	count: number;
	hours: number;
}

interface chartData {
	[date: string]: chartDataEntry;
}

const props = defineProps<{
	statistics: {
		reservationsTurnoverPaid: paymentTypeGrouped;
		reservationsTurnoverUnpaid: paymentTypeGrouped;
		reservationsHoursPaid: paymentTypeGrouped;
		reservationsHoursUnpaid: paymentTypeGrouped;
		reservationsCountPaid: paymentTypeGrouped;
		reservationsCountUnpaid: paymentTypeGrouped;
		averageReservationsTurnover: paymentTypeGrouped;
		averageReservationsHours: paymentTypeGrouped;
		averageReservationsCount: paymentTypeGrouped;
		allCustomersCount: number;
		newCustomersCount: number;
		detailed: {
			turnover: StatisticsDetailsCategory;
			hours: StatisticsDetailsCategory;
			count: StatisticsDetailsCategory;
		};
		chart: {
			online: chartData;
			offline: chartData;
		};
	};
	games: Array<Game>;
	clubs: Array<Club>;
}>();

console.log(props.statistics);

const { paymentTypeOptions } = useSelectOptions();
const { dateRange } = useDateRangeFilter(null);
const { queryArray, queryUrl, queryValue } = useQueryString();
const { formatAmount, formattedFloat } = useNumber();
const exportType = useExport('admin.statistics.export');

const currentQueryArray = computed<any>(() => {
	return queryArray(window.location.search);
});

const statusOptions = ref<SelectOption[]>([
	{ code: 1, label: wTrans('statistics.paid') },
	{ code: 2, label: wTrans('statistics.unpaid') },
	{ code: 3, label: wTrans('statistics.paid-and-unpaid') },
]);

// game filter
let longestGameNameLength: number = 0;
let gamesOptions = ref<SelectOption[]>([{ code: '0', label: 'Wszystkie gry' }]);
const game = ref<string>(queryValue(window.location.search, `filters[game]`) || '0');

for (const _game of props.games) {
	longestGameNameLength = Math.max(_game.name.length, longestGameNameLength);

	gamesOptions.value.push({
		code: `${_game.id || 0}`,
		label: _game.name,
	});
}

const gameSelectMinWidth = ref<string>('100px');
onMounted(() => {
	gameSelectMinWidth.value = `${longestGameNameLength * 10 + 50}px`;
});

// club filter
let clubs = ref<SelectOption[]>([{ code: '0', label: 'Wszystkie kluby' }]);
const club = ref<string>(queryValue(window.location.search, `filters[club]`) || '0');
let clubExist = false;

for (const _club of props.clubs) {
	clubs.value.push({
		code: `${_club.id || 0}`,
		label: _club.name,
	});

	if (club.value === _club.id?.toString()) {
		clubExist = true;
	}
}

if (!clubExist) {
	club.value = '0';
}

// statistics filters
const paymentType = ref<number>(parseInt(queryValue(window.location.search, `filters[paymentType]`)));
const status = ref<number>(parseInt(queryValue(window.location.search, `filters[status]`)));

function createFilterWatcher<T extends string | number>(key: string | number, refValue: Ref<T>) {
	watch(refValue, async () => {
		const currentQueryArray: Record<string, string> = queryArray(window.location.search);
		currentQueryArray[key] = String(refValue.value);
		router.visit(queryUrl(currentQueryArray), {
			preserveScroll: true,
		});
	});
}

createFilterWatcher<number>('filters[paymentType]', paymentType);
createFilterWatcher<string>('filters[club]', club);
createFilterWatcher<number>('filters[status]', status);
createFilterWatcher<number>('filters[game]', game);

// chart types management
let localePostfix = computed<string>(() => {
	let postfix = '';
	if (status.value === 1) {
		postfix = ' (' + wTrans('reservation.status.paid').value + ')';
	}
	if (status.value === 2) {
		postfix = ' (' + wTrans('reservation.status.unpaid').value + ')';
	}
	return postfix;
});
let allChartTypes = computed<SelectOption[]>(() => {
	return [
		{
			code: 'turnover',
			label: computed<string>(() => {
				return wTrans('statistics.reservation-turnover').value + localePostfix.value;
			}),
		},
		{
			code: 'hours',
			label: computed<string>(() => {
				return wTrans('statistics.reservation-hours').value + localePostfix.value;
			}),
		},
		{
			code: 'count',
			label: computed<string>(() => {
				return wTrans('statistics.reservation-count').value + localePostfix.value;
			}),
		},
	];
});
let chartTypes = ref<SelectOption[]>([]);
let chartType = ref<string>(queryValue(window.location.search, 'chartType'));
const chartTypeLabel: ComputedRef = computed(() => {
	return allChartTypes.value.find((item) => item.code === chartType.value)?.label.value;
});

const chartTypeLabelWidthClass = computed<string>(() => {
	if ([1, 2].includes(status.value)) {
		return 'w-100';
	}
	return 'w-72';
});

const chartTypeSelectWidthClass = computed<string>(() => {
	if ([1, 2].includes(status.value)) {
		return 'w-112';
	}
	return 'w-80';
});

function updateChartTypes(): void {
	chartTypes.value = allChartTypes.value.filter((item) => item.code !== chartType.value);
}

watch(chartType, async () => {
	updateChartTypes();
});
updateChartTypes();

// utilities
function formatHoursAndMinutes(value: number | string): string {
	if (typeof value === 'string') {
		value = parseInt(value);
	}

	const hours: number = Math.floor(value);
	const minutes: number = Math.round((value - hours) * 60);

	return `${hours}${wTrans('main.hours-postfix').value}${minutes ? ` ${minutes}min` : ''}`;
}

const fixedReservationDurationValue =
	parseInt(
		usePage().props.clubSettings[
			Object.keys(usePage().props.clubSettings).find(
				(settingKey) =>
					usePage().props.clubSettings[settingKey].feature?.type === 'fixed_reservation_duration',
			)
		]?.value,
	) ?? 0;
</script>
