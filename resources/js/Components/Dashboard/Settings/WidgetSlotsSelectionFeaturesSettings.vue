<template>
	<AccordionTab key="widgetSlotsSelectionFeaturesSettings" :class="`icon-${settingIconColor}`">
		<template #icon>
			<PuzzleIcon />
		</template>

		<template #title>
			{{ getTranslation('status-setting-title') }}
		</template>

		<p class="block w-full" v-html="getTranslation('status-setting-description')"></p>

		<form class="accordion-footer flex-wrap sm:flex-nowrap" @submit.prevent="submit">
			<div class="w-full space-y-1.5 sm:w-1/2">
				<InputLabel :value="capitalize($t('main.current-value'))" class="text-xs" />
				<SimpleSelect
					v-model="value"
					:options="options"
					:disabled="!hasPermission"
					:class="{ 'disabled-readable': !hasPermission }"
					class="w-full"
					position="top" />
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
import { Game, SelectOption } from '@/Types/models';
import { SettingEntity } from '@/Types/responses';
import { capitalize, computed, ref } from 'vue';
import { wTrans } from 'laravel-vue-i18n';
import { usePanelStore } from '@/Stores/panel';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import HashIcon from '@/Components/Dashboard/Icons/HashIcon.vue';
import PuzzleIcon from '@/Components/Dashboard/Icons/PuzzleIcon.vue';

const featureKeys: string[] = ['has_widget_slots_selection', 'slot_has_lounge'];
const featureSetting: Record<string, SettingEntity> = featureKeys.reduce(
	(acc, key) => ({ ...acc, [key]: getSettingByFeatureType(key) }),
	{},
);

const options = ref<SelectOption[]>([
	{ code: 0, label: computed<string>(() => capitalize(wTrans('main.none').value)) },
]);

const value = ref(0);
featureKeys.forEach((key, index) => {
	if (featureSetting[key]) {
		options.value.push({
			code: index + 1,
			label: featureSetting[key]!.feature!.translations['status-setting-option'],
		});
		if (featureSetting[key].value) {
			value.value = index + 1;
		}
	}
});

const props = withDefaults(
	defineProps<{
		settings: Record<string, SettingEntity>;
		game: Game;
		settingIconColor: string;
	}>(),
	{
		settingIconColor: 'info',
	},
);

function getTranslation(key: string) {
	return (
		featureSetting['has_widget_slots_selection']?.feature?.translations?.[key] ??
		featureSetting['slot_has_lounge']?.feature?.translations?.[key]
	);
}

function getSettingByFeatureType(featureType: string): SettingEntity | undefined {
	return Object.values(props.settings).find(
		(setting) => setting?.feature?.type === featureType && setting?.feature?.game?.id === props.game.id,
	);
}

const { page } = usePanelStore();
const hasPermission = ['admin', 'manager'].includes(page.props.user.type as string);

function submit() {
	const featureValues: Record<string, boolean> = {
		has_widget_slots_selection: value.value === 1,
		slot_has_lounge: value.value === 2,
	};
	const featureTypesStatusSettingKeysMap: Record<string, string> = {
		has_widget_slots_selection: 'widget_slots_selection_status',
		slot_has_lounge: 'lounges_status',
	};

	if (featureSetting['slot_has_lounge']) {
		axios.put(route('club.settings.update', { key: featureTypesStatusSettingKeysMap['slot_has_lounge'] }), {
			feature_id: featureSetting['slot_has_lounge']?.feature?.id,
			value: featureValues['slot_has_lounge'],
			returnType: 'json',
		});
	}

	if (featureSetting['has_widget_slots_selection']) {
		setTimeout(() => {
			router.put(
				route('club.settings.update', {
					key: featureTypesStatusSettingKeysMap['has_widget_slots_selection'],
				}),
				{
					feature_id: featureSetting['has_widget_slots_selection']?.feature?.id,
					value: featureValues['has_widget_slots_selection'],
				},
				{
					preserveState: true,
					preserveScroll: true,
				},
			);
		}, 1000);
	}
}
</script>
