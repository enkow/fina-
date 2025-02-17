<template>
	<AccordionTab key="calendarTimeScale" :class="`icon-${settingIconColor}`">
		<template #icon>
			<OvenIcon />
		</template>

		<template #title>Ustawienia żarówek ({{ setting.feature.game.name }})</template>

		<form
			class="flex-wrap space-y-3"
			@submit.prevent="
				form.put(route('admin.clubs.settings.update', { club: club, key: 'bulb_status' }), {
					preserveState: true,
					preserveScroll: true,
				})
			">
			<div class="w-full space-y-1.5">
				<InputLabel value="Używany operator" class="text-xs" />
				<SimpleSelect v-model="form.provider" :options="options" class="w-full" position="top" />
				<div v-if="form.errors.provider" class="error">
					{{ form.errors.provider }}
				</div>
			</div>

			<div class="w-full space-y-1.5" v-if="form.value === true">
				<InputLabel value="Automatyczna synchronizacja" class="text-xs" />
				<ToggleInput v-model="form.synchronize" />
			</div>

			<div
				class="w-full space-y-1.5"
				v-for="field in bulbsAdaptersFields[form.provider]"
				v-if="form.value === true">
				<InputLabel :value="`Wartość [${field}]`" class="text-xs" />
				<TextInput v-model="form.auth[field]" />

				<div v-if="form.errors.value" class="error">
					{{ form.auth[field].value }}
				</div>
			</div>
			<div class="w-full">
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
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Club, SelectOption } from '@/Types/models';
import { SettingEntity } from '@/Types/responses';
import { ref, watch } from 'vue';
import OvenIcon from '@/Components/Dashboard/Icons/OvenIcon.vue';
import TextInput from '../TextInput.vue';
import ToggleInput from '../ToggleInput.vue';

const options = ref<SelectOption[]>([
	{ code: null, label: 'Wyłączone' },
	{ code: 'webthings', label: 'WebThings' },
	{ code: 'domiq', label: 'Domiq' },
	{ code: 'homeassistant', label: 'Home Assistant' },
]);

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		settingIconColor: string;
		club: Club;
		bulbsAdaptersFields: {
			[key: string]: string[];
		};
	}>(),
	{
		settingIconColor: 'info',
	},
);

const form = useForm({
	provider: props.setting.value ? props.setting?.bulbs?.type : null,
	value: props.setting.value,
	feature_id: props.setting.feature?.id,
	synchronize: props.setting?.bulbs?.synchronize || false,
	auth: { ...props.setting?.bulbs?.credentials },
});

watch(
	() => form.provider,
	(value) => {
		form.value = !!value;
	},
);
</script>
