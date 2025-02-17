<template>
	<WidgetUnstyledOrderDatePicker id="widget-2-datepicker">
		<template #month-year="{ month, year, handleMonthYearChange }">
			<div class="flex gap-x-3.5 text-ui-green-950">
				<button type="button" aria-label="Previous month" @click="handleMonthYearChange(false)">
					<PrevIcon />
				</button>
				<div class="flex w-36 justify-center gap-x-1 text-sm font-bold text-black">
					<p>{{ $t(`main.month-name.${month + 1}`) }}</p>
					<p>{{ year }}</p>
				</div>
				<button type="button" aria-label="Next month" @click="handleMonthYearChange(true)">
					<PrevIcon class="rotate-180" />
				</button>
			</div>
		</template>
	</WidgetUnstyledOrderDatePicker>
</template>

<script lang="ts" setup>
import WidgetUnstyledOrderDatePicker from '../Widget/Unstyled/WidgetUnstyledOrderDatePicker.vue';
import PrevIcon from './Icons/PrevIcon.vue';
</script>

<style>
#widget-2-datepicker .dp__theme_light {
	--dp-background-color: #f4faf1;
}

#widget-2-datepicker .dp__outer_menu_wrap {
	@apply absolute !left-0 !top-12.5 z-50 w-full md:relative md:!top-0 md:z-0;
}

#widget-2-datepicker .dp__menu {
	@apply !left-0 w-full !translate-x-0 rounded-lg border-0 bg-ui-green-50 focus:border-none;
}

#widget-2-datepicker .dp__menu_inner {
	@apply px-5 py-4 md:px-7 md:py-3.5;
}

#widget-2-datepicker .dp__calendar_header_item {
	@apply !h-auto text-xs text-ui-black/40;
}

#widget-2-datepicker .dp__calendar_header_separator {
	@apply hidden;
}

#widget-2-datepicker .dp__cell_inner {
	@apply h-fit rounded-lg border-0 px-0 py-0.5 text-sm text-ui-black;
}

#widget-2-datepicker .dp__date_hover {
	@apply hover:bg-ui-green-300 hover:text-ui-green-950;
}

#widget-2-datepicker .dp__active_date {
	@apply bg-ui-green text-white;
}

#widget-2-datepicker .dp__today {
	@apply border-ui-green;
}

#widget-2-datepicker .dp__cell_disabled {
	@apply text-ui-black/20;
}

#widget-2-datepicker .dp__arrow_top {
	@apply hidden;
}

#widget-2-datepicker .dp__input_readonly {
	@apply rounded-2.5xl border-0 py-2;
}

#widget-2-datepicker .dp__main {
	@apply static;
}

#widget-2-datepicker .dp__calendar_item {
	@apply flex justify-center;
}

#widget-2-datepicker .dp__instance_calendar {
	@apply font-montserrat;
}

#widget-2-datepicker .dp__month_year_row {
	@apply mb-2 !h-auto;
}

#widget-2-datepicker .dp__input {
	@apply h-10 bg-ui-green/10 px-6 text-xs font-medium !ring-0;
}

#widget-2-datepicker .dp__icon {
	@apply hidden;
}
</style>
