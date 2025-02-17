<template>
	<TextValue
		:label="capitalize($t('main.current-value'))"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="visible_calendar_slots_quantity">
		<template #icon>
			<HashIcon />
		</template>

		<template #title>{{ setting.feature.translations['setting-title'] }}</template>

		<p class="mb-4 font-semibold">
			{{ setting.feature.translations['setting-description-bolder'] }}
		</p>
		{{ setting.feature.translations['setting-description'] }}
	</TextValue>
</template>

<script lang="ts" setup>
import { useString } from '@/Composables/useString';
import TextValue from '@/Components/Dashboard/Settings/Types/TextValue.vue';
import { SettingEntity } from '@/Types/responses';
import HashIcon from '@/Components/Dashboard/Icons/HashIcon.vue';

const { capitalize } = useString();

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		settingIconColor?: string;
	}>(),
	{
		settingIconColor: 'info',
	},
);
</script>
