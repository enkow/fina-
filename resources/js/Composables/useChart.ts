import { ChartType, TooltipItem } from 'chart.js';

export function useChart(
	data: {
		valuePostfix: string;
		tooltipLabelCallback?: (context: any) => string | string[];
		axisYCallback?: (context: any) => string | string[];
		labels?: string[];
	} = {
		valuePostfix: 'PLN',
		tooltipLabelCallback: undefined,
		axisYCallback: undefined,
		labels: undefined,
	},
) {
	let options: Object = {
		hover: { mode: null },
		responsive: true,
		aspectRatio: window.innerWidth > 1200 ? 3 : window.innerWidth > 400 ? 2 : 1,
		animation: navigator.userAgent.indexOf('Chrome') === -1,
		tooltips: {
			yPadding: -100,
		},
		plugins: {
			title: {
				display: false,
			},
			legend: {
				display: false,
			},
			tooltip: {
				enabled: true,
				callbacks: {
					label: data.tooltipLabelCallback
						? data.tooltipLabelCallback
						: function (context: TooltipItem<ChartType>) {
								let label = context.dataset.label || '';

								if (context.parsed.y !== null) {
									label += context.parsed.y + data.valuePostfix;
								}
								return label;
						  },
				},
				backgroundColor: 'white',
				bodyColor: '#000',
				bodySpacing: 8,
				titleColor: '#000',
				titleAlign: 'center',
				titleMarginBottom: 10,
				position: 'nearest',
				yAlign: 'bottom',
				yPadding: '100px',
				caretSize: 14,
				padding: {
					left: 18,
					right: 17,
					top: 12,
					bottom: 10,
				},
				displayColors: false,
				bodyFont: {
					size: 10,
					weight: 500,
				},
			},
		},
		interaction: {
			mode: 'index',
			intersect: false,
		},
		scales: {
			x: {
				title: {
					display: false,
					text: 'Month',
				},
				grid: {
					display: false,
				},
				border: {
					display: false,
				},
				ticks: {
					color: '#9AA1B3',
					padding: window.innerWidth > 700 ? 27 : 10,
					font: {
						family:
							'Poppins, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
						size: window.innerWidth > 700 ? 18 : 15,
						weight: 400,
					},
					callback: function (value: number, index: number, ticks: number) {
						if (!data.labels) {
							return value;
						}
						let label = data.labels[value];
						const dateParts = label.split('-');
						let output = dateParts.length > 1 ? dateParts[2] + '.' + dateParts[1] : data.labels[value];
						return data.labels ? output : value;
					},
				},
			},
			y: {
				min: 0,
				grace: window.innerWidth > 700 ? '50%' : '60%',
				display: true,
				title: {
					display: false,
				},
				grid: {
					tickLength: window.innerWidth > 700 ? 40 : 15,
					color: '#9AA1B3',
				},
				border: {
					display: false,
				},
				ticks: {
					beginAtZero: true,
					maxTicksLimit: window.innerWidth > 700 ? 8 : 8,
					color: '#9AA1B3',
					font: {
						family:
							'Poppins, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
						size: window.innerWidth > 700 ? 18 : 15,
						weight: 400,
					},
					callback: data.axisYCallback
						? data.axisYCallback
						: function (value: string, index: number, ticks: number) {
								let result = value + `${data.valuePostfix}`;
								if (window.innerWidth > 700) {
									result += '     ';
								}
								return result;
						  },
				},
			},
		},
	};

	let defaultDatasetValues = {
		backgroundColor: (ctx: any) => {
			const ctxEl = ctx.chart.ctx;
			let gradient = ctxEl.createLinearGradient(0, 0, 0, 600);
			gradient.addColorStop(0, '#1BC5BD');
			gradient.addColorStop(1, 'rgba(246,246,246,0.5)');
			return gradient;
		},
		borderColor: '#1BC5BD',
		borderWidth: 4,
		tension: 0.4,
		fill: true,
		pointStyle: 'circle',
		pointBackgroundColor: '#fff',
		pointRadius: window.innerWidth > 700 ? 8 : 5,
		pointHoverRadius: window.innerWidth > 700 ? 8 : 5,
		pointBorderWidth: window.innerWidth > 700 ? 3 : 2,
		pointBorderColor: '#1BC5BD',
	};

	return { options, defaultDatasetValues };
}
