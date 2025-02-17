<template>
	<AccordionTab key="calendarTimeScale" :class="`icon-${settingIconColor}`">
		<template #icon>
			<ClockIcon />
		</template>

		<template #title>Cena za osobę - typ opłaty ({{ setting.feature.game.name }})</template>

		<form
			class="accordion-footer flex-wrap sm:flex-nowrap"
			@submit.prevent="
				form.put(route('admin.clubs.settings.update', { club: club, key: 'price_per_person_type' }), {
					preserveState: true,
					preserveScroll: true,
				})
			">
			<div class="w-full space-y-1.5 sm:w-1/2">
				<InputLabel :value="capitalize($t('main.current-value'))" class="text-xs" />
				<SimpleSelect v-model="form.value" :options="options" class="w-full" position="top" />
				<div v-if="form.errors.value" class="error">
					{{ form.errors.value }}
				</div>
			</div>
			<div class="w-full sm:w-1/2">
				<InputLabel class="mb-0.25 text-xs" value="&nbsp;" />
				<Button class="lg accordion-footer__submit mt-1 !h-12" type="submit">
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
import { Club, SelectOption } from '@/Types/models';
import { SettingEntity } from '@/Types/responses';
import { capitalize, ref } from 'vue';
import { wTrans } from 'laravel-vue-i18n';
import { usePanelStore } from '@/Stores/panel';

const options = ref<SelectOption[]>([
	{ code: 0, label: 'Wyłączone' },
	{ code: 1, label: 'Opłata za dodatek' },
	{ code: 2, label: 'Opłata za osobę' },
]);

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		settingIconColor: string;
		club: Club;
	}>(),
	{
		settingIconColor: 'info',
	},
);

const form = useForm({
	value: props.setting.value,
	feature_id: props.setting.feature?.id,
});
</script>
