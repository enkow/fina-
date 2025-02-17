<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.games.statistics.main', { game: game }),
				label: $t('statistics.plural'),
			},
		]">
		<div class="flex w-full flex-wrap px-3 py-8 xl:px-10">
			<div class="flex w-full flex-wrap justify-between xl:w-1/2 xl:pr-3">
				<div class="filters mb-3 grid w-full gap-x-3 md:grid-cols-2">
					<RangeDatepicker
						v-model="dateRange"
						:input-classes="['opacity-60']"
						input-with-icon
						position="left" />
					<SimpleSelect
						v-model="setFilter"
						:options="setsOptions"
						:placeholder="$t('statistics.choose-set')"
						label="label" />
				</div>
				<div class="flex w-full flex-wrap space-y-2 rounded-md bg-white px-8 py-5 shadow-sm md:space-y-5">
					<h2 class="w-full text-base">
						{{ $t('statistics.sales-in-selected-period') }}
					</h2>
					<div class="grid w-full gap-y-4 md:grid-cols-2">
						<div class="flex items-end space-x-2">
							<p class="text-4xl font-semibold">{{ statistics.count }}</p>
							<p class="text-base font-normal">x {{ $t('set.singular') }}</p>
						</div>
						<div class="flex items-end space-x-2">
							<p class="text-4xl font-semibold">
								{{ formatAmount(statistics.turnover) }}
							</p>
							<p class="text-base font-normal">
								{{ $t('statistics.revenue-amount') }}
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="hidden w-1/2 xl:block xl:pl-3">
				<div class="flex h-full w-full space-x-2">
					<WideHelper class="h-full" float="right">
						<template #mascot>
							<Mascot :type="16" />
						</template>
						{{ usePage().props.generalTranslations['help-statistics-content'] }}
					</WideHelper>
				</div>
			</div>
			<div class="!mt-30 w-full overflow-hidden">
				<div class="w-[calc(100%+40px)] md:w-[calc(100%+100px)] xl:w-[calc(100%+140px)]">
					<SetsTurnoverChart :statistics="statistics" />
				</div>
			</div>
		</div>
	</PanelLayout>
</template>

<style scoped>
.filters:deep(.vs__dropdown-toggle) {
	@apply opacity-70;
}
</style>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { Ref, ref, watch } from 'vue';
import RangeDatepicker from '@/Components/Dashboard/RangeDatepicker.vue';
import { router, usePage } from '@inertiajs/vue3';
import { Game, SetModel } from '@/Types/models';
import { useDateRangeFilter } from '@/Composables/useDateRangeFilter';
import { useQueryString } from '@/Composables/useQueryString';
import { useNumber } from '@/Composables/useNumber';
import WideHelper from '@/Components/Dashboard/Help/WideHelper.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { wTrans } from 'laravel-vue-i18n';
import SetsTurnoverChart from '@/Pages/Club/Statistics/Charts/SetsTurnoverChart.vue';

const props = defineProps<{
	game: Game;
	statistics: {
		turnover: number;
		count: number;
		chart: Array<{
			date: string;
			amount: number;
		}>;
	};
	sets: SetModel[];
}>();

const { queryArray, queryUrl, queryValue } = useQueryString();
const { dateRange } = useDateRangeFilter(null);
const { formatAmount } = useNumber();

const setFilter = ref<null | number>(parseInt(queryValue(window.location.search, `filters[set]`) ?? 0));
const setsOptions = ref<
	{
		label: Ref<string>;
		code: number;
	}[]
>([{ label: wTrans('statistics.all-sets'), code: 0 }]);
props.sets.forEach((item) => {
	setsOptions.value.push({ label: item.name, code: item.id });
});

function createFilterWatcher<T>(key: string, refValue: Ref<T>) {
	watch(refValue, async () => {
		const currentQueryArray: Record<string, string> = queryArray(window.location.search);
		currentQueryArray[key] = String(refValue.value);
		router.visit(queryUrl(currentQueryArray));
	});
}

createFilterWatcher<number | null>(`filters[set]`, setFilter);
</script>
