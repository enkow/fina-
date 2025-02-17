<template>
	<AccordionTab key="calendarTimeScale" :class="`icon-${settingIconColor}`">
		<template #icon>
			<ClockIcon />
		</template>

		<template #title>
			{{ $t('settings.calendar-time-scale') }}
		</template>

		<p class="block w-full">
			{{ $t('settings.calendar-time-scale-description') }}
		</p>

		<form
			class="accordion-footer flex-wrap sm:flex-nowrap"
			@submit.prevent="
				form.put(route('club.settings.update', { key: 'calendar_time_scale' }), {
					preserveState: true,
					preserveScroll: true,
				})
			">
			<div class="w-full space-y-1.5 sm:w-1/2">
				<InputLabel :value="capitalize($t('main.current-value'))" class="text-xs" />
				<SimpleSelect
					v-model="form.value"
					:options="options"
					:disabled="!hasPermission"
					:class="{ 'disabled-readable': !hasPermission }"
					class="w-full"
					position="top" />
				<div v-if="form.errors.value" class="error">
					{{ form.errors.value }}
				</div>
			</div>
			<div class="w-full sm:w-1/2">
				<InputLabel class="mb-0.25 text-xs" value="&nbsp;" />
				<Button
					v-if="!hasPermission"
					class="lg accordion-footer__submit disabled mt-1 !h-12"
					v-tippy="{ allowHTML: true }"
					:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'">
					{{ $t('main.action.update') }}
				</Button>
				<Button v-else class="lg accordion-footer__submit mt-1 !h-12" type="submit">
					{{ $t('main.action.update') }}
				</Button>
			</div>
		</form>
	</AccordionTab>
</template>
<script lang="ts" setup>
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import ClockIcon from '@/Components/Dashboard/Icons/ClockIcon.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { SelectOption } from '@/Types/models';
import { SettingEntity } from '@/Types/responses';
import { capitalize, ref } from 'vue';
import { wTrans } from 'laravel-vue-i18n';
import { usePanelStore } from '@/Stores/panel';

const options = ref<SelectOption[]>([
	{ code: 15, label: wTrans('settings.minutes_value', { value: '15' }) },
	{ code: 30, label: wTrans('settings.minutes_value', { value: '30' }) },
	{ code: 60, label: wTrans('settings.minutes_value', { value: '60' }) },
]);

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		settingIconColor: string;
	}>(),
	{
		settingIconColor: 'info',
	},
);

const { page } = usePanelStore();
const hasPermission = ['admin', 'manager'].includes(page.props.user.type as string);

const form = useForm({
	value: props.setting.value,
	feature_id: props.setting.feature?.id,
});
</script>
