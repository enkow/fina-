<template>
	<Line id="chart" ref="chartRef" :data="data" :options="options" />
</template>

<script lang="ts" setup>
import {
	CategoryScale,
	Chart as ChartJS,
	Filler,
	Legend,
	LinearScale,
	LineElement,
	PointElement,
	Title,
	Tooltip,
} from 'chart.js';
import { Line } from 'vue-chartjs';
import { capitalize, ref } from 'vue';
import { useChart } from '@/Composables/useChart';
import { usePage } from '@inertiajs/vue3';
import { useQueryString } from '@/Composables/useQueryString';

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
	paymentType: number;
	statistics: {
		online: chartData;
		offline: chartData;
	};
}>();
const { queryValue } = useQueryString();

const labels: string[] = Object.entries(props.statistics.offline)
	.map(([date, stats]) =>
		capitalize(usePage().props.generalTranslations['week-day'][stats['weekDay'].toString()]),
	)
	.concat('');

const offlineData: number[] = Object.entries(props.statistics.offline).map(([date, stats]) =>
	stats.hours > 0 ? stats.hours / Math.max(countWeekDaysInRange(stats.weekDay), 1) : 0,
);
const onlineData: number[] = Object.entries(props.statistics.online).map(([date, stats]) =>
	stats.hours > 0 ? stats.hours / Math.max(countWeekDaysInRange(stats.weekDay), 1) : 0,
);

const offlineHours: number[] = Object.entries(props.statistics.offline).map(([date, stats]) => stats.hours);

const onlineHours: number[] = Object.entries(props.statistics.online).map(([date, stats]) => stats.hours);

let { options, defaultDatasetValues } = useChart({
	valuePostfix: usePage().props.translations['main.hours-postfix'],

	tooltipLabelCallback: function (context): string | string[] {
		let label = context.dataset.label || '';
		if (context.parsed.y !== null) {
			label += context.parsed.y.toFixed(2);
		}
		if (context.datasetIndex === 0 && props.paymentType === 3) {
			return [
				usePage().props.translations['statistics.tooltip-hours'] +
					(
						(parseFloat(offlineHours[context.dataIndex]) + parseFloat(onlineHours[context.dataIndex])) /
						Math.max(countWeekDaysInRange(context.dataIndex + 1), 1)
					).toFixed(2),
				`${label}`,
			];
		}
		return [`${label}`];
	},
	labels: labels,
});

const datasets: Object[] = [];

if ([1, 3].includes(props.paymentType)) {
	datasets.push(
		Object.assign({}, defaultDatasetValues, {
			label: usePage().props.translations['statistics.tooltip-online-average-hours'],
			data: onlineData,
		}),
	);
}

if ([2, 3].includes(props.paymentType)) {
	datasets.push(
		Object.assign({}, defaultDatasetValues, {
			label: usePage().props.translations['statistics.tooltip-offline-average-hours'],
			backgroundColor: (ctx: any) => {
				const ctxEl = ctx.chart.ctx;
				let gradient = ctxEl.createLinearGradient(0, 150, 0, 550);
				gradient.addColorStop(0, '#129D97');
				gradient.addColorStop(1, 'rgba(18, 157, 150,0.3)');
				return gradient;
			},
			data: offlineData,
		}),
	);
}

const data = ref<Object>({
	labels: labels,
	datasets: datasets,
});

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

function countWeekDaysInRange(weekDay: number): number {
	const startDate = new Date(queryValue(window.location.search, 'filters[startRange][from]'));
	const endDate = new Date(queryValue(window.location.search, 'filters[startRange][to]'));
	let count = 0;
	weekDay = weekDay % 7;
	for (let day = startDate; day <= endDate; day.setDate(day.getDate() + 1)) {
		if (day.getDay() === weekDay) {
			count++;
		}
	}
	return count;
}
</script>
