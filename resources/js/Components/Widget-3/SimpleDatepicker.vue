<template>
	<Datepicker
		:clearable="false"
		:day-names="usePage().props.generalTranslations?.['week-day-short'] ?? []"
		:enable-time-picker="false"
		:format="format"
		auto-apply
		class="datepicker mb-3 w-full"
		locale="pl"
		menu-class-name="simpleDatepicker"
		model-type="yyyy-MM-dd">
		<template #month-year="{ month, year, months, years, updateMonthYear, handleMonthYearChange }">
			<slot
				name="month-year"
				:month="month"
				:year="year"
				:months="months"
				:years="years"
				:update-month-year="updateMonthYear"
				:handle-month-year-change="handleMonthYearChange"></slot>
		</template>
		<template #dp-input="{ value, onInput, onEnter, onTab, onClear }">
			<DateInput
				style="-webkit-appearance: none"
				class="h-9.5 text-sm !font-extrabold !ring-transparent focus:border-gray-2"
				:class="{ withIcon: inputWithIcon }"
				:placeholder="placeholder"
				:value="value" />
		</template>
	</Datepicker>
</template>

<style scoped>
.border-widget {
	border-color: v-bind(widgetColor) !important;
}
.datepicker:deep(.dp__instance_calendar) {
	.dp__calendar_item .dp__cell_inner {
		&:not(.dp__cell_disabled).dp__range_between:after,
		&:not(.dp__cell_disabled):not(.dp__range_end).dp__range_start:after {
			@apply opacity-80;
			background: v-bind(widgetColor);
		}

		&.dp__active_date {
			background: v-bind(widgetColor);
		}

		&.dp__today {
			border-color: v-bind(widgetColor);
		}
	}

	.dp__select {
		background: v-bind(widgetColor);
	}

	.dp__overlay_cell_active {
		background: v-bind(widgetColor);
	}
}

.datepicker:deep(.dp__menu_inner) {
	@apply w-full text-sm;
}
.datepicker:deep(.dp__menu) {
	@apply -mt-1 h-[210px] w-full;
}
.datepicker:deep(.dp__instance_calendar) {
	@apply pb-0 pt-0;
}
.datepicker:deep(.simpleDatepicker .dp__instance_calendar .dp__calendar_item) {
	@apply my-0;
}
.datepicker:deep(.dp__calendar_row) {
	@apply my-0.5;
}
.datepicker:deep(.simpleDatepicker .dp__instance_calendar .dp__calendar_item .dp__cell_inner) {
	@apply !text-xs;
}
.datepicker:deep(dp__calendar_row) {
	@apply !my-0;
}
.datepicker:deep(.dp__calendar_header),
.datepicker:deep(.dp__arrow_top) {
	@apply hidden;
}
</style>

<script lang="ts" setup>
import Datepicker from '@vuepic/vue-datepicker';
import DateInput from '@/Components/Dashboard/DateInput.vue';
import { useString } from '@/Composables/useString';
import { usePage } from '@inertiajs/vue3';
import '@vuepic/vue-datepicker/dist/main.css';
import { useWidgetStore } from '@/Stores/widget';

const { pad } = useString();
const widgetStore = useWidgetStore();
const widgetColor: string = widgetStore.widgetColor;

const props = withDefaults(
	defineProps<{
		inputWithIcon?: boolean;
		placeholder?: string;
	}>(),
	{
		inputWithIcon: false,
		placeholder: '',
	},
);

const emit = defineEmits<{
	(e: 'update', value: string): void;
}>();

const format = (date: Date): string => {
	const day = pad(date.getDate().toString(), 2);
	const month = pad((date.getMonth() + 1).toString(), 2);
	const year = date.getFullYear();

	return `${year}-${month}-${day}`;
};
</script>
