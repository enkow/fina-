<template>
	<Datepicker
		:autoApply="true"
		:clearable="false"
		:disabled="disabled"
		:disabledDates="disabledDates"
		:close-on-auto-apply="true"
		:day-names="usePage().props.generalTranslations['week-day-short']"
		:enable-time-picker="false"
		:preset-ranges="presetRanges"
		:no-disabled-range="noDisabledRange"
		class="range-datepicker mb-3 w-full"
		format="yyyy-MM-dd"
		:locale="locale ?? usePage().props.user.club?.country?.locale ?? 'pl'"
		menu-class-name="simpleDatepicker"
		multi-calendars
		range>
		<template #dp-input="{ value, onInput, onEnter, onTab, onClear }">
			<DateInput
				:classes="{ withIcon: inputWithIcon && !disabled, 'disabled-readable cursor-not-allowed': disabled }"
				:disabled="disabled"
				:value="value"
				class="min-w-[250px]" />
		</template>
	</Datepicker>
</template>

<style scoped>
.range-datepicker:deep(.dp__menu) {
	@apply h-92;
}
</style>

<script lang="ts" setup>
import { Ref, ref } from 'vue';
import Datepicker from '@vuepic/vue-datepicker';
import DateInput from '@/Components/Dashboard/DateInput.vue';
import dayjs from 'dayjs';
import { usePage } from '@inertiajs/vue3';
import { wTrans } from 'laravel-vue-i18n';
import '@vuepic/vue-datepicker/dist/main.css';
import { usePanelStore } from '@/Stores/panel';

const props = withDefaults(
	defineProps<{
		inputClasses?: string[];
		inputWithIcon?: boolean;
		disabled?: boolean;
		onlyFutureDates?: boolean;
		noDisabledRange?: boolean;
		disabledDates?: Date[] | string[] | ((date: Date) => boolean);
		locale?: string;
	}>(),
	{
		inputClasses: [],
		inputWithIcon: false,
		disabled: false,
		onlyFutureDates: false,
	},
);

const panel = usePanelStore();

interface PresetRange {
	label: string | number;
	range: Array<any>;
}

const presetRanges: ref<PresetRange[]> = ref([]);
if (!props.onlyFutureDates) {
	presetRanges.value.push({
		label: wTrans('reservation.start-date-filters.yesterday'),
		range: [dayjs().subtract(1, 'day'), dayjs().subtract(1, 'day')],
	});
}
presetRanges.value.push(
	{
		label: wTrans('reservation.start-date-filters.today'),
		range: [dayjs(), dayjs()],
	},
	{
		label: wTrans('reservation.start-date-filters.tomorrow'),
		range: [dayjs().add(1, 'day'), dayjs().add(1, 'day')],
	},
);
if (!props.onlyFutureDates) {
	presetRanges.value.push(
		{
			label: wTrans('reservation.start-date-filters.last-seven-days'),
			range: [dayjs().subtract(6, 'day'), dayjs()],
		},
		{
			label: wTrans('reservation.start-date-filters.last-thirty-days'),
			range: [dayjs().subtract(29, 'day'), dayjs()],
		},
	);
}
presetRanges.value.push(
	{
		label: wTrans('reservation.start-date-filters.this-month'),
		range: [dayjs().startOf('month'), dayjs().endOf('month')],
	},
	{
		label: wTrans('reservation.start-date-filters.this-year'),
		range: [dayjs().startOf('year'), dayjs().endOf('year')],
	},
);
</script>
