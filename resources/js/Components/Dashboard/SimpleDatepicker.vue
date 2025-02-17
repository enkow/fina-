<template>
	<Datepicker
		:clearable="clearable"
		:day-names="usePage().props.generalTranslations?.['week-day-short'] ?? []"
		:enable-time-picker="false"
		:format="format"
		auto-apply
		class="mb-3 w-full"
		:locale="locale ?? usePage().props.user.club?.country?.locale ?? 'pl'"
		:disabled="disabled"
		menu-class-name="simpleDatepicker"
		model-type="yyyy-MM-dd">
		<template #dp-input="{ value, onInput, onEnter, onTab, onClear }">
			<DateInput
				:classes="{ withIcon: inputWithIcon && !disabled, 'disabled-readable': disabled }"
				:placeholder="placeholder"
				:value="value"
				:disabled="disabled" />
		</template>
	</Datepicker>
</template>

<script lang="ts" setup>
import Datepicker from '@vuepic/vue-datepicker';
import DateInput from '@/Components/Dashboard/DateInput.vue';
import { useString } from '@/Composables/useString';
import { usePage } from '@inertiajs/vue3';
import '@vuepic/vue-datepicker/dist/main.css';

const { pad } = useString();

const props = withDefaults(
	defineProps<{
		inputWithIcon?: boolean;
		placeholder?: string;
		disabled?: boolean;
		textInputClass?: any;
		locale?: string;
		clearable?: boolean;
	}>(),
	{
		clearable: false,
		inputWithIcon: false,
		placeholder: '',
		disabled: false,
	},
);

const format = (date: Date): string => {
	const day = pad(date.getDate().toString(), 2);
	const month = pad((date.getMonth() + 1).toString(), 2);
	const year = date.getFullYear();

	return `${year}-${month}-${day}`;
};
</script>
