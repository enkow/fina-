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
import { useNumber } from '@/Composables/useNumber';
import { useChart } from '@/Composables/useChart';
import { usePage } from '@inertiajs/vue3';
import {onMounted, onUnmounted, ref} from 'vue';

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
	turnover: number;
	count: number;
	hours: number;
}

interface chartData {
	[date: string]: chartDataEntry;
}

const props = defineProps<{
	paymentType: number;
	statistics: {
		reservationsTurnover: paymentTypeGrouped;
		reservationsHours: paymentTypeGrouped;
		reservationsCount: paymentTypeGrouped;
		averageReservationsTurnover: paymentTypeGrouped;
		averageReservationsHours: paymentTypeGrouped;
		averageReservationsCount: paymentTypeGrouped;
		allCustomersCount: number;
		newCustomersCount: number;
		returningCustomersCount: number;
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
}>();

const { formatAmount } = useNumber();

const labels: string[] = Object.keys(props.statistics.chart.online).concat(
	[...Array(Math.ceil(Object.keys(props.statistics.chart.online).length / 6)).keys()].map(String).fill(''),
);
const onlineData: number[] = Object.keys(props.statistics.chart.online).map(
	(key) => props.statistics.chart.online[key]['count'],
);
const offlineData: number[] = Object.keys(props.statistics.chart.offline).map(
	(key) => props.statistics.chart.offline[key]['count'],
);

let { options, defaultDatasetValues } = useChart({
  tooltipLabelCallback: function (context): string | string[] {
    let label = context.dataset.label || '';
    if (context.parsed.y !== null) {
      label += Math.round(context.parsed.y).toString();
    }
    if (context.datasetIndex === 0 && props.paymentType === 3) {
      return [
        usePage().props.translations['statistics.tooltip-count'] +
        Math.round(offlineData[context.dataIndex] + onlineData[context.dataIndex]).toString(),
        `${label}`,
      ];
    }
    return [`${label}`];
  },
  axisYCallback: function (value: string, index: number, ticks: number): string | string[] {
    let result = value;
    if (window.innerWidth > 700) {
      result += '     ';
    }
    return result;
  },
  labels: labels,
});

const datasets: Object[] = [];

if ([1, 3].includes(props.paymentType)) {
	datasets.push(
		Object.assign({}, defaultDatasetValues, {
			label: usePage().props.translations['statistics.tooltip-online-count'],
			data: onlineData,
		}),
	);
}

if ([2, 3].includes(props.paymentType)) {
	datasets.push(
		Object.assign({}, defaultDatasetValues, {
			label: usePage().props.translations['statistics.tooltip-offline-count'],
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

const data: Object = ref<{
	labels: {
		[key: string]: any;
	};
	datasets: Object[];
}>({
	labels: labels,
	datasets: datasets,
});

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);
</script>
