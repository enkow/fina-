<template>
	<Datepicker
		:clearable="clearable"
		:day-names="dayNames"
		:enable-time-picker="true"
		auto-apply
		text-input
		class="mb-3 w-full"
		format="yyyy-MM-dd HH:mm"
		:locale="panel.user?.club?.country?.locale ?? 'pl'"
		menu-class-name="simpleDatepicker"
		model-type="yyyy-MM-dd HH:mm"
		@update:modelValue="handleDatepickerUpdate">
		<template #dp-input="dateInputSlotProps">
			<DateInput
				:class="{ withIcon: inputWithIcon, 'disabled-readable': disabled }"
				:placeholder="placeholder"
				:disabled="disabled"
				:value="dateInputSlotProps.value"
				@update:modelValue="handleUpdate" />
		</template>
	</Datepicker>
</template>

<script lang="ts" setup>
import { computed, Ref, ref } from 'vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import ChevronUpIcon from '@/Components/Dashboard/Icons/ChevronUpIcon.vue';
import DateInput from '@/Components/Dashboard/DateInput.vue';
import { useString } from '@/Composables/useString';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import { SelectOption } from '@/Types/models';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import VueSelect from 'vue-select';
import { usePanelStore } from '@/Stores/panel';
import { wTrans } from 'laravel-vue-i18n';

const props = withDefaults(
	defineProps<{
		inputWithIcon?: boolean;
		placeholder: string;
		clearable?: boolean;
		disabled?: boolean;
	}>(),
	{
		inputWithIcon: false,
		placeholder: '',
		clearable: false,
		disabled: false,
	},
);

const emit = defineEmits<{
	(event: 'update:modelValue', value: any): void;
}>();

const dateInputSlotProps = ref<{
	value?: string;
	onInput?: (value: string) => void;
	onEnter?: Function;
	onTab?: Function;
	onClear?: Function;
}>({});

const { pad: string } = useString();
const { monthOptions, yearOptions } = useSelectOptions();

const panel = usePanelStore();
const dayNames = computed<string[]>(() => {
	let result = [];
	for (let i = 1; i <= 7; i++) {
		result.push(wTrans(`main.week-day-short.${i}`).value);
	}
	return result;
});

const singleDateYear: Ref<SelectOption> = ref({
	code: '2023',
	label: 'Rok 2023',
});
const singleDateMonth: Ref<SelectOption> = ref({
	code: '6',
	label: 'Czerwiec',
});

type UpdateMonthYear = (month: number, year: number) => void;

const updateMonth = (event: InputEvent, updateMonthYear: UpdateMonthYear, year: number) => {
	updateMonthYear(+event, year);
};

const updateYear = (event: InputEvent, updateMonthYear: UpdateMonthYear, month: number) => {
	updateMonthYear(month, +event);
};

const year = ref<SimpleSelect | null>(null);

const isValidDate = (value: string): boolean => {
	const regex = /^\d{4}-\d{2}-\d{2} ([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/;
	return regex.test(value);
};

const handleUpdate = (newValue) => {
	if (!isValidDate(newValue)) {
		return;
	}
	if (dateInputSlotProps.value.onInput) {
		dateInputSlotProps.value.onInput(newValue);
	}
	emit('update:modelValue', newValue);
};

const handleDatepickerUpdate = (newValue) => {
	dateInputSlotProps.value = newValue;

	emit('update:modelValue', newValue);
};

function hideYear() {}
</script>

<style scoped>
.datepicker:deep(.vs__selected) {
	@apply capitalize;
}
</style>
