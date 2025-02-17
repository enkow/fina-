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
import { useChart } from '@/Composables/useChart';
import { usePage } from '@inertiajs/vue3';
import { useNumber } from '@/Composables/useNumber';

const props = defineProps<{
	statistics: {
		chart: Array<{
			date: string;
			amount: number;
		}>;
	};
}>();

const { formatAmount } = useNumber();

const labels: string[] = props.statistics.chart
	.map((item) => item.date)
	.concat([...Array(Math.ceil(Object.keys(props.statistics.chart).length / 6)).keys()].map(String).fill(''));
const rawDataTop: number[] = props.statistics.chart.map((item) => item.amount / 100);

let { options, defaultDatasetValues } = useChart({
	tooltipLabelCallback: function (context): string | string[] {
		let label = context.dataset.label || '';

		if (label) {
			label += ':              ';
		}
		if (context.parsed.y !== null) {
			label += formatAmount(context.parsed.y * 100);
		}
		return [`${label}`];
	},
	axisYCallback: function (value: string, index: number, ticks: number) {
		let result = formatAmount(value * 100, null, 0, 0);
		if (window.innerWidth > 700) {
			result += '     ';
		}
		return result;
	},
	labels: labels,
});

const data: Object = {
	labels: labels,
	datasets: [
		Object.assign({}, defaultDatasetValues, {
			label: usePage().props.translations['statistics.sets-turnover'],
			data: rawDataTop,
		}),
	],
};

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);
</script>
